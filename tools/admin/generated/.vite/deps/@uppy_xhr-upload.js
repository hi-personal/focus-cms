import { l as EventManager, u as BasePlugin } from "./lib-DxrmiKCR.js";
//#region ../../../node_modules/@uppy/core/lib/utils/NetworkError.js
var NetworkError = class extends Error {
	cause;
	isNetworkError;
	request;
	constructor(error, xhr = null) {
		super(`This looks like a network error, the endpoint might be blocked by an internet provider or a firewall.`);
		this.cause = error;
		this.isNetworkError = true;
		this.request = xhr;
	}
};
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/ProgressTimeout.js
/**
* Helper to abort upload requests if there has not been any progress for `timeout` ms.
* Create an instance using `timer = new ProgressTimeout(10000, onTimeout)`
* Call `timer.progress()` to signal that there has been progress of any kind.
* Call `timer.done()` when the upload has completed.
*/
var ProgressTimeout = class {
	#aliveTimer;
	#isDone = false;
	#onTimedOut;
	#timeout;
	constructor(timeout, timeoutHandler) {
		this.#timeout = timeout;
		this.#onTimedOut = () => timeoutHandler(timeout);
	}
	progress() {
		if (this.#isDone) return;
		if (this.#timeout > 0) {
			clearTimeout(this.#aliveTimer);
			this.#aliveTimer = setTimeout(this.#onTimedOut, this.#timeout);
		}
	}
	done() {
		if (!this.#isDone) {
			clearTimeout(this.#aliveTimer);
			this.#aliveTimer = void 0;
			this.#isDone = true;
		}
	}
};
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/fetcher.js
var noop = () => {};
/**
* Fetches data from a specified URL using XMLHttpRequest, with optional retry functionality and progress tracking.
*
* @param url The URL to send the request to.
* @param options Optional settings for the fetch operation.
*/
function fetcher(url, options = {}) {
	const { body = null, headers = {}, method = "GET", onBeforeRequest = noop, onUploadProgress = noop, shouldRetry = () => true, onAfterResponse = noop, onTimeout = noop, responseType, retries = 3, signal = null, timeout = 3e4, withCredentials = false } = options;
	const delay = (attempt) => .3 * 2 ** (attempt - 1) * 1e3;
	const timer = new ProgressTimeout(timeout, onTimeout);
	function requestWithRetry(retryCount = 0) {
		return new Promise(async (resolve, reject) => {
			const xhr = new XMLHttpRequest();
			const onError = (error) => {
				if (shouldRetry(xhr) && retryCount < retries) setTimeout(() => {
					requestWithRetry(retryCount + 1).then(resolve, reject);
				}, delay(retryCount));
				else {
					timer.done();
					reject(error);
				}
			};
			xhr.open(method, url, true);
			xhr.withCredentials = withCredentials;
			if (responseType) xhr.responseType = responseType;
			xhr.onload = async () => {
				try {
					await onAfterResponse(xhr, retryCount);
				} catch (err) {
					err.request = xhr;
					onError(err);
					return;
				}
				if (xhr.status >= 200 && xhr.status < 300) {
					timer.done();
					resolve(xhr);
				} else if (shouldRetry(xhr) && retryCount < retries) setTimeout(() => {
					requestWithRetry(retryCount + 1).then(resolve, reject);
				}, delay(retryCount));
				else {
					timer.done();
					reject(new NetworkError(xhr.statusText, xhr));
				}
			};
			xhr.onerror = () => onError(new NetworkError(xhr.statusText, xhr));
			xhr.upload.onprogress = (event) => {
				timer.progress();
				onUploadProgress(event);
			};
			if (headers) Object.keys(headers).forEach((key) => {
				xhr.setRequestHeader(key, headers[key]);
			});
			function abort() {
				xhr.abort();
				reject(new DOMException("Aborted", "AbortError"));
			}
			signal?.addEventListener("abort", abort);
			if (signal?.aborted) {
				abort();
				return;
			}
			await onBeforeRequest(xhr, retryCount);
			xhr.send(body);
		});
	}
	return requestWithRetry();
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/fileFilters.js
var hasError = (file) => "error" in file && !!file.error;
var isCompleted = (file) => file.progress.uploadComplete;
function filterFilesToUpload(files) {
	return files.filter((file) => !hasError(file) && !isCompleted(file));
}
function filterFilesToEmitUploadStarted(files) {
	return files.filter((file) => !file.progress?.uploadStarted || !file.isRestored);
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/getAllowedMetaFields.js
function getAllowedMetaFields(fields, meta) {
	if (fields === true) return Object.keys(meta);
	if (Array.isArray(fields)) return fields;
	return [];
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/isNetworkError.js
function isNetworkError(xhr) {
	if (!xhr) return false;
	return xhr.readyState === 4 && xhr.status === 0;
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/TaskQueue.js
/**
* A concurrent task queue with FIFO ordering.
*
* Tasks are functions that receive an AbortSignal and return a Promise.
* The queue manages concurrency and processes tasks in insertion order.
*
* @example
* ```ts
* const queue = new TaskQueue({ concurrency: 3 })
*
* const promise = queue.add(async (signal) => {
*   const response = await fetch(url, { signal })
*   return response.json()
* })
*
* // To abort:
* promise.abort()
* ```
*/
var TaskQueue = class {
	#queue = [];
	#running = 0;
	#concurrency;
	#paused = false;
	constructor(options) {
		const limit = options?.concurrency;
		this.#concurrency = typeof limit !== "number" || limit === 0 ? Infinity : limit;
	}
	/**
	* Add a task to the queue.
	*
	* @param task - Function receiving AbortSignal, returns Promise
	* @returns AbortablePromise that resolves with task result
	*/
	add(task) {
		const controller = new AbortController();
		let resolve;
		let reject;
		const promise = new Promise((res, rej) => {
			resolve = res;
			reject = rej;
		});
		const queuedTask = {
			run: () => task(controller.signal),
			resolve,
			reject,
			controller
		};
		controller.signal.addEventListener("abort", () => {
			const index = this.#queue.indexOf(queuedTask);
			if (index !== -1) {
				this.#queue.splice(index, 1);
				reject(controller.signal.reason ?? new DOMException("Aborted", "AbortError"));
			}
		}, { once: true });
		promise.abort = (reason) => {
			controller.abort(reason ?? new DOMException("Aborted", "AbortError"));
		};
		promise.abortOn = (signal) => {
			if (signal) {
				const onAbort = () => promise.abort(signal.reason);
				signal.addEventListener("abort", onAbort, { once: true });
				promise.then(() => signal.removeEventListener("abort", onAbort), () => signal.removeEventListener("abort", onAbort));
			}
			return promise;
		};
		if (!this.#paused && this.#running < this.#concurrency) this.#execute(queuedTask);
		else this.#queue.push(queuedTask);
		return promise;
	}
	#execute(task) {
		this.#running++;
		if (task.controller.signal.aborted) {
			this.#running--;
			task.reject(task.controller.signal.reason ?? new DOMException("Aborted", "AbortError"));
			this.#advance();
			return;
		}
		let runPromise;
		try {
			runPromise = task.run();
		} catch (error) {
			runPromise = Promise.reject(error);
		}
		runPromise.then((result) => {
			if (task.controller.signal.aborted) task.reject(task.controller.signal.reason ?? new DOMException("Aborted", "AbortError"));
			else task.resolve(result);
		}, (error) => {
			task.reject(error);
		}).finally(() => {
			this.#running--;
			this.#advance();
		});
	}
	#advance() {
		queueMicrotask(() => {
			if (this.#paused || this.#running >= this.#concurrency) return;
			while (this.#queue.length > 0) {
				const next = this.#queue.shift();
				if (next.controller.signal.aborted) continue;
				this.#execute(next);
				return;
			}
		});
	}
	/**
	* Pause the queue. Running tasks continue, but no new tasks start.
	*/
	pause() {
		this.#paused = true;
	}
	/**
	* Resume the queue and start processing pending tasks.
	*/
	resume() {
		this.#paused = false;
		const available = this.#concurrency - this.#running;
		for (let i = 0; i < available; i++) this.#advance();
	}
	/**
	* Clear all pending tasks from the queue.
	* Running tasks are not affected.
	*
	* @param reason - Optional reason for rejection (defaults to AbortError)
	*/
	clear(reason) {
		const tasks = this.#queue.splice(0);
		const error = reason ?? new DOMException("Cleared", "AbortError");
		for (const task of tasks) {
			task.controller.abort(error);
			task.reject(error);
		}
	}
	get concurrency() {
		return this.#concurrency;
	}
	set concurrency(value) {
		this.#concurrency = typeof value !== "number" || value === 0 ? Infinity : value;
		if (!this.#paused) {
			const available = this.#concurrency - this.#running;
			for (let i = 0; i < available; i++) this.#advance();
		}
	}
	get pending() {
		return this.#queue.length;
	}
	get running() {
		return this.#running;
	}
	get isPaused() {
		return this.#paused;
	}
	/**
	* @deprecated Legacy compatibility wrapper for RateLimitedQueue API.
	* Wraps a function so that when called, it's queued and returns an AbortablePromise.
	* Note: for legacy compatibility with RateLimitedQueue, the wrapped function
	* does not receive this queue's AbortSignal. Aborting the returned promise
	* will reject it, but it will not automatically cancel work inside the wrapped
	* function unless that function is wired to an external AbortSignal.
	*/
	wrapPromiseFunction(fn) {
		return (...args) => {
			return this.add((signal) => {
				return fn(...args);
			});
		};
	}
};
//#endregion
//#region ../../../node_modules/@uppy/xhr-upload/package.json
var version = "6.0.0";
//#endregion
//#region ../../../node_modules/@uppy/xhr-upload/lib/locale.js
var locale_default = { strings: { uploadStalled: "Upload has not made any progress for %{seconds} seconds. You may want to retry it." } };
//#endregion
//#region ../../../node_modules/@uppy/xhr-upload/lib/index.js
function buildResponseError(xhr, err) {
	let error = err;
	if (!error) error = /* @__PURE__ */ new Error("Upload error");
	if (typeof error === "string") error = new Error(error);
	if (!(error instanceof Error)) error = Object.assign(/* @__PURE__ */ new Error("Upload error"), { data: error });
	if (isNetworkError(xhr)) {
		error = new NetworkError(error, xhr);
		return error;
	}
	error.request = xhr;
	return error;
}
/**
* Set `data.type` in the blob to `file.meta.type`,
* because we might have detected a more accurate file type in Uppy
* https://stackoverflow.com/a/50875615
*/
function setTypeInBlob(file) {
	return file.data.slice(0, file.data.size, file.meta.type);
}
var defaultOptions = {
	formData: true,
	fieldName: "file",
	method: "post",
	allowedMetaFields: true,
	bundle: false,
	headers: {},
	timeout: 3e4,
	limit: 5,
	withCredentials: false,
	responseType: ""
};
var XHRUpload = class extends BasePlugin {
	static VERSION = version;
	#getFetcher;
	#queue;
	uploaderEvents;
	constructor(uppy, opts) {
		super(uppy, {
			...defaultOptions,
			fieldName: opts.bundle ? "files[]" : "file",
			...opts
		});
		this.type = "uploader";
		this.id = this.opts.id || "XHRUpload";
		this.defaultLocale = locale_default;
		this.i18nInit();
		this.#queue = new TaskQueue({ concurrency: this.opts.limit });
		if (this.opts.bundle && !this.opts.formData) throw new Error("`opts.formData` must be true when `opts.bundle` is enabled.");
		if (this.opts.bundle && typeof this.opts.headers === "function") throw new Error("`opts.headers` can not be a function when the `bundle: true` option is set.");
		if (opts?.allowedMetaFields === void 0 && "metaFields" in this.opts) throw new Error("The `metaFields` option has been renamed to `allowedMetaFields`.");
		this.uploaderEvents = Object.create(null);
		/**
		* xhr-upload wrapper for `fetcher` to handle user options
		* `validateStatus`, `getResponseError`, `getResponseData`
		* and to emit `upload-progress`, `upload-error`, and `upload-success` events.
		*/
		this.#getFetcher = (files) => {
			return async (url, options) => {
				try {
					const res = await fetcher(url, {
						...options,
						onBeforeRequest: (xhr, retryCount) => this.opts.onBeforeRequest?.(xhr, retryCount, files),
						shouldRetry: this.opts.shouldRetry,
						onAfterResponse: this.opts.onAfterResponse,
						onTimeout: (timeout) => {
							const seconds = Math.ceil(timeout / 1e3);
							const error = new Error(this.i18n("uploadStalled", { seconds }));
							this.uppy.emit("upload-stalled", error, files);
						},
						onUploadProgress: (event) => {
							if (event.lengthComputable) for (const { id } of files) {
								const file = this.uppy.getFile(id);
								if (file != null) this.uppy.emit("upload-progress", file, {
									uploadStarted: file.progress.uploadStarted ?? 0,
									bytesUploaded: event.loaded / event.total * file.size,
									bytesTotal: file.size
								});
							}
						}
					});
					let body = await this.opts.getResponseData?.(res);
					if (res.responseType === "json") body ??= res.response;
					else try {
						body ??= JSON.parse(res.responseText);
					} catch (cause) {
						throw new Error("@uppy/xhr-upload expects a JSON response (with a `url` property). To parse non-JSON responses, use `getResponseData` to turn your response into JSON.", { cause });
					}
					const uploadURL = typeof body?.url === "string" ? body.url : void 0;
					for (const { id } of files) this.uppy.emit("upload-success", this.uppy.getFile(id), {
						status: res.status,
						body,
						uploadURL
					});
					return res;
				} catch (error) {
					if (error.name === "AbortError") return;
					const request = error.request;
					for (const file of files) this.uppy.emit("upload-error", this.uppy.getFile(file.id), buildResponseError(request, error), request);
					throw error;
				}
			};
		};
	}
	getOptions(file) {
		const overrides = this.uppy.getState().xhrUpload;
		const { headers } = this.opts;
		const opts = {
			...this.opts,
			...overrides || {},
			...file.xhrUpload || {},
			headers: {}
		};
		if (typeof headers === "function") opts.headers = headers(file);
		else Object.assign(opts.headers, this.opts.headers);
		if (overrides) Object.assign(opts.headers, overrides.headers);
		if (file.xhrUpload) Object.assign(opts.headers, file.xhrUpload.headers);
		return opts;
	}
	addMetadata(formData, meta, opts) {
		getAllowedMetaFields(opts.allowedMetaFields, meta).forEach((item) => {
			const value = meta[item];
			if (Array.isArray(value)) value.forEach((subItem) => {
				formData.append(item, subItem);
			});
			else formData.append(item, value);
		});
	}
	createFormDataUpload(file, opts) {
		const formPost = new FormData();
		this.addMetadata(formPost, file.meta, opts);
		const dataWithUpdatedType = setTypeInBlob(file);
		if (file.name) formPost.append(opts.fieldName, dataWithUpdatedType, file.meta.name);
		else formPost.append(opts.fieldName, dataWithUpdatedType);
		return formPost;
	}
	createBundledUpload(files, opts) {
		const formPost = new FormData();
		const { meta } = this.uppy.getState();
		this.addMetadata(formPost, meta, opts);
		files.forEach((file) => {
			const options = this.getOptions(file);
			const dataWithUpdatedType = setTypeInBlob(file);
			if (file.name) formPost.append(options.fieldName, dataWithUpdatedType, file.name);
			else formPost.append(options.fieldName, dataWithUpdatedType);
		});
		return formPost;
	}
	async #uploadLocalFile(file) {
		const events = new EventManager(this.uppy);
		const controller = new AbortController();
		events.onFileRemove(file.id, () => controller.abort());
		events.onCancelAll(file.id, () => controller.abort());
		try {
			await this.#queue.add(async (signal) => {
				const opts = this.getOptions(file);
				const fetch = this.#getFetcher([file]);
				const body = opts.formData ? this.createFormDataUpload(file, opts) : file.data;
				return fetch(typeof opts.endpoint === "string" ? opts.endpoint : await opts.endpoint(file), {
					...opts,
					body,
					signal: AbortSignal.any([signal, controller.signal])
				});
			});
		} catch (error) {
			if (error.name === "AbortError") return;
			throw error;
		} finally {
			events.remove();
		}
	}
	async #uploadBundle(files) {
		const controller = new AbortController();
		function abort() {
			controller.abort();
		}
		this.uppy.once("cancel-all", abort);
		try {
			await this.#queue.add(async (signal) => {
				const optsFromState = this.uppy.getState().xhrUpload ?? {};
				const fetch = this.#getFetcher(files);
				const body = this.createBundledUpload(files, {
					...this.opts,
					...optsFromState
				});
				return fetch(typeof this.opts.endpoint === "string" ? this.opts.endpoint : await this.opts.endpoint(files), {
					...this.opts,
					body,
					signal: AbortSignal.any([signal, controller.signal])
				});
			});
		} catch (error) {
			if (error.name === "AbortError") return;
			throw error;
		} finally {
			this.uppy.off("cancel-all", abort);
		}
	}
	#getCompanionClientArgs(file) {
		const opts = this.getOptions(file);
		const allowedMetaFields = getAllowedMetaFields(opts.allowedMetaFields, file.meta);
		return {
			...file.remote?.body,
			protocol: "multipart",
			endpoint: opts.endpoint,
			size: file.data.size,
			fieldname: opts.fieldName,
			metadata: Object.fromEntries(allowedMetaFields.map((name) => [name, file.meta[name]])),
			httpMethod: opts.method,
			useFormData: opts.formData,
			headers: opts.headers
		};
	}
	async #uploadFiles(files) {
		await Promise.allSettled(files.map((file) => {
			if (file.isRemote) {
				const getQueue = () => this.#queue;
				const controller = new AbortController();
				const removedHandler = (removedFile) => {
					if (removedFile.id === file.id) controller.abort();
				};
				this.uppy.on("file-removed", removedHandler);
				return this.uppy.getRequestClientForFile(file).uploadRemoteFile(file, this.#getCompanionClientArgs(file), {
					signal: controller.signal,
					getQueue
				}).finally(() => {
					this.uppy.off("file-removed", removedHandler);
				});
			}
			return this.#uploadLocalFile(file);
		}));
	}
	#handleUpload = async (fileIDs) => {
		if (fileIDs.length === 0) {
			this.uppy.log("[XHRUpload] No files to upload!");
			return;
		}
		if (this.opts.limit === 0) this.uppy.log("[XHRUpload] When uploading multiple files at once, consider setting the `limit` option (to `10` for example), to limit the number of concurrent uploads, which helps prevent memory and network issues: https://uppy.io/docs/xhr-upload/#limit-0", "warning");
		this.uppy.log("[XHRUpload] Uploading...");
		const filesFiltered = filterFilesToUpload(this.uppy.getFilesByIds(fileIDs));
		const filesToEmit = filterFilesToEmitUploadStarted(filesFiltered);
		this.uppy.emit("upload-start", filesToEmit);
		if (this.opts.bundle) {
			if (filesFiltered.some((file) => file.isRemote)) throw new Error("Can’t upload remote files when the `bundle: true` option is set");
			if (typeof this.opts.headers === "function") throw new TypeError("`headers` may not be a function when the `bundle: true` option is set");
			await this.#uploadBundle(filesFiltered);
		} else await this.#uploadFiles(filesFiltered);
	};
	install() {
		if (this.opts.bundle) {
			const { capabilities } = this.uppy.getState();
			this.uppy.setState({ capabilities: {
				...capabilities,
				individualCancellation: false
			} });
		}
		this.uppy.addUploader(this.#handleUpload);
	}
	uninstall() {
		if (this.opts.bundle) {
			const { capabilities } = this.uppy.getState();
			this.uppy.setState({ capabilities: {
				...capabilities,
				individualCancellation: true
			} });
		}
		this.uppy.removeUploader(this.#handleUpload);
	}
};
//#endregion
export { XHRUpload as default };
