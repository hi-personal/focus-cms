//#region \0rolldown/runtime.js
var __create = Object.create;
var __defProp = Object.defineProperty;
var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
var __getOwnPropNames = Object.getOwnPropertyNames;
var __getProtoOf = Object.getPrototypeOf;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __commonJSMin = (cb, mod) => () => (mod || (cb((mod = { exports: {} }).exports, mod), cb = null), mod.exports);
var __copyProps = (to, from, except, desc) => {
	if (from && typeof from === "object" || typeof from === "function") for (var keys = __getOwnPropNames(from), i = 0, n = keys.length, key; i < n; i++) {
		key = keys[i];
		if (!__hasOwnProp.call(to, key) && key !== except) __defProp(to, key, {
			get: ((k) => from[k]).bind(null, key),
			enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable
		});
	}
	return to;
};
var __toESM = (mod, isNodeMode, target) => (target = mod != null ? __create(__getProtoOf(mod)) : {}, __copyProps(isNodeMode || !mod || !mod.__esModule || !__hasOwnProp.call(mod, "default") ? __defProp(target, "default", {
	value: mod,
	enumerable: true
}) : target, mod));
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/isDOMElement.js
/**
* Check if an object is a DOM element. Duck-typing based on `nodeType`.
*/
function isDOMElement(obj) {
	if (typeof obj !== "object" || obj === null) return false;
	if (!("nodeType" in obj)) return false;
	return obj.nodeType === Node.ELEMENT_NODE;
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/findDOMElement.js
function findDOMElement(element, context = document) {
	if (typeof element === "string") return context.querySelector(element);
	if (isDOMElement(element)) return element;
	return null;
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/getFileNameAndExtension.js
/**
* Takes a full filename string and returns an object {name, extension}
*/
function getFileNameAndExtension(fullFileName) {
	const lastDot = fullFileName.lastIndexOf(".");
	if (lastDot === -1 || lastDot === fullFileName.length - 1) return {
		name: fullFileName,
		extension: void 0
	};
	return {
		name: fullFileName.slice(0, lastDot),
		extension: fullFileName.slice(lastDot + 1)
	};
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/mimeTypes.js
var mimeTypes_default = {
	__proto__: null,
	md: "text/markdown",
	markdown: "text/markdown",
	mp4: "video/mp4",
	mp3: "audio/mp3",
	svg: "image/svg+xml",
	jpg: "image/jpeg",
	png: "image/png",
	webp: "image/webp",
	gif: "image/gif",
	heic: "image/heic",
	heif: "image/heif",
	yaml: "text/yaml",
	yml: "text/yaml",
	csv: "text/csv",
	tsv: "text/tab-separated-values",
	tab: "text/tab-separated-values",
	avi: "video/x-msvideo",
	mks: "video/x-matroska",
	mkv: "video/x-matroska",
	mov: "video/quicktime",
	dicom: "application/dicom",
	doc: "application/msword",
	msg: "application/vnd.ms-outlook",
	docm: "application/vnd.ms-word.document.macroenabled.12",
	docx: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
	dot: "application/msword",
	dotm: "application/vnd.ms-word.template.macroenabled.12",
	dotx: "application/vnd.openxmlformats-officedocument.wordprocessingml.template",
	xla: "application/vnd.ms-excel",
	xlam: "application/vnd.ms-excel.addin.macroenabled.12",
	xlc: "application/vnd.ms-excel",
	xlf: "application/x-xliff+xml",
	xlm: "application/vnd.ms-excel",
	xls: "application/vnd.ms-excel",
	xlsb: "application/vnd.ms-excel.sheet.binary.macroenabled.12",
	xlsm: "application/vnd.ms-excel.sheet.macroenabled.12",
	xlsx: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
	xlt: "application/vnd.ms-excel",
	xltm: "application/vnd.ms-excel.template.macroenabled.12",
	xltx: "application/vnd.openxmlformats-officedocument.spreadsheetml.template",
	xlw: "application/vnd.ms-excel",
	txt: "text/plain",
	text: "text/plain",
	conf: "text/plain",
	log: "text/plain",
	pdf: "application/pdf",
	zip: "application/zip",
	"7z": "application/x-7z-compressed",
	rar: "application/x-rar-compressed",
	tar: "application/x-tar",
	gz: "application/gzip",
	dmg: "application/x-apple-diskimage"
};
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/getFileType.js
function getFileType(file) {
	if (file.type) return file.type;
	const fileExtension = file.name ? getFileNameAndExtension(file.name).extension?.toLowerCase() : null;
	if (fileExtension && fileExtension in mimeTypes_default) return mimeTypes_default[fileExtension];
	return "application/octet-stream";
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/generateFileID.js
function encodeCharacter(character) {
	return character.charCodeAt(0).toString(32);
}
function encodeFilename(name) {
	let suffix = "";
	return name.replace(/[^A-Z0-9]/gi, (character) => {
		suffix += `-${encodeCharacter(character)}`;
		return "/";
	}) + suffix;
}
/**
* Takes a file object and turns it into fileID, by converting file.name to lowercase,
* removing extra characters and adding type, size and lastModified
*/
function generateFileID(file, instanceId) {
	let id = instanceId || "uppy";
	if (typeof file.name === "string") id += `-${encodeFilename(file.name.toLowerCase())}`;
	if (file.type !== void 0) id += `-${file.type}`;
	if (file.meta && typeof file.meta.relativePath === "string") id += `-${encodeFilename(file.meta.relativePath.toLowerCase())}`;
	if (file.data?.size !== void 0) id += `-${file.data.size}`;
	if (file.data.lastModified !== void 0) id += `-${file.data.lastModified}`;
	return id;
}
function hasFileStableId(file) {
	if (!file.isRemote || !file.remote) return false;
	return (/* @__PURE__ */ new Set([
		"box",
		"dropbox",
		"drive",
		"facebook",
		"unsplash"
	])).has(file.remote.provider);
}
function getSafeFileId(file, instanceId) {
	if (hasFileStableId(file)) return file.id;
	const fileType = getFileType(file);
	return generateFileID({
		...file,
		type: fileType
	}, instanceId);
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/getTextDirection.js
/**
* Get the declared text direction for an element.
*/
function getTextDirection(element) {
	while (element && !element.dir) element = element.parentNode;
	return element?.dir;
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/getTimeStamp.js
/**
* Adds zero to strings shorter than two characters.
*/
function pad(number) {
	return number < 10 ? `0${number}` : number.toString();
}
/**
* Returns a timestamp in the format of `hours:minutes:seconds`
*/
function getTimeStamp() {
	const date = /* @__PURE__ */ new Date();
	return `${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/utils/Translator.js
function insertReplacement(source, rx, replacement) {
	const newParts = [];
	source.forEach((chunk) => {
		if (typeof chunk !== "string") {
			newParts.push(chunk);
			return;
		}
		rx[Symbol.split](chunk).forEach((raw, i, list) => {
			if (raw !== "") newParts.push(raw);
			if (i < list.length - 1) newParts.push(replacement);
		});
	});
	return newParts;
}
/**
* Takes a string with placeholder variables like `%{smart_count} file selected`
* and replaces it with values from options `{smart_count: 5}`
*
* @license https://github.com/airbnb/polyglot.js/blob/master/LICENSE
* taken from https://github.com/airbnb/polyglot.js/blob/master/lib/polyglot.js#L299
*
* @param phrase that needs interpolation, with placeholders
* @param options with values that will be used to replace placeholders
*/
function interpolate(phrase, options) {
	const dollarRegex = /\$/g;
	const dollarBillsYall = "$$$$";
	let interpolated = [phrase];
	if (options == null) return interpolated;
	for (const arg of Object.keys(options)) if (arg !== "_") {
		let replacement = options[arg];
		if (typeof replacement === "string") replacement = dollarRegex[Symbol.replace](replacement, dollarBillsYall);
		interpolated = insertReplacement(interpolated, new RegExp(`%\\{${arg}\\}`, "g"), replacement);
	}
	return interpolated;
}
var defaultOnMissingKey = (key) => {
	throw new Error(`missing string: ${key}`);
};
/**
* Translates strings with interpolation & pluralization support.
* Extensible with custom dictionaries and pluralization functions.
*
* Borrows heavily from and inspired by Polyglot https://github.com/airbnb/polyglot.js,
* basically a stripped-down version of it. Differences: pluralization functions are not hardcoded
* and can be easily added among with dictionaries, nested objects are used for pluralization
* as opposed to `||||` delimeter
*
* Usage example: `translator.translate('files_chosen', {smart_count: 3})`
*/
var Translator = class {
	locale;
	constructor(locales, { onMissingKey = defaultOnMissingKey } = {}) {
		this.locale = {
			strings: {},
			pluralize(n) {
				if (n === 1) return 0;
				return 1;
			}
		};
		if (Array.isArray(locales)) locales.forEach(this.#apply, this);
		else this.#apply(locales);
		this.#onMissingKey = onMissingKey;
	}
	#onMissingKey;
	#apply(locale) {
		if (!locale?.strings) return;
		const prevLocale = this.locale;
		Object.assign(this.locale, {
			strings: {
				...prevLocale.strings,
				...locale.strings
			},
			pluralize: locale.pluralize || prevLocale.pluralize
		});
	}
	/**
	* Public translate method
	*
	* @param key
	* @param options with values that will be used later to replace placeholders in string
	* @returns string translated (and interpolated)
	*/
	translate(key, options) {
		return this.translateArray(key, options).join("");
	}
	/**
	* Get a translation and return the translated and interpolated parts as an array.
	*
	* @returns The translated and interpolated parts, in order.
	*/
	translateArray(key, options) {
		let string = this.locale.strings[key];
		if (string == null) {
			this.#onMissingKey(key);
			string = key;
		}
		if (typeof string === "object") {
			if (options && typeof options.smart_count !== "undefined") {
				const plural = this.locale.pluralize(options.smart_count);
				return interpolate(string[plural], options);
			}
			throw new Error("Attempted to use a string with plural forms, but no value was given for %{smart_count}");
		}
		if (typeof string !== "string") throw new Error(`string was not a string`);
		return interpolate(string, options);
	}
};
//#endregion
//#region ../../../node_modules/preact/dist/preact.module.js
var n;
var l;
var u;
var i;
var r;
var o;
var e;
var f;
var c;
var a;
var s;
var h;
var p;
var v;
var d = {};
var w = [];
var _ = /acit|ex(?:s|g|n|p|$)|rph|grid|ows|mnc|ntw|ine[ch]|zoo|^ord|itera/i;
var g = Array.isArray;
function m(n, l) {
	for (var u in l) n[u] = l[u];
	return n;
}
function b(n) {
	n && n.parentNode && n.parentNode.removeChild(n);
}
function k(l, u, t) {
	var i, r, o, e = {};
	for (o in u) "key" == o ? i = u[o] : "ref" == o ? r = u[o] : e[o] = u[o];
	if (arguments.length > 2 && (e.children = arguments.length > 3 ? n.call(arguments, 2) : t), "function" == typeof l && null != l.defaultProps) for (o in l.defaultProps) void 0 === e[o] && (e[o] = l.defaultProps[o]);
	return x(l, e, i, r, null);
}
function x(n, t, i, r, o) {
	var e = {
		type: n,
		props: t,
		key: i,
		ref: r,
		__k: null,
		__: null,
		__b: 0,
		__e: null,
		__c: null,
		constructor: void 0,
		__v: null == o ? ++u : o,
		__i: -1,
		__u: 0
	};
	return null == o && null != l.vnode && l.vnode(e), e;
}
function M() {
	return { current: null };
}
function S(n) {
	return n.children;
}
function C(n, l) {
	this.props = n, this.context = l;
}
function $(n, l) {
	if (null == l) return n.__ ? $(n.__, n.__i + 1) : null;
	for (var u; l < n.__k.length; l++) if (null != (u = n.__k[l]) && null != u.__e) return u.__e;
	return "function" == typeof n.type ? $(n) : null;
}
function I(n) {
	if (n.__P && n.__d) {
		var u = n.__v, t = u.__e, i = [], r = [], o = m({}, u);
		o.__v = u.__v + 1, l.vnode && l.vnode(o), q(n.__P, o, u, n.__n, n.__P.namespaceURI, 32 & u.__u ? [t] : null, i, null == t ? $(u) : t, !!(32 & u.__u), r), o.__v = u.__v, o.__.__k[o.__i] = o, D(i, o, r), u.__e = u.__ = null, o.__e != t && P(o);
	}
}
function P(n) {
	if (null != (n = n.__) && null != n.__c) return n.__e = n.__c.base = null, n.__k.some(function(l) {
		if (null != l && null != l.__e) return n.__e = n.__c.base = l.__e;
	}), P(n);
}
function A(n) {
	(!n.__d && (n.__d = !0) && i.push(n) && !H.__r++ || r != l.debounceRendering) && ((r = l.debounceRendering) || o)(H);
}
function H() {
	try {
		for (var n, l = 1; i.length;) i.length > l && i.sort(e), n = i.shift(), l = i.length, I(n);
	} finally {
		i.length = H.__r = 0;
	}
}
function L(n, l, u, t, i, r, o, e, f, c, a) {
	var s, h, p, v, y, _, g = t && t.__k || w, m = l.length;
	for (f = T(u, l, g, f, m), s = 0; s < m; s++) null != (p = u.__k[s]) && (h = -1 != p.__i && g[p.__i] || d, p.__i = s, _ = q(n, p, h, i, r, o, e, f, c, a), v = p.__e, p.ref && h.ref != p.ref && (h.ref && J(h.ref, null, p), a.push(p.ref, p.__c || v, p)), null == y && null != v && (y = v), 4 & p.__u ? (f = j(p, f, n), h.__e && (h.__e = null)) : "function" == typeof p.type && void 0 !== _ ? f = _ : v && (f = v.nextSibling), p.__u &= -7);
	return u.__e = y, f;
}
function T(n, l, u, t, i) {
	var r, o, e, f, c, a = u.length, s = a, h = 0;
	for (n.__k = new Array(i), r = 0; r < i; r++) null != (o = l[r]) && "boolean" != typeof o && "function" != typeof o ? ("string" == typeof o || "number" == typeof o || "bigint" == typeof o || o.constructor == String ? o = n.__k[r] = x(null, o, null, null, null) : g(o) ? o = n.__k[r] = x(S, { children: o }, null, null, null) : void 0 === o.constructor && o.__b > 0 ? o = n.__k[r] = x(o.type, o.props, o.key, o.ref ? o.ref : null, o.__v) : n.__k[r] = o, f = r + h, o.__ = n, o.__b = n.__b + 1, e = null, -1 != (c = o.__i = O(o, u, f, s)) && (s--, (e = u[c]) && (e.__u |= 2)), null == e || null == e.__v ? (-1 == c && (i > a ? h-- : i < a && h++), "function" != typeof o.type && (o.__u |= 4)) : c != f && (c == f - 1 ? h-- : c == f + 1 ? h++ : (c > f ? h-- : h++, o.__u |= 4))) : n.__k[r] = null;
	if (s) for (r = 0; r < a; r++) null != (e = u[r]) && 0 == (2 & e.__u) && (e.__e == t && (t = $(e)), K(e, e));
	return t;
}
function j(n, l, u) {
	var t, i;
	if ("function" == typeof n.type) {
		for (t = n.__k, i = 0; t && i < t.length; i++) t[i] && (t[i].__ = n, l = j(t[i], l, u));
		return l;
	}
	n.__e != l && (l && n.type && !l.parentNode && (l = $(n)), l = u.insertBefore(n.__e, l || null));
	do
		l = l && l.nextSibling;
	while (null != l && 8 == l.nodeType);
	return l;
}
function F(n, l) {
	return l = l || [], null == n || "boolean" == typeof n || (g(n) ? n.some(function(n) {
		F(n, l);
	}) : l.push(n)), l;
}
function O(n, l, u, t) {
	var i, r, o, e = n.key, f = n.type, c = l[u], a = null != c && 0 == (2 & c.__u);
	if (null === c && null == e || a && e == c.key && f == c.type) return u;
	if (t > (a ? 1 : 0)) {
		for (i = u - 1, r = u + 1; i >= 0 || r < l.length;) if (null != (c = l[o = i >= 0 ? i-- : r++]) && 0 == (2 & c.__u) && e == c.key && f == c.type) return o;
	}
	return -1;
}
function z(n, l, u) {
	"-" == l[0] ? n.setProperty(l, null == u ? "" : u) : n[l] = null == u ? "" : "number" != typeof u || _.test(l) ? u : u + "px";
}
function N(n, l, u, t, i) {
	var r, o;
	n: if ("style" == l) if ("string" == typeof u) n.style.cssText = u;
	else {
		if ("string" == typeof t && (n.style.cssText = t = ""), t) for (l in t) u && l in u || z(n.style, l, "");
		if (u) for (l in u) t && u[l] == t[l] || z(n.style, l, u[l]);
	}
	else if ("o" == l[0] && "n" == l[1]) r = l != (l = l.replace(s, "$1")), o = l.toLowerCase(), l = o in n || "onFocusOut" == l || "onFocusIn" == l ? o.slice(2) : l.slice(2), n.l || (n.l = {}), n.l[l + r] = u, u ? t ? u[a] = t[a] : (u[a] = h, n.addEventListener(l, r ? v : p, r)) : n.removeEventListener(l, r ? v : p, r);
	else {
		if ("http://www.w3.org/2000/svg" == i) l = l.replace(/xlink(H|:h)/, "h").replace(/sName$/, "s");
		else if ("width" != l && "height" != l && "href" != l && "list" != l && "form" != l && "tabIndex" != l && "download" != l && "rowSpan" != l && "colSpan" != l && "role" != l && "popover" != l && l in n) try {
			n[l] = null == u ? "" : u;
			break n;
		} catch (n) {}
		"function" == typeof u || (null == u || !1 === u && "-" != l[4] ? n.removeAttribute(l) : n.setAttribute(l, "popover" == l && 1 == u ? "" : u));
	}
}
function V(n) {
	return function(u) {
		if (this.l) {
			var t = this.l[u.type + n];
			if (null == u[c]) u[c] = h++;
			else if (u[c] < t[a]) return;
			return t(l.event ? l.event(u) : u);
		}
	};
}
function q(n, u, t, i, r, o, e, f, c, a) {
	var s, h, p, v, y, d, _, k, x, M, I, P, A, H, T, j, F = u.type;
	if (void 0 !== u.constructor) return null;
	128 & t.__u && (c = !!(32 & t.__u), o = [f = u.__e = t.__e]), (s = l.__b) && s(u);
	n: if ("function" == typeof F) {
		h = e.length;
		try {
			if (x = u.props, M = F.prototype && F.prototype.render, I = (s = F.contextType) && i[s.__c], P = s ? I ? I.props.value : s.__ : i, t.__c ? k = (p = u.__c = t.__c).__ = p.__E : (M ? u.__c = p = new F(x, P) : (u.__c = p = new C(x, P), p.constructor = F, p.render = Q), I && I.sub(p), p.state || (p.state = {}), p.__n = i, v = p.__d = !0, p.__h = [], p._sb = []), M && null == p.__s && (p.__s = p.state), M && null != F.getDerivedStateFromProps && (p.__s == p.state && (p.__s = m({}, p.__s)), m(p.__s, F.getDerivedStateFromProps(x, p.__s))), y = p.props, d = p.state, p.__v = u, v) M && null == F.getDerivedStateFromProps && null != p.componentWillMount && p.componentWillMount(), M && null != p.componentDidMount && p.__h.push(p.componentDidMount);
			else {
				if (M && null == F.getDerivedStateFromProps && x !== y && null != p.componentWillReceiveProps && p.componentWillReceiveProps(x, P), u.__v == t.__v || !p.__e && null != p.shouldComponentUpdate && !1 === p.shouldComponentUpdate(x, p.__s, P)) {
					u.__v != t.__v && (p.props = x, p.state = p.__s, p.__d = !1), u.__e = t.__e, u.__k = t.__k, u.__k.some(function(n) {
						n && (n.__ = u);
					}), w.push.apply(p.__h, p._sb), p._sb = [], p.__h.length && e.push(p), f = $(t);
					break n;
				}
				null != p.componentWillUpdate && p.componentWillUpdate(x, p.__s, P), M && null != p.componentDidUpdate && p.__h.push(function() {
					p.componentDidUpdate(y, d, _);
				});
			}
			if (p.context = P, p.props = x, p.__P = n, p.__e = !1, A = l.__r, H = 0, M) p.state = p.__s, p.__d = !1, A && A(u), s = p.render(p.props, p.state, p.context), w.push.apply(p.__h, p._sb), p._sb = [];
			else do
				p.__d = !1, A && A(u), s = p.render(p.props, p.state, p.context), p.state = p.__s;
			while (p.__d && ++H < 25);
			p.state = p.__s, null != p.getChildContext && (i = m(m({}, i), p.getChildContext())), M && !v && null != p.getSnapshotBeforeUpdate && (_ = p.getSnapshotBeforeUpdate(y, d)), T = null != s && s.type === S && null == s.key ? E(s.props.children) : s, f = L(n, g(T) ? T : [T], u, t, i, r, o, e, f, c, a), p.base = u.__e, u.__u &= -161, p.__h.length && e.push(p), k && (p.__E = p.__ = null);
		} catch (n) {
			if (e.length = h, u.__v = null, c || null != o) {
				if (n.then) {
					for (u.__u |= c ? 160 : 128; f && 8 == f.nodeType && f.nextSibling;) f = f.nextSibling;
					null != o && (o[o.indexOf(f)] = null), u.__e = f;
				} else if (null != o) for (j = o.length; j--;) b(o[j]);
			} else u.__e = t.__e;
			u.__k ??= t.__k || [], n.then || B(u), l.__e(n, u, t);
		}
	} else null == o && u.__v == t.__v ? (u.__k = t.__k, u.__e = t.__e) : f = u.__e = G(t.__e, u, t, i, r, o, e, c, a);
	return (s = l.diffed) && s(u), 128 & u.__u ? void 0 : f;
}
function B(n) {
	n && (n.__c && (n.__c.__e = !0), n.__k && n.__k.some(B));
}
function D(n, u, t) {
	for (var i = 0; i < t.length; i++) J(t[i], t[++i], t[++i]);
	l.__c && l.__c(u, n), n.some(function(u) {
		try {
			n = u.__h, u.__h = [], n.some(function(n) {
				n.call(u);
			});
		} catch (n) {
			l.__e(n, u.__v);
		}
	});
}
function E(n) {
	return "object" != typeof n || null == n || n.__b > 0 ? n : g(n) ? n.map(E) : void 0 !== n.constructor ? null : m({}, n);
}
function G(u, t, i, r, o, e, f, c, a) {
	var s, h, p, v, y, w, _, m = i.props || d, k = t.props, x = t.type;
	if ("svg" == x ? o = "http://www.w3.org/2000/svg" : "math" == x ? o = "http://www.w3.org/1998/Math/MathML" : o || (o = "http://www.w3.org/1999/xhtml"), null != e) {
		for (s = 0; s < e.length; s++) if ((y = e[s]) && "setAttribute" in y == !!x && (x ? y.localName == x : 3 == y.nodeType)) {
			u = y, e[s] = null;
			break;
		}
	}
	if (null == u) {
		if (null == x) return document.createTextNode(k);
		u = document.createElementNS(o, x, k.is && k), c && (l.__m && l.__m(t, e), c = !1), e = null;
	}
	if (null == x) m === k || c && u.data == k || (u.data = k);
	else {
		if (e = "textarea" == x && null != k.defaultValue ? null : e && n.call(u.childNodes), !c && null != e) for (m = {}, s = 0; s < u.attributes.length; s++) m[(y = u.attributes[s]).name] = y.value;
		for (s in m) y = m[s], "dangerouslySetInnerHTML" == s ? p = y : "children" == s || s in k || "value" == s && "defaultValue" in k || "checked" == s && "defaultChecked" in k || N(u, s, null, y, o);
		for (s in k) y = k[s], "children" == s ? v = y : "dangerouslySetInnerHTML" == s ? h = y : "value" == s ? w = y : "checked" == s ? _ = y : c && "function" != typeof y || m[s] === y || N(u, s, y, m[s], o);
		if (h) c || p && (h.__html == p.__html || h.__html == u.innerHTML) || (u.innerHTML = h.__html), t.__k = [];
		else if (p && (u.innerHTML = ""), L("template" == t.type ? u.content : u, g(v) ? v : [v], t, i, r, "foreignObject" == x ? "http://www.w3.org/1999/xhtml" : o, e, f, e ? e[0] : i.__k && $(i, 0), c, a), null != e) for (s = e.length; s--;) b(e[s]);
		c && "textarea" != x || (s = "value", "progress" == x && null == w ? u.removeAttribute("value") : null != w && (w !== u[s] || "progress" == x && !w || "option" == x && w != m[s]) && N(u, s, w, m[s], o), s = "checked", null != _ && _ != u[s] && N(u, s, _, m[s], o));
	}
	return u;
}
function J(n, u, t) {
	try {
		if ("function" == typeof n) {
			var i = "function" == typeof n.__u;
			i && n.__u(), i && null == u || (n.__u = n(u));
		} else n.current = u;
	} catch (n) {
		l.__e(n, t);
	}
}
function K(n, u, t) {
	var i, r;
	if (l.unmount && l.unmount(n), (i = n.ref) && (i.current && i.current != n.__e || J(i, null, u)), null != (i = n.__c)) {
		if (i.componentWillUnmount) try {
			i.componentWillUnmount();
		} catch (n) {
			l.__e(n, u);
		}
		i.base = i.__P = i.__n = null;
	}
	if (i = n.__k) for (r = 0; r < i.length; r++) i[r] && K(i[r], u, t || "function" != typeof n.type);
	t || b(n.__e), n.__c = n.__ = n.__e = void 0;
}
function Q(n, l, u) {
	return this.constructor(n, u);
}
function R(u, t, i) {
	var r, o, e, f;
	t == document && (t = document.documentElement), l.__ && l.__(u, t), o = (r = "function" == typeof i) ? null : i && i.__k || t.__k, e = [], f = [], q(t, u = (!r && i || t).__k = k(S, null, [u]), o || d, d, t.namespaceURI, !r && i ? [i] : o ? null : t.firstChild ? n.call(t.childNodes) : null, e, !r && i ? i : o ? o.__e : t.firstChild, r, f), D(e, u, f), u.props.children = null;
}
function W(l, u, t) {
	var i, r, o, e, f = m({}, l.props);
	for (o in l.type && l.type.defaultProps && (e = l.type.defaultProps), u) "key" == o ? i = u[o] : "ref" == o ? r = u[o] : f[o] = void 0 === u[o] && null != e ? e[o] : u[o];
	return arguments.length > 2 && (f.children = arguments.length > 3 ? n.call(arguments, 2) : t), x(l.type, f, i || l.key, r || l.ref, null);
}
n = w.slice, l = { __e: function(n, l, u, t) {
	for (var i, r, o; l = l.__;) if ((i = l.__c) && !i.__) try {
		if ((r = i.constructor) && null != r.getDerivedStateFromError && (i.setState(r.getDerivedStateFromError(n)), o = i.__d), null != i.componentDidCatch && (i.componentDidCatch(n, t || {}), o = i.__d), o) return i.__E = i;
	} catch (l) {
		n = l;
	}
	throw n;
} }, u = 0, C.prototype.setState = function(n, l) {
	var u = null != this.__s && this.__s != this.state ? this.__s : this.__s = m({}, this.state);
	"function" == typeof n && (n = n(m({}, u), this.props)), n && m(u, n), null != n && this.__v && (l && this._sb.push(l), A(this));
}, C.prototype.forceUpdate = function(n) {
	this.__v && (this.__e = !0, n && this.__h.push(n), A(this));
}, C.prototype.render = S, i = [], o = "function" == typeof Promise ? Promise.prototype.then.bind(Promise.resolve()) : setTimeout, e = function(n, l) {
	return n.__v.__b - l.__v.__b;
}, H.__r = 0, f = Math.random().toString(8), c = "__d" + f, a = "__a" + f, s = /(PointerCapture)$|Capture$/i, h = 0, p = V(!1), v = V(!0);
//#endregion
//#region ../../../node_modules/@uppy/core/lib/BasePlugin.js
/**
* Core plugin logic that all plugins share.
*
* BasePlugin does not contain DOM rendering so it can be used for plugins
* without a user interface.
*
* See `Plugin` for the extended version with Preact rendering for interfaces.
*/
var BasePlugin = class {
	uppy;
	opts;
	id;
	defaultLocale;
	i18n;
	i18nArray;
	type;
	VERSION;
	constructor(uppy, opts) {
		this.uppy = uppy;
		this.opts = opts ?? {};
	}
	getPluginState() {
		const { plugins } = this.uppy.getState();
		return plugins?.[this.id] || {};
	}
	setPluginState(update) {
		const { plugins } = this.uppy.getState();
		this.uppy.setState({ plugins: {
			...plugins,
			[this.id]: {
				...plugins[this.id],
				...update
			}
		} });
	}
	setOptions(newOpts) {
		this.opts = {
			...this.opts,
			...newOpts
		};
		this.setPluginState(void 0);
		this.i18nInit();
	}
	i18nInit() {
		const translator = new Translator([
			this.defaultLocale,
			this.uppy.locale,
			this.opts.locale
		]);
		this.i18n = translator.translate.bind(translator);
		this.i18nArray = translator.translateArray.bind(translator);
		this.setPluginState(void 0);
	}
	/**
	* Extendable methods
	* ==================
	* These methods are here to serve as an overview of the extendable methods as well as
	* making them not conditional in use, such as `if (this.afterUpdate)`.
	*/
	addTarget(plugin) {
		throw new Error("Extend the addTarget method to add your plugin to another plugin's target");
	}
	install() {}
	uninstall() {}
	update(state) {}
	afterUpdate() {}
};
//#endregion
//#region ../../../node_modules/@uppy/core/lib/EventManager.js
/**
* Create a wrapper around an event emitter with a `remove` method to remove
* all events that were added using the wrapped emitter.
*/
var EventManager = class {
	#uppy;
	#events = [];
	constructor(uppy) {
		this.#uppy = uppy;
	}
	on(event, fn) {
		this.#events.push([event, fn]);
		return this.#uppy.on(event, fn);
	}
	remove() {
		for (const [event, fn] of this.#events.splice(0)) this.#uppy.off(event, fn);
	}
	onFilePause(fileID, cb) {
		this.on("upload-pause", (file, isPaused) => {
			if (fileID === file?.id) cb(isPaused);
		});
	}
	onFileRemove(fileID, cb) {
		this.on("file-removed", (file) => {
			if (fileID === file.id) cb(file.id);
		});
	}
	onPause(fileID, cb) {
		this.on("upload-pause", (file, isPaused) => {
			if (fileID === file?.id) cb(isPaused);
		});
	}
	onRetry(fileID, cb) {
		this.on("upload-retry", (file) => {
			if (fileID === file?.id) cb();
		});
	}
	onRetryAll(fileID, cb) {
		this.on("retry-all", () => {
			if (!this.#uppy.getFile(fileID)) return;
			cb();
		});
	}
	onPauseAll(fileID, cb) {
		this.on("pause-all", () => {
			if (!this.#uppy.getFile(fileID)) return;
			cb();
		});
	}
	onCancelAll(fileID, eventHandler) {
		this.on("cancel-all", (...args) => {
			if (!this.#uppy.getFile(fileID)) return;
			eventHandler(...args);
		});
	}
	onResumeAll(fileID, cb) {
		this.on("resume-all", () => {
			if (!this.#uppy.getFile(fileID)) return;
			cb();
		});
	}
};
//#endregion
//#region ../../../node_modules/@uppy/core/lib/loggers.js
var justErrorsLogger = {
	debug: () => {},
	warn: () => {},
	error: (...args) => console.error(`[Uppy] [${getTimeStamp()}]`, ...args)
};
var debugLogger = {
	debug: (...args) => console.debug(`[Uppy] [${getTimeStamp()}]`, ...args),
	warn: (...args) => console.warn(`[Uppy] [${getTimeStamp()}]`, ...args),
	error: (...args) => console.error(`[Uppy] [${getTimeStamp()}]`, ...args)
};
//#endregion
//#region ../../../node_modules/@transloadit/prettier-bytes/dist/prettierBytes.js
var units = [
	"B",
	"KB",
	"MB",
	"GB",
	"TB",
	"PB",
	"EB",
	"ZB",
	"YB"
];
function prettierBytes(input, unit) {
	if (typeof input !== "number" || Number.isNaN(input)) throw new TypeError(`Expected a number, got ${typeof input}`);
	const neg = input < 0;
	let num = Math.abs(input);
	if (neg) num = -num;
	if (num === 0) return "0 B";
	const exponent = unit ? units.indexOf(unit) : Math.min(Math.floor(Math.log(num) / Math.log(1024)), units.length - 1);
	const value = Number(num / 1024 ** exponent);
	const displayUnit = units[exponent];
	if (exponent >= 3) return value % 1 === 0 ? `${Math.round(value)} ${displayUnit}` : `${value.toFixed(2)} ${displayUnit}`;
	return `${value >= 10 || value % 1 === 0 ? Math.round(value) : value.toFixed(1)} ${displayUnit}`;
}
//#endregion
//#region ../../../node_modules/wildcard/index.js
var require_wildcard = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/**
	# wildcard
	
	Very simple wildcard matching, which is designed to provide the same
	functionality that is found in the
	[eve](https://github.com/adobe-webplatform/eve) eventing library.
	
	## Usage
	
	It works with strings:
	
	<<< examples/strings.js
	
	Arrays:
	
	<<< examples/arrays.js
	
	Objects (matching against keys):
	
	<<< examples/objects.js
	
	While the library works in Node, if you are are looking for file-based
	wildcard matching then you should have a look at:
	
	<https://github.com/isaacs/node-glob>
	**/
	function WildcardMatcher(text, separator) {
		this.text = text = text || "";
		this.hasWild = ~text.indexOf("*");
		this.separator = separator;
		this.parts = text.split(separator);
	}
	WildcardMatcher.prototype.match = function(input) {
		var matches = true;
		var parts = this.parts;
		var ii;
		var partsCount = parts.length;
		var testParts;
		if (typeof input == "string" || input instanceof String) {
			if (!this.hasWild && this.text != input) matches = false;
			else {
				testParts = (input || "").split(this.separator);
				for (ii = 0; matches && ii < partsCount; ii++) if (parts[ii] === "*") continue;
				else if (ii < testParts.length) matches = parts[ii] === testParts[ii];
				else matches = false;
				matches = matches && testParts;
			}
		} else if (typeof input.splice == "function") {
			matches = [];
			for (ii = input.length; ii--;) if (this.match(input[ii])) matches[matches.length] = input[ii];
		} else if (typeof input == "object") {
			matches = {};
			for (var key in input) if (this.match(key)) matches[key] = input[key];
		}
		return matches;
	};
	module.exports = function(text, test, separator) {
		var matcher = new WildcardMatcher(text, separator || /[\/\.]/);
		if (typeof test != "undefined") return matcher.match(test);
		return matcher;
	};
}));
//#endregion
//#region ../../../node_modules/@uppy/core/lib/Restricter.js
var import_mime_match = /* @__PURE__ */ __toESM((/* @__PURE__ */ __commonJSMin(((exports, module) => {
	var wildcard = require_wildcard();
	var reMimePartSplit = /[\/\+\.]/;
	/**
	# mime-match
	
	A simple function to checker whether a target mime type matches a mime-type
	pattern (e.g. image/jpeg matches image/jpeg OR image/*).
	
	## Example Usage
	
	<<< example.js
	
	**/
	module.exports = function(target, pattern) {
		function test(pattern) {
			var result = wildcard(pattern, target, reMimePartSplit);
			return result && result.length >= 2;
		}
		return pattern ? test(pattern.split(";")[0]) : test;
	};
})))(), 1);
var defaultOptions = {
	maxFileSize: null,
	minFileSize: null,
	maxTotalFileSize: null,
	maxNumberOfFiles: null,
	minNumberOfFiles: null,
	allowedFileTypes: null,
	requiredMetaFields: []
};
var RestrictionError = class extends Error {
	isUserFacing;
	file;
	constructor(message, opts) {
		super(message);
		this.isUserFacing = opts?.isUserFacing ?? true;
		if (opts?.file) this.file = opts.file;
	}
	isRestriction = true;
};
var Restricter = class {
	getI18n;
	getOpts;
	constructor(getOpts, getI18n) {
		this.getI18n = getI18n;
		this.getOpts = () => {
			const opts = getOpts();
			if (opts.restrictions?.allowedFileTypes != null && !Array.isArray(opts.restrictions.allowedFileTypes)) throw new TypeError("`restrictions.allowedFileTypes` must be an array");
			return opts;
		};
	}
	validateAggregateRestrictions(existingFiles, addingFiles) {
		const { maxTotalFileSize, maxNumberOfFiles } = this.getOpts().restrictions;
		if (maxNumberOfFiles) {
			if (existingFiles.filter((f) => !f.isGhost).length + addingFiles.length > maxNumberOfFiles) throw new RestrictionError(`${this.getI18n()("youCanOnlyUploadX", { smart_count: maxNumberOfFiles })}`);
		}
		if (maxTotalFileSize) {
			const totalFilesSize = [...existingFiles, ...addingFiles].reduce((total, f) => total + (f.size ?? 0), 0);
			if (totalFilesSize > maxTotalFileSize) throw new RestrictionError(this.getI18n()("aggregateExceedsSize", {
				sizeAllowed: prettierBytes(maxTotalFileSize),
				size: prettierBytes(totalFilesSize)
			}));
		}
	}
	validateSingleFile(file) {
		const { maxFileSize, minFileSize, allowedFileTypes } = this.getOpts().restrictions;
		if (allowedFileTypes) {
			if (!allowedFileTypes.some((type) => {
				if (type.includes("/")) {
					if (!file.type) return false;
					return (0, import_mime_match.default)(file.type.replace(/;.*?$/, ""), type);
				}
				if (type[0] === "." && file.extension) return file.extension.toLowerCase() === type.slice(1).toLowerCase();
				return false;
			})) {
				const allowedFileTypesString = allowedFileTypes.join(", ");
				throw new RestrictionError(this.getI18n()("youCanOnlyUploadFileTypes", { types: allowedFileTypesString }), { file });
			}
		}
		if (maxFileSize && file.size != null && file.size > maxFileSize) throw new RestrictionError(this.getI18n()("exceedsSize", {
			size: prettierBytes(maxFileSize),
			file: file.name ?? this.getI18n()("unnamed")
		}), { file });
		if (minFileSize && file.size != null && file.size < minFileSize) throw new RestrictionError(this.getI18n()("inferiorSize", { size: prettierBytes(minFileSize) }), { file });
	}
	validate(existingFiles, addingFiles) {
		addingFiles.forEach((addingFile) => {
			this.validateSingleFile(addingFile);
		});
		this.validateAggregateRestrictions(existingFiles, addingFiles);
	}
	validateMinNumberOfFiles(files) {
		const { minNumberOfFiles } = this.getOpts().restrictions;
		if (minNumberOfFiles && Object.keys(files).length < minNumberOfFiles) throw new RestrictionError(this.getI18n()("youHaveToAtLeastSelectX", { smart_count: minNumberOfFiles }));
	}
	getMissingRequiredMetaFields(file) {
		const error = new RestrictionError(this.getI18n()("missingRequiredMetaFieldOnFile", { fileName: file.name ?? this.getI18n()("unnamed") }));
		const { requiredMetaFields } = this.getOpts().restrictions;
		const missingFields = [];
		for (const field of requiredMetaFields) if (!Object.hasOwn(file.meta, field) || file.meta[field] === "") missingFields.push(field);
		return {
			missingFields,
			error
		};
	}
};
//#endregion
//#region ../../../node_modules/@uppy/core/lib/UIPlugin.js
/**
* Defer a frequent call to the microtask queue.
*/
function debounce(fn) {
	let calling = null;
	let latestArgs;
	return (...args) => {
		latestArgs = args;
		if (!calling) calling = Promise.resolve().then(() => {
			calling = null;
			return fn(...latestArgs);
		});
		return calling;
	};
}
/**
* UIPlugin is the extended version of BasePlugin to incorporate rendering with Preact.
* Use this for plugins that need a user interface.
*
* For plugins without an user interface, see BasePlugin.
*/
var UIPlugin = class UIPlugin extends BasePlugin {
	#updateUI;
	isTargetDOMEl;
	el = null;
	parent;
	title;
	getTargetPlugin(target) {
		let targetPlugin;
		if (typeof target?.addTarget === "function") {
			targetPlugin = target;
			if (!(targetPlugin instanceof UIPlugin)) console.warn(new Error("The provided plugin is not an instance of UIPlugin. This is an indication of a bug with the way Uppy is bundled.", { cause: {
				targetPlugin,
				UIPlugin
			} }));
		} else if (typeof target === "function") {
			const Target = target;
			this.uppy.iteratePlugins((p) => {
				if (p instanceof Target) targetPlugin = p;
			});
		}
		return targetPlugin;
	}
	/**
	* Check if supplied `target` is a DOM element or an `object`.
	* If it’s an object — target is a plugin, and we search `plugins`
	* for a plugin with same name and return its target.
	*/
	mount(target, plugin) {
		const callerPluginName = plugin.id;
		const targetElement = findDOMElement(target);
		if (targetElement) {
			this.isTargetDOMEl = true;
			const uppyRootElement = document.createElement("div");
			uppyRootElement.classList.add("uppy-Root");
			this.#updateUI = debounce((state) => {
				if (!this.uppy.getPlugin(this.id)) return;
				R(this.render(state, uppyRootElement), uppyRootElement);
				this.afterUpdate();
			});
			this.uppy.log(`Installing ${callerPluginName} to a DOM element '${target}'`);
			if (this.opts.replaceTargetContent) targetElement.innerHTML = "";
			R(this.render(this.uppy.getState(), uppyRootElement), uppyRootElement);
			this.el = uppyRootElement;
			targetElement.appendChild(uppyRootElement);
			uppyRootElement.dir = this.opts.direction || getTextDirection(uppyRootElement) || "ltr";
			this.onMount();
			return this.el;
		}
		const targetPlugin = this.getTargetPlugin(target);
		if (targetPlugin) {
			this.uppy.log(`Installing ${callerPluginName} to ${targetPlugin.id}`);
			this.parent = targetPlugin;
			this.el = targetPlugin.addTarget(plugin);
			this.onMount();
			return this.el;
		}
		this.uppy.log(`Not installing ${callerPluginName}`);
		let message = `Invalid target option given to ${callerPluginName}.`;
		if (typeof target === "function") message += " The given target is not a Plugin class. Please check that you're not specifying a React Component instead of a plugin. If you are using @uppy/* packages directly, make sure you have only 1 version of @uppy/core installed: run `npm ls @uppy/core` on the command line and verify that all the versions match and are deduped correctly.";
		else message += "If you meant to target an HTML element, please make sure that the element exists. Check that the <script> tag initializing Uppy is right before the closing </body> tag at the end of the page. (see https://github.com/transloadit/uppy/issues/1042)\n\nIf you meant to target a plugin, please confirm that your `import` statements or `require` calls are correct.";
		throw new Error(message);
	}
	/**
	* Called when plugin is mounted, whether in DOM or into another plugin.
	* Needed because sometimes plugins are mounted separately/after `install`,
	* so this.el and this.parent might not be available in `install`.
	* This is the case with @uppy/react plugins, for example.
	*/
	render(state, container) {
		throw new Error("Extend the render method to add your plugin to a DOM element");
	}
	update(state) {
		if (this.el != null) this.#updateUI?.(state);
	}
	unmount() {
		if (this.isTargetDOMEl) this.el?.remove();
		this.onUnmount();
	}
	onMount() {}
	onUnmount() {}
};
//#endregion
//#region ../../../node_modules/lodash/isObject.js
var require_isObject = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/**
	* Checks if `value` is the
	* [language type](http://www.ecma-international.org/ecma-262/7.0/#sec-ecmascript-language-types)
	* of `Object`. (e.g. arrays, functions, objects, regexes, `new Number(0)`, and `new String('')`)
	*
	* @static
	* @memberOf _
	* @since 0.1.0
	* @category Lang
	* @param {*} value The value to check.
	* @returns {boolean} Returns `true` if `value` is an object, else `false`.
	* @example
	*
	* _.isObject({});
	* // => true
	*
	* _.isObject([1, 2, 3]);
	* // => true
	*
	* _.isObject(_.noop);
	* // => true
	*
	* _.isObject(null);
	* // => false
	*/
	function isObject(value) {
		var type = typeof value;
		return value != null && (type == "object" || type == "function");
	}
	module.exports = isObject;
}));
//#endregion
//#region ../../../node_modules/lodash/_freeGlobal.js
var require__freeGlobal = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	module.exports = typeof global == "object" && global && global.Object === Object && global;
}));
//#endregion
//#region ../../../node_modules/lodash/_root.js
var require__root = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var freeGlobal = require__freeGlobal();
	/** Detect free variable `self`. */
	var freeSelf = typeof self == "object" && self && self.Object === Object && self;
	module.exports = freeGlobal || freeSelf || Function("return this")();
}));
//#endregion
//#region ../../../node_modules/lodash/now.js
var require_now = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var root = require__root();
	/**
	* Gets the timestamp of the number of milliseconds that have elapsed since
	* the Unix epoch (1 January 1970 00:00:00 UTC).
	*
	* @static
	* @memberOf _
	* @since 2.4.0
	* @category Date
	* @returns {number} Returns the timestamp.
	* @example
	*
	* _.defer(function(stamp) {
	*   console.log(_.now() - stamp);
	* }, _.now());
	* // => Logs the number of milliseconds it took for the deferred invocation.
	*/
	var now = function() {
		return root.Date.now();
	};
	module.exports = now;
}));
//#endregion
//#region ../../../node_modules/lodash/_trimmedEndIndex.js
var require__trimmedEndIndex = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/** Used to match a single whitespace character. */
	var reWhitespace = /\s/;
	/**
	* Used by `_.trim` and `_.trimEnd` to get the index of the last non-whitespace
	* character of `string`.
	*
	* @private
	* @param {string} string The string to inspect.
	* @returns {number} Returns the index of the last non-whitespace character.
	*/
	function trimmedEndIndex(string) {
		var index = string.length;
		while (index-- && reWhitespace.test(string.charAt(index)));
		return index;
	}
	module.exports = trimmedEndIndex;
}));
//#endregion
//#region ../../../node_modules/lodash/_baseTrim.js
var require__baseTrim = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var trimmedEndIndex = require__trimmedEndIndex();
	/** Used to match leading whitespace. */
	var reTrimStart = /^\s+/;
	/**
	* The base implementation of `_.trim`.
	*
	* @private
	* @param {string} string The string to trim.
	* @returns {string} Returns the trimmed string.
	*/
	function baseTrim(string) {
		return string ? string.slice(0, trimmedEndIndex(string) + 1).replace(reTrimStart, "") : string;
	}
	module.exports = baseTrim;
}));
//#endregion
//#region ../../../node_modules/lodash/_Symbol.js
var require__Symbol = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	module.exports = require__root().Symbol;
}));
//#endregion
//#region ../../../node_modules/lodash/_getRawTag.js
var require__getRawTag = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var Symbol = require__Symbol();
	/** Used for built-in method references. */
	var objectProto = Object.prototype;
	/** Used to check objects for own properties. */
	var hasOwnProperty = objectProto.hasOwnProperty;
	/**
	* Used to resolve the
	* [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)
	* of values.
	*/
	var nativeObjectToString = objectProto.toString;
	/** Built-in value references. */
	var symToStringTag = Symbol ? Symbol.toStringTag : void 0;
	/**
	* A specialized version of `baseGetTag` which ignores `Symbol.toStringTag` values.
	*
	* @private
	* @param {*} value The value to query.
	* @returns {string} Returns the raw `toStringTag`.
	*/
	function getRawTag(value) {
		var isOwn = hasOwnProperty.call(value, symToStringTag), tag = value[symToStringTag];
		try {
			value[symToStringTag] = void 0;
			var unmasked = true;
		} catch (e) {}
		var result = nativeObjectToString.call(value);
		if (unmasked) {
			if (isOwn) value[symToStringTag] = tag;
			else delete value[symToStringTag];
		}
		return result;
	}
	module.exports = getRawTag;
}));
//#endregion
//#region ../../../node_modules/lodash/_objectToString.js
var require__objectToString = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/**
	* Used to resolve the
	* [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)
	* of values.
	*/
	var nativeObjectToString = Object.prototype.toString;
	/**
	* Converts `value` to a string using `Object.prototype.toString`.
	*
	* @private
	* @param {*} value The value to convert.
	* @returns {string} Returns the converted string.
	*/
	function objectToString(value) {
		return nativeObjectToString.call(value);
	}
	module.exports = objectToString;
}));
//#endregion
//#region ../../../node_modules/lodash/_baseGetTag.js
var require__baseGetTag = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var Symbol = require__Symbol();
	var getRawTag = require__getRawTag();
	var objectToString = require__objectToString();
	/** `Object#toString` result references. */
	var nullTag = "[object Null]";
	var undefinedTag = "[object Undefined]";
	/** Built-in value references. */
	var symToStringTag = Symbol ? Symbol.toStringTag : void 0;
	/**
	* The base implementation of `getTag` without fallbacks for buggy environments.
	*
	* @private
	* @param {*} value The value to query.
	* @returns {string} Returns the `toStringTag`.
	*/
	function baseGetTag(value) {
		if (value == null) return value === void 0 ? undefinedTag : nullTag;
		return symToStringTag && symToStringTag in Object(value) ? getRawTag(value) : objectToString(value);
	}
	module.exports = baseGetTag;
}));
//#endregion
//#region ../../../node_modules/lodash/isObjectLike.js
var require_isObjectLike = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/**
	* Checks if `value` is object-like. A value is object-like if it's not `null`
	* and has a `typeof` result of "object".
	*
	* @static
	* @memberOf _
	* @since 4.0.0
	* @category Lang
	* @param {*} value The value to check.
	* @returns {boolean} Returns `true` if `value` is object-like, else `false`.
	* @example
	*
	* _.isObjectLike({});
	* // => true
	*
	* _.isObjectLike([1, 2, 3]);
	* // => true
	*
	* _.isObjectLike(_.noop);
	* // => false
	*
	* _.isObjectLike(null);
	* // => false
	*/
	function isObjectLike(value) {
		return value != null && typeof value == "object";
	}
	module.exports = isObjectLike;
}));
//#endregion
//#region ../../../node_modules/lodash/isSymbol.js
var require_isSymbol = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var baseGetTag = require__baseGetTag();
	var isObjectLike = require_isObjectLike();
	/** `Object#toString` result references. */
	var symbolTag = "[object Symbol]";
	/**
	* Checks if `value` is classified as a `Symbol` primitive or object.
	*
	* @static
	* @memberOf _
	* @since 4.0.0
	* @category Lang
	* @param {*} value The value to check.
	* @returns {boolean} Returns `true` if `value` is a symbol, else `false`.
	* @example
	*
	* _.isSymbol(Symbol.iterator);
	* // => true
	*
	* _.isSymbol('abc');
	* // => false
	*/
	function isSymbol(value) {
		return typeof value == "symbol" || isObjectLike(value) && baseGetTag(value) == symbolTag;
	}
	module.exports = isSymbol;
}));
//#endregion
//#region ../../../node_modules/lodash/toNumber.js
var require_toNumber = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var baseTrim = require__baseTrim();
	var isObject = require_isObject();
	var isSymbol = require_isSymbol();
	/** Used as references for various `Number` constants. */
	var NAN = NaN;
	/** Used to detect bad signed hexadecimal string values. */
	var reIsBadHex = /^[-+]0x[0-9a-f]+$/i;
	/** Used to detect binary string values. */
	var reIsBinary = /^0b[01]+$/i;
	/** Used to detect octal string values. */
	var reIsOctal = /^0o[0-7]+$/i;
	/** Built-in method references without a dependency on `root`. */
	var freeParseInt = parseInt;
	/**
	* Converts `value` to a number.
	*
	* @static
	* @memberOf _
	* @since 4.0.0
	* @category Lang
	* @param {*} value The value to process.
	* @returns {number} Returns the number.
	* @example
	*
	* _.toNumber(3.2);
	* // => 3.2
	*
	* _.toNumber(Number.MIN_VALUE);
	* // => 5e-324
	*
	* _.toNumber(Infinity);
	* // => Infinity
	*
	* _.toNumber('3.2');
	* // => 3.2
	*/
	function toNumber(value) {
		if (typeof value == "number") return value;
		if (isSymbol(value)) return NAN;
		if (isObject(value)) {
			var other = typeof value.valueOf == "function" ? value.valueOf() : value;
			value = isObject(other) ? other + "" : other;
		}
		if (typeof value != "string") return value === 0 ? value : +value;
		value = baseTrim(value);
		var isBinary = reIsBinary.test(value);
		return isBinary || reIsOctal.test(value) ? freeParseInt(value.slice(2), isBinary ? 2 : 8) : reIsBadHex.test(value) ? NAN : +value;
	}
	module.exports = toNumber;
}));
//#endregion
//#region ../../../node_modules/lodash/debounce.js
var require_debounce = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var isObject = require_isObject();
	var now = require_now();
	var toNumber = require_toNumber();
	/** Error message constants. */
	var FUNC_ERROR_TEXT = "Expected a function";
	var nativeMax = Math.max;
	var nativeMin = Math.min;
	/**
	* Creates a debounced function that delays invoking `func` until after `wait`
	* milliseconds have elapsed since the last time the debounced function was
	* invoked. The debounced function comes with a `cancel` method to cancel
	* delayed `func` invocations and a `flush` method to immediately invoke them.
	* Provide `options` to indicate whether `func` should be invoked on the
	* leading and/or trailing edge of the `wait` timeout. The `func` is invoked
	* with the last arguments provided to the debounced function. Subsequent
	* calls to the debounced function return the result of the last `func`
	* invocation.
	*
	* **Note:** If `leading` and `trailing` options are `true`, `func` is
	* invoked on the trailing edge of the timeout only if the debounced function
	* is invoked more than once during the `wait` timeout.
	*
	* If `wait` is `0` and `leading` is `false`, `func` invocation is deferred
	* until to the next tick, similar to `setTimeout` with a timeout of `0`.
	*
	* See [David Corbacho's article](https://css-tricks.com/debouncing-throttling-explained-examples/)
	* for details over the differences between `_.debounce` and `_.throttle`.
	*
	* @static
	* @memberOf _
	* @since 0.1.0
	* @category Function
	* @param {Function} func The function to debounce.
	* @param {number} [wait=0] The number of milliseconds to delay.
	* @param {Object} [options={}] The options object.
	* @param {boolean} [options.leading=false]
	*  Specify invoking on the leading edge of the timeout.
	* @param {number} [options.maxWait]
	*  The maximum time `func` is allowed to be delayed before it's invoked.
	* @param {boolean} [options.trailing=true]
	*  Specify invoking on the trailing edge of the timeout.
	* @returns {Function} Returns the new debounced function.
	* @example
	*
	* // Avoid costly calculations while the window size is in flux.
	* jQuery(window).on('resize', _.debounce(calculateLayout, 150));
	*
	* // Invoke `sendMail` when clicked, debouncing subsequent calls.
	* jQuery(element).on('click', _.debounce(sendMail, 300, {
	*   'leading': true,
	*   'trailing': false
	* }));
	*
	* // Ensure `batchLog` is invoked once after 1 second of debounced calls.
	* var debounced = _.debounce(batchLog, 250, { 'maxWait': 1000 });
	* var source = new EventSource('/stream');
	* jQuery(source).on('message', debounced);
	*
	* // Cancel the trailing debounced invocation.
	* jQuery(window).on('popstate', debounced.cancel);
	*/
	function debounce(func, wait, options) {
		var lastArgs, lastThis, maxWait, result, timerId, lastCallTime, lastInvokeTime = 0, leading = false, maxing = false, trailing = true;
		if (typeof func != "function") throw new TypeError(FUNC_ERROR_TEXT);
		wait = toNumber(wait) || 0;
		if (isObject(options)) {
			leading = !!options.leading;
			maxing = "maxWait" in options;
			maxWait = maxing ? nativeMax(toNumber(options.maxWait) || 0, wait) : maxWait;
			trailing = "trailing" in options ? !!options.trailing : trailing;
		}
		function invokeFunc(time) {
			var args = lastArgs, thisArg = lastThis;
			lastArgs = lastThis = void 0;
			lastInvokeTime = time;
			result = func.apply(thisArg, args);
			return result;
		}
		function leadingEdge(time) {
			lastInvokeTime = time;
			timerId = setTimeout(timerExpired, wait);
			return leading ? invokeFunc(time) : result;
		}
		function remainingWait(time) {
			var timeSinceLastCall = time - lastCallTime, timeSinceLastInvoke = time - lastInvokeTime, timeWaiting = wait - timeSinceLastCall;
			return maxing ? nativeMin(timeWaiting, maxWait - timeSinceLastInvoke) : timeWaiting;
		}
		function shouldInvoke(time) {
			var timeSinceLastCall = time - lastCallTime, timeSinceLastInvoke = time - lastInvokeTime;
			return lastCallTime === void 0 || timeSinceLastCall >= wait || timeSinceLastCall < 0 || maxing && timeSinceLastInvoke >= maxWait;
		}
		function timerExpired() {
			var time = now();
			if (shouldInvoke(time)) return trailingEdge(time);
			timerId = setTimeout(timerExpired, remainingWait(time));
		}
		function trailingEdge(time) {
			timerId = void 0;
			if (trailing && lastArgs) return invokeFunc(time);
			lastArgs = lastThis = void 0;
			return result;
		}
		function cancel() {
			if (timerId !== void 0) clearTimeout(timerId);
			lastInvokeTime = 0;
			lastArgs = lastCallTime = lastThis = timerId = void 0;
		}
		function flush() {
			return timerId === void 0 ? result : trailingEdge(now());
		}
		function debounced() {
			var time = now(), isInvoking = shouldInvoke(time);
			lastArgs = arguments;
			lastThis = this;
			lastCallTime = time;
			if (isInvoking) {
				if (timerId === void 0) return leadingEdge(lastCallTime);
				if (maxing) {
					clearTimeout(timerId);
					timerId = setTimeout(timerExpired, wait);
					return invokeFunc(lastCallTime);
				}
			}
			if (timerId === void 0) timerId = setTimeout(timerExpired, wait);
			return result;
		}
		debounced.cancel = cancel;
		debounced.flush = flush;
		return debounced;
	}
	module.exports = debounce;
}));
//#endregion
//#region ../../../node_modules/lodash/throttle.js
var require_throttle = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	var debounce = require_debounce();
	var isObject = require_isObject();
	/** Error message constants. */
	var FUNC_ERROR_TEXT = "Expected a function";
	/**
	* Creates a throttled function that only invokes `func` at most once per
	* every `wait` milliseconds. The throttled function comes with a `cancel`
	* method to cancel delayed `func` invocations and a `flush` method to
	* immediately invoke them. Provide `options` to indicate whether `func`
	* should be invoked on the leading and/or trailing edge of the `wait`
	* timeout. The `func` is invoked with the last arguments provided to the
	* throttled function. Subsequent calls to the throttled function return the
	* result of the last `func` invocation.
	*
	* **Note:** If `leading` and `trailing` options are `true`, `func` is
	* invoked on the trailing edge of the timeout only if the throttled function
	* is invoked more than once during the `wait` timeout.
	*
	* If `wait` is `0` and `leading` is `false`, `func` invocation is deferred
	* until to the next tick, similar to `setTimeout` with a timeout of `0`.
	*
	* See [David Corbacho's article](https://css-tricks.com/debouncing-throttling-explained-examples/)
	* for details over the differences between `_.throttle` and `_.debounce`.
	*
	* @static
	* @memberOf _
	* @since 0.1.0
	* @category Function
	* @param {Function} func The function to throttle.
	* @param {number} [wait=0] The number of milliseconds to throttle invocations to.
	* @param {Object} [options={}] The options object.
	* @param {boolean} [options.leading=true]
	*  Specify invoking on the leading edge of the timeout.
	* @param {boolean} [options.trailing=true]
	*  Specify invoking on the trailing edge of the timeout.
	* @returns {Function} Returns the new throttled function.
	* @example
	*
	* // Avoid excessively updating the position while scrolling.
	* jQuery(window).on('scroll', _.throttle(updatePosition, 100));
	*
	* // Invoke `renewToken` when the click event is fired, but not more than once every 5 minutes.
	* var throttled = _.throttle(renewToken, 300000, { 'trailing': false });
	* jQuery(element).on('click', throttled);
	*
	* // Cancel the trailing throttled invocation.
	* jQuery(window).on('popstate', throttled.cancel);
	*/
	function throttle(func, wait, options) {
		var leading = true, trailing = true;
		if (typeof func != "function") throw new TypeError(FUNC_ERROR_TEXT);
		if (isObject(options)) {
			leading = "leading" in options ? !!options.leading : leading;
			trailing = "trailing" in options ? !!options.trailing : trailing;
		}
		return debounce(func, wait, {
			"leading": leading,
			"maxWait": wait,
			"trailing": trailing
		});
	}
	module.exports = throttle;
}));
//#endregion
//#region ../../../node_modules/namespace-emitter/index.js
var require_namespace_emitter = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	/**
	* Create an event emitter with namespaces
	* @name createNamespaceEmitter
	* @example
	* var emitter = require('./index')()
	*
	* emitter.on('*', function () {
	*   console.log('all events emitted', this.event)
	* })
	*
	* emitter.on('example', function () {
	*   console.log('example event emitted')
	* })
	*/
	module.exports = function createNamespaceEmitter() {
		var emitter = {};
		var _fns = emitter._fns = {};
		/**
		* Emit an event. Optionally namespace the event. Handlers are fired in the order in which they were added with exact matches taking precedence. Separate the namespace and event with a `:`
		* @name emit
		* @param {String} event – the name of the event, with optional namespace
		* @param {...*} data – up to 6 arguments that are passed to the event listener
		* @example
		* emitter.emit('example')
		* emitter.emit('demo:test')
		* emitter.emit('data', { example: true}, 'a string', 1)
		*/
		emitter.emit = function emit(event, arg1, arg2, arg3, arg4, arg5, arg6) {
			var toEmit = getListeners(event);
			if (toEmit.length) emitAll(event, toEmit, [
				arg1,
				arg2,
				arg3,
				arg4,
				arg5,
				arg6
			]);
		};
		/**
		* Create en event listener.
		* @name on
		* @param {String} event
		* @param {Function} fn
		* @example
		* emitter.on('example', function () {})
		* emitter.on('demo', function () {})
		*/
		emitter.on = function on(event, fn) {
			if (!_fns[event]) _fns[event] = [];
			_fns[event].push(fn);
		};
		/**
		* Create en event listener that fires once.
		* @name once
		* @param {String} event
		* @param {Function} fn
		* @example
		* emitter.once('example', function () {})
		* emitter.once('demo', function () {})
		*/
		emitter.once = function once(event, fn) {
			function one() {
				fn.apply(this, arguments);
				emitter.off(event, one);
			}
			this.on(event, one);
		};
		/**
		* Stop listening to an event. Stop all listeners on an event by only passing the event name. Stop a single listener by passing that event handler as a callback.
		* You must be explicit about what will be unsubscribed: `emitter.off('demo')` will unsubscribe an `emitter.on('demo')` listener,
		* `emitter.off('demo:example')` will unsubscribe an `emitter.on('demo:example')` listener
		* @name off
		* @param {String} event
		* @param {Function} [fn] – the specific handler
		* @example
		* emitter.off('example')
		* emitter.off('demo', function () {})
		*/
		emitter.off = function off(event, fn) {
			var keep = [];
			if (event && fn) {
				var fns = this._fns[event];
				var i = 0;
				var l = fns ? fns.length : 0;
				for (; i < l; i++) if (fns[i] !== fn) keep.push(fns[i]);
			}
			keep.length ? this._fns[event] = keep : delete this._fns[event];
		};
		function getListeners(e) {
			var out = _fns[e] ? _fns[e] : [];
			var idx = e.indexOf(":");
			var args = idx === -1 ? [e] : [e.substring(0, idx), e.substring(idx + 1)];
			var keys = Object.keys(_fns);
			var i = 0;
			var l = keys.length;
			for (; i < l; i++) {
				var key = keys[i];
				if (key === "*") out = out.concat(_fns[key]);
				if (args.length === 2 && args[0] === key) {
					out = out.concat(_fns[key]);
					break;
				}
			}
			return out;
		}
		function emitAll(e, fns, args) {
			var i = 0;
			var l = fns.length;
			for (; i < l; i++) {
				if (!fns[i]) break;
				fns[i].event = e;
				fns[i].apply(fns[i], args);
			}
		}
		return emitter;
	};
}));
//#endregion
//#region ../../../node_modules/nanoid/non-secure/index.js
var import_throttle = /* @__PURE__ */ __toESM(require_throttle(), 1);
var import_namespace_emitter = /* @__PURE__ */ __toESM(require_namespace_emitter(), 1);
var urlAlphabet = "useandom-26T198340PX75pxJACKVERYMINDBUSHWOLF_GQZbfghjklqvwyzrict";
var nanoid = (size = 21) => {
	let id = "";
	let i = size | 0;
	while (i-- > 0) id += urlAlphabet[Math.random() * 64 | 0];
	return id;
};
//#endregion
//#region ../../../node_modules/@uppy/core/package.json
var version = "6.0.0";
//#endregion
//#region ../../../node_modules/@uppy/core/lib/getFileName.js
function getFileName(fileType, fileDescriptor) {
	if (fileDescriptor.name) return fileDescriptor.name;
	if (fileType.split("/")[0] === "image") return `${fileType.split("/")[0]}.${fileType.split("/")[1]}`;
	return "noname";
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/locale.js
var locale_default = { strings: {
	addBulkFilesFailed: {
		0: "Failed to add %{smart_count} file due to an internal error",
		1: "Failed to add %{smart_count} files due to internal errors"
	},
	youCanOnlyUploadX: {
		0: "You can only upload %{smart_count} file",
		1: "You can only upload %{smart_count} files"
	},
	youHaveToAtLeastSelectX: {
		0: "You have to select at least %{smart_count} file",
		1: "You have to select at least %{smart_count} files"
	},
	aggregateExceedsSize: "You selected %{size} of files, but maximum allowed size is %{sizeAllowed}",
	exceedsSize: "%{file} exceeds maximum allowed size of %{size}",
	missingRequiredMetaField: "Missing required meta fields",
	missingRequiredMetaFieldOnFile: "Missing required meta fields in %{fileName}",
	inferiorSize: "This file is smaller than the allowed size of %{size}",
	youCanOnlyUploadFileTypes: "You can only upload: %{types}",
	noMoreFilesAllowed: "Cannot add more files",
	noDuplicates: "Cannot add the duplicate file '%{fileName}', it already exists",
	companionError: "Connection with Companion failed",
	authAborted: "Authentication aborted",
	companionUnauthorizeHint: "To unauthorize to your %{provider} account, please go to %{url}",
	failedToUpload: "Failed to upload %{file}",
	noInternetConnection: "No Internet connection",
	connectedToInternet: "Connected to the Internet",
	noFilesFound: "You have no files or folders here",
	noSearchResults: "Unfortunately, there are no results for this search",
	selectX: {
		0: "Select %{smart_count}",
		1: "Select %{smart_count}"
	},
	allFilesFromFolderNamed: "All files from folder %{name}",
	openFolderNamed: "Open folder %{name}",
	cancel: "Cancel",
	logOut: "Log out",
	logIn: "Log in",
	pickFiles: "Pick files",
	pickPhotos: "Pick photos",
	filter: "Filter",
	resetFilter: "Reset filter",
	loading: "Loading...",
	loadedXFiles: "Loaded %{numFiles} files",
	authenticateWithTitle: "Please authenticate with %{pluginName} to select files",
	authenticateWith: "Connect to %{pluginName}",
	signInWithGoogle: "Sign in with Google",
	searchImages: "Search for images",
	enterTextToSearch: "Enter text to search for images",
	search: "Search",
	resetSearch: "Reset search",
	emptyFolderAdded: "No files were added from empty folder",
	addedNumFiles: "Added %{numFiles} file(s)",
	folderAlreadyAdded: "The folder \"%{folder}\" was already added",
	folderAdded: {
		0: "Added %{smart_count} file from %{folder}",
		1: "Added %{smart_count} files from %{folder}"
	},
	additionalRestrictionsFailed: "%{count} additional restrictions were not fulfilled",
	unnamed: "Unnamed",
	pleaseWait: "Please wait"
} };
//#endregion
//#region ../../../node_modules/@uppy/core/lib/store/index.js
/**
* Default store that keeps state in a simple object.
*/
var DefaultStore = class {
	static VERSION = version;
	state = {};
	#callbacks = /* @__PURE__ */ new Set();
	getState() {
		return this.state;
	}
	setState(patch) {
		const prevState = { ...this.state };
		const nextState = {
			...this.state,
			...patch
		};
		this.state = nextState;
		this.#publish(prevState, nextState, patch);
	}
	subscribe(listener) {
		this.#callbacks.add(listener);
		return () => {
			this.#callbacks.delete(listener);
		};
	}
	#publish(...args) {
		this.#callbacks.forEach((listener) => {
			listener(...args);
		});
	}
};
//#endregion
//#region ../../../node_modules/@uppy/core/lib/supportsUploadProgress.js
function supportsUploadProgress(userAgent) {
	if (userAgent == null && typeof navigator !== "undefined") userAgent = navigator.userAgent;
	if (!userAgent) return true;
	const m = /Edge\/(\d+\.\d+)/.exec(userAgent);
	if (!m) return true;
	const version = m[1].split(".", 2);
	const major = parseInt(version[0], 10);
	const minor = parseInt(version[1], 10);
	if (major < 15 || major === 15 && minor < 15063) return true;
	if (major > 18 || major === 18 && minor >= 18218) return true;
	return false;
}
//#endregion
//#region ../../../node_modules/@uppy/core/lib/Uppy.js
var defaultUploadState = {
	totalProgress: 0,
	allowNewUpload: true,
	error: null,
	recoveredState: null
};
/**
* Uppy Core module.
* Manages plugins, state updates, acts as an event bus,
* adds/removes files and metadata.
*/
var Uppy = class Uppy {
	static VERSION = version;
	#plugins = Object.create(null);
	#restricter;
	#storeUnsubscribe;
	#emitter = (0, import_namespace_emitter.default)();
	#preProcessors = /* @__PURE__ */ new Set();
	#uploaders = /* @__PURE__ */ new Set();
	#postProcessors = /* @__PURE__ */ new Set();
	defaultLocale;
	locale;
	opts;
	store;
	i18n;
	i18nArray;
	scheduledAutoProceed = null;
	wasOffline = false;
	/**
	* Instantiate Uppy
	*/
	constructor(opts) {
		this.defaultLocale = locale_default;
		const defaultOptions$1 = {
			id: "uppy",
			autoProceed: false,
			allowMultipleUploadBatches: true,
			debug: false,
			restrictions: defaultOptions,
			meta: {},
			onBeforeFileAdded: (file, files) => !Object.hasOwn(files, file.id),
			onBeforeUpload: (files) => files,
			store: new DefaultStore(),
			logger: justErrorsLogger,
			infoTimeout: 5e3
		};
		const merged = {
			...defaultOptions$1,
			...opts
		};
		this.opts = {
			...merged,
			restrictions: {
				...defaultOptions$1.restrictions,
				...opts?.restrictions
			}
		};
		if (opts?.logger && opts.debug) this.log("You are using a custom `logger`, but also set `debug: true`, which uses built-in logger to output logs to console. Ignoring `debug: true` and using your custom `logger`.", "warning");
		else if (opts?.debug) this.opts.logger = debugLogger;
		this.log(`Using Core v${Uppy.VERSION}`);
		this.i18nInit();
		this.store = this.opts.store;
		this.setState({
			...defaultUploadState,
			plugins: {},
			files: {},
			currentUploads: {},
			capabilities: {
				uploadProgress: supportsUploadProgress(),
				individualCancellation: true,
				resumableUploads: false
			},
			meta: { ...this.opts.meta },
			info: []
		});
		this.#restricter = new Restricter(() => this.opts, () => this.i18n);
		this.#storeUnsubscribe = this.store.subscribe((prevState, nextState, patch) => {
			this.emit("state-update", prevState, nextState, patch);
			this.updateAll(nextState);
		});
		if (this.opts.debug && typeof window !== "undefined") window[this.opts.id] = this;
		this.#addListeners();
	}
	emit(event, ...args) {
		this.#emitter.emit(event, ...args);
	}
	on(event, callback) {
		this.#emitter.on(event, callback);
		return this;
	}
	once(event, callback) {
		this.#emitter.once(event, callback);
		return this;
	}
	off(event, callback) {
		this.#emitter.off(event, callback);
		return this;
	}
	/**
	* Iterate on all plugins and run `update` on them.
	* Called each time state changes.
	*
	*/
	updateAll(state) {
		this.iteratePlugins((plugin) => {
			plugin.update(state);
		});
	}
	/**
	* Updates state with a patch
	*/
	setState(patch) {
		this.store.setState(patch);
	}
	/**
	* Returns current state.
	*/
	getState() {
		return this.store.getState();
	}
	patchFilesState(filesWithNewState) {
		const existingFilesState = this.getState().files;
		this.setState({ files: {
			...existingFilesState,
			...Object.fromEntries(Object.entries(filesWithNewState).map(([fileID, newFileState]) => [fileID, {
				...existingFilesState[fileID],
				...newFileState
			}]))
		} });
	}
	/**
	* Shorthand to set state for a specific file.
	*/
	setFileState(fileID, state) {
		if (!this.getState().files[fileID]) throw new Error(`Can’t set state for ${fileID} (the file could have been removed)`);
		this.patchFilesState({ [fileID]: state });
	}
	i18nInit() {
		const onMissingKey = (key) => this.log(`Missing i18n string: ${key}`, "error");
		const translator = new Translator([this.defaultLocale, this.opts.locale], { onMissingKey });
		this.i18n = translator.translate.bind(translator);
		this.i18nArray = translator.translateArray.bind(translator);
		this.locale = translator.locale;
	}
	setOptions(newOpts) {
		this.opts = {
			...this.opts,
			...newOpts,
			restrictions: {
				...this.opts.restrictions,
				...newOpts?.restrictions
			}
		};
		if (newOpts.meta) this.setMeta(newOpts.meta);
		this.i18nInit();
		if (newOpts.locale) this.iteratePlugins((plugin) => {
			plugin.setOptions(newOpts);
		});
		this.setState(void 0);
	}
	resetProgress() {
		const defaultProgress = {
			percentage: 0,
			bytesUploaded: false,
			uploadComplete: false,
			uploadStarted: null
		};
		const files = { ...this.getState().files };
		const updatedFiles = Object.create(null);
		Object.keys(files).forEach((fileID) => {
			updatedFiles[fileID] = {
				...files[fileID],
				progress: {
					...files[fileID].progress,
					...defaultProgress
				},
				tus: void 0,
				transloadit: void 0,
				s3Multipart: void 0
			};
		});
		this.setState({
			files: updatedFiles,
			...defaultUploadState
		});
	}
	clear() {
		const { capabilities, currentUploads } = this.getState();
		if (Object.keys(currentUploads).length > 0 && !capabilities.individualCancellation) throw new Error("The installed uploader plugin does not allow removing files during an upload.");
		this.setState({
			...defaultUploadState,
			files: {}
		});
	}
	addPreProcessor(fn) {
		this.#preProcessors.add(fn);
	}
	removePreProcessor(fn) {
		return this.#preProcessors.delete(fn);
	}
	addPostProcessor(fn) {
		this.#postProcessors.add(fn);
	}
	removePostProcessor(fn) {
		return this.#postProcessors.delete(fn);
	}
	addUploader(fn) {
		this.#uploaders.add(fn);
	}
	removeUploader(fn) {
		return this.#uploaders.delete(fn);
	}
	setMeta(data) {
		const updatedMeta = {
			...this.getState().meta,
			...data
		};
		const updatedFiles = { ...this.getState().files };
		Object.keys(updatedFiles).forEach((fileID) => {
			updatedFiles[fileID] = {
				...updatedFiles[fileID],
				meta: {
					...updatedFiles[fileID].meta,
					...data
				}
			};
		});
		this.log("Adding metadata:");
		this.log(data);
		this.setState({
			meta: updatedMeta,
			files: updatedFiles
		});
	}
	setFileMeta(fileID, data) {
		const updatedFiles = { ...this.getState().files };
		if (!updatedFiles[fileID]) {
			this.log(`Was trying to set metadata for a file that has been removed: ${fileID}`);
			return;
		}
		const newMeta = {
			...updatedFiles[fileID].meta,
			...data
		};
		updatedFiles[fileID] = {
			...updatedFiles[fileID],
			meta: newMeta
		};
		this.setState({ files: updatedFiles });
	}
	/**
	* Get a file object.
	*/
	getFile(fileID) {
		return this.getState().files[fileID];
	}
	/**
	* Get all files in an array.
	*/
	getFiles() {
		const { files } = this.getState();
		return Object.values(files);
	}
	getFilesByIds(ids) {
		return ids.map((id) => this.getFile(id));
	}
	getObjectOfFilesPerState() {
		const { files: filesObject, totalProgress, error } = this.getState();
		const files = Object.values(filesObject);
		const inProgressFiles = [];
		const newFiles = [];
		const startedFiles = [];
		const uploadStartedFiles = [];
		const pausedFiles = [];
		const completeFiles = [];
		const erroredFiles = [];
		const inProgressNotPausedFiles = [];
		const processingFiles = [];
		for (const file of files) {
			const { progress } = file;
			if (!progress.uploadComplete && progress.uploadStarted) {
				inProgressFiles.push(file);
				if (!file.isPaused) inProgressNotPausedFiles.push(file);
			}
			if (!progress.uploadStarted) newFiles.push(file);
			if (progress.uploadStarted || progress.preprocess || progress.postprocess) startedFiles.push(file);
			if (progress.uploadStarted) uploadStartedFiles.push(file);
			if (file.isPaused) pausedFiles.push(file);
			if (progress.uploadComplete) completeFiles.push(file);
			if (file.error) erroredFiles.push(file);
			if (progress.preprocess || progress.postprocess) processingFiles.push(file);
		}
		return {
			newFiles,
			startedFiles,
			uploadStartedFiles,
			pausedFiles,
			completeFiles,
			erroredFiles,
			inProgressFiles,
			inProgressNotPausedFiles,
			processingFiles,
			isUploadStarted: uploadStartedFiles.length > 0,
			isAllComplete: totalProgress === 100 && completeFiles.length === files.length && processingFiles.length === 0,
			isAllErrored: !!error && erroredFiles.length === files.length,
			isAllPaused: inProgressFiles.length !== 0 && pausedFiles.length === inProgressFiles.length,
			isUploadInProgress: inProgressFiles.length > 0,
			isSomeGhost: files.some((file) => file.isGhost)
		};
	}
	#informAndEmit(errors) {
		for (const error of errors) {
			if (error.isRestriction) this.emit("restriction-failed", error.file, error);
			else this.emit("error", error, error.file);
			this.log(error, "warning");
		}
		const userFacingErrors = errors.filter((error) => error.isUserFacing);
		const maxNumToShow = 4;
		const firstErrors = userFacingErrors.slice(0, maxNumToShow);
		const additionalErrors = userFacingErrors.slice(maxNumToShow);
		firstErrors.forEach(({ message, details = "" }) => {
			this.info({
				message,
				details
			}, "error", this.opts.infoTimeout);
		});
		if (additionalErrors.length > 0) this.info({ message: this.i18n("additionalRestrictionsFailed", { count: additionalErrors.length }) });
	}
	validateRestrictions(file, files = this.getFiles()) {
		try {
			this.#restricter.validate(files, [file]);
		} catch (err) {
			return err;
		}
		return null;
	}
	validateSingleFile(file) {
		try {
			this.#restricter.validateSingleFile(file);
		} catch (err) {
			return err.message;
		}
		return null;
	}
	validateAggregateRestrictions(files) {
		const existingFiles = this.getFiles();
		try {
			this.#restricter.validateAggregateRestrictions(existingFiles, files);
		} catch (err) {
			return err.message;
		}
		return null;
	}
	#checkRequiredMetaFieldsOnFile(file) {
		const { missingFields, error } = this.#restricter.getMissingRequiredMetaFields(file);
		if (missingFields.length > 0) {
			this.setFileState(file.id, {
				missingRequiredMetaFields: missingFields,
				error: error.message
			});
			this.log(error.message);
			this.emit("restriction-failed", file, error);
			return false;
		}
		if (missingFields.length === 0 && file.missingRequiredMetaFields) this.setFileState(file.id, { missingRequiredMetaFields: [] });
		return true;
	}
	#checkRequiredMetaFields(files) {
		let success = true;
		for (const file of Object.values(files)) if (!this.#checkRequiredMetaFieldsOnFile(file)) success = false;
		return success;
	}
	#assertNewUploadAllowed(file) {
		const { allowNewUpload } = this.getState();
		if (allowNewUpload === false) {
			const error = new RestrictionError(this.i18n("noMoreFilesAllowed"), { file });
			this.#informAndEmit([error]);
			throw error;
		}
	}
	checkIfFileAlreadyExists(fileID) {
		const { files } = this.getState();
		if (files[fileID] && !files[fileID].isGhost) return true;
		return false;
	}
	/**
	* Create a file state object based on user-provided `addFile()` options.
	*/
	#transformFile(fileDescriptorOrFile) {
		const file = fileDescriptorOrFile instanceof File ? {
			name: fileDescriptorOrFile.name,
			type: fileDescriptorOrFile.type,
			size: fileDescriptorOrFile.size,
			data: fileDescriptorOrFile,
			meta: {},
			isRemote: false,
			source: void 0,
			preview: void 0
		} : fileDescriptorOrFile;
		const fileType = getFileType(file);
		const fileName = getFileName(fileType, file);
		const fileExtension = getFileNameAndExtension(fileName).extension;
		const id = getSafeFileId(file, this.getID());
		const meta = {
			...file.meta,
			name: fileName,
			type: fileType
		};
		const size = Number.isFinite(file.data.size) ? file.data.size : null;
		return {
			source: file.source || "",
			id,
			name: fileName,
			extension: fileExtension || "",
			meta: {
				...this.getState().meta,
				...meta
			},
			type: fileType,
			progress: {
				percentage: 0,
				bytesUploaded: false,
				bytesTotal: size,
				uploadComplete: false,
				uploadStarted: null
			},
			size,
			isGhost: false,
			...file.isRemote ? {
				isRemote: true,
				remote: file.remote,
				data: file.data
			} : {
				isRemote: false,
				data: file.data
			},
			preview: file.preview
		};
	}
	#startIfAutoProceed() {
		if (this.opts.autoProceed && !this.scheduledAutoProceed) this.scheduledAutoProceed = setTimeout(() => {
			this.scheduledAutoProceed = null;
			this.upload().catch((err) => {
				if (!err.isRestriction) this.log(err.stack || err.message || err);
			});
		}, 4);
	}
	#checkAndUpdateFileState(filesToAdd) {
		let { files: existingFiles } = this.getState();
		let nextFilesState = { ...existingFiles };
		const validFilesToAdd = [];
		const errors = [];
		for (const fileToAdd of filesToAdd) try {
			let newFile = this.#transformFile(fileToAdd);
			this.#assertNewUploadAllowed(newFile);
			const existingFile = existingFiles[newFile.id];
			const isGhost = existingFile?.isGhost;
			if (isGhost && !newFile.isRemote) {
				if (newFile.data == null) throw new Error("File data is missing");
				newFile = {
					...existingFile,
					isGhost: false,
					data: newFile.data
				};
				this.log(`Replaced the blob in the restored ghost file: ${newFile.name}, ${newFile.id}`);
			}
			const onBeforeFileAddedResult = this.opts.onBeforeFileAdded(newFile, nextFilesState);
			existingFiles = this.getState().files;
			nextFilesState = {
				...existingFiles,
				...nextFilesState
			};
			if (!onBeforeFileAddedResult && this.checkIfFileAlreadyExists(newFile.id)) throw new RestrictionError(this.i18n("noDuplicates", { fileName: newFile.name ?? this.i18n("unnamed") }), { file: newFile });
			if (onBeforeFileAddedResult === false && !isGhost) throw new RestrictionError("Cannot add the file because onBeforeFileAdded returned false.", {
				isUserFacing: false,
				file: newFile
			});
			else if (typeof onBeforeFileAddedResult === "object" && onBeforeFileAddedResult !== null) newFile = onBeforeFileAddedResult;
			this.#restricter.validateSingleFile(newFile);
			nextFilesState[newFile.id] = newFile;
			validFilesToAdd.push(newFile);
		} catch (err) {
			errors.push(err);
		}
		try {
			this.#restricter.validateAggregateRestrictions(Object.values(existingFiles), validFilesToAdd);
		} catch (err) {
			errors.push(err);
			return {
				nextFilesState: existingFiles,
				validFilesToAdd: [],
				errors
			};
		}
		return {
			nextFilesState,
			validFilesToAdd,
			errors
		};
	}
	/**
	* Add a new file to `state.files`. Runs `onBeforeFileAdded`, guesses the
	* file type, checks the file against restrictions, and starts an upload if
	* `autoProceed === true`.
	*
	* Emits `file-added` and `files-added` with a single-element array. Throws
	* on the first error, including restriction errors. Use `addFiles` for
	* multi-file adds — see its documentation for batch semantics.
	*/
	addFile(file) {
		const { nextFilesState, validFilesToAdd, errors } = this.#checkAndUpdateFileState([file]);
		const restrictionErrors = errors.filter((error) => error.isRestriction);
		this.#informAndEmit(restrictionErrors);
		if (errors.length > 0) throw errors[0];
		this.setState({ files: nextFilesState });
		const [firstValidFileToAdd] = validFilesToAdd;
		this.emit("file-added", firstValidFileToAdd);
		this.emit("files-added", validFilesToAdd);
		this.log(`Added file: ${firstValidFileToAdd.name}, ${firstValidFileToAdd.id}, mime type: ${firstValidFileToAdd.type}`);
		this.#startIfAutoProceed();
		return firstValidFileToAdd.id;
	}
	/**
	* Add multiple files to `state.files`.
	*
	* Emits `file-added` per accepted file and `files-added` once for the batch.
	* Restriction failures emit `restriction-failed` and show an info message
	* without throwing — valid files in the same batch are still added. Any
	* non-restriction error (for example, an exception from `onBeforeFileAdded`)
	* is aggregated into an `AggregateError` (with `.errors`) and thrown
	* *before* state is updated — when this happens, no files from the batch
	* are added and `files-added` is not emitted.
	*
	* Prefer this over calling `addFile` in a loop when adding multiple files:
	* batch listeners on `files-added` fire exactly once, and restriction
	* failures on individual files do not abort the batch. Non-restriction
	* errors still abort the batch before any files are added.
	*/
	addFiles(fileDescriptors) {
		const { nextFilesState, validFilesToAdd, errors } = this.#checkAndUpdateFileState(fileDescriptors);
		const restrictionErrors = errors.filter((error) => error.isRestriction);
		this.#informAndEmit(restrictionErrors);
		const nonRestrictionErrors = errors.filter((error) => !error.isRestriction);
		if (nonRestrictionErrors.length > 0) {
			let message = "Multiple errors occurred while adding files:\n";
			nonRestrictionErrors.forEach((subError) => {
				message += `\n * ${subError.message}`;
			});
			this.info({
				message: this.i18n("addBulkFilesFailed", { smart_count: nonRestrictionErrors.length }),
				details: message
			}, "error", this.opts.infoTimeout);
			if (typeof AggregateError === "function") throw new AggregateError(nonRestrictionErrors, message);
			else {
				const err = new Error(message);
				err.errors = nonRestrictionErrors;
				throw err;
			}
		}
		this.setState({ files: nextFilesState });
		validFilesToAdd.forEach((file) => {
			this.emit("file-added", file);
		});
		this.emit("files-added", validFilesToAdd);
		if (validFilesToAdd.length > 5) this.log(`Added batch of ${validFilesToAdd.length} files`);
		else Object.values(validFilesToAdd).forEach((file) => {
			this.log(`Added file: ${file.name}\n id: ${file.id}\n type: ${file.type}`);
		});
		if (validFilesToAdd.length > 0) this.#startIfAutoProceed();
	}
	removeFiles(fileIDs) {
		const { files, currentUploads } = this.getState();
		const updatedFiles = { ...files };
		const updatedUploads = { ...currentUploads };
		const removedFiles = Object.create(null);
		fileIDs.forEach((fileID) => {
			if (files[fileID]) {
				removedFiles[fileID] = files[fileID];
				delete updatedFiles[fileID];
			}
		});
		function fileIsNotRemoved(uploadFileID) {
			return removedFiles[uploadFileID] === void 0;
		}
		Object.keys(updatedUploads).forEach((uploadID) => {
			const newFileIDs = currentUploads[uploadID].fileIDs.filter(fileIsNotRemoved);
			if (newFileIDs.length === 0) {
				delete updatedUploads[uploadID];
				return;
			}
			const { capabilities } = this.getState();
			if (newFileIDs.length !== currentUploads[uploadID].fileIDs.length && !capabilities.individualCancellation) throw new Error("The installed uploader plugin does not allow removing files during an upload.");
			updatedUploads[uploadID] = {
				...currentUploads[uploadID],
				fileIDs: newFileIDs
			};
		});
		const stateUpdate = {
			currentUploads: updatedUploads,
			files: updatedFiles
		};
		if (Object.keys(updatedFiles).length === 0) {
			stateUpdate.allowNewUpload = true;
			stateUpdate.error = null;
			stateUpdate.recoveredState = null;
		}
		this.setState(stateUpdate);
		this.#updateTotalProgressThrottled();
		const removedFileIDs = Object.keys(removedFiles);
		removedFileIDs.forEach((fileID) => {
			this.emit("file-removed", removedFiles[fileID]);
		});
		if (removedFileIDs.length > 5) this.log(`Removed ${removedFileIDs.length} files`);
		else this.log(`Removed files: ${removedFileIDs.join(", ")}`);
	}
	removeFile(fileID) {
		this.removeFiles([fileID]);
	}
	pauseResume(fileID) {
		if (!this.getState().capabilities.resumableUploads || this.getFile(fileID).progress.uploadComplete) return;
		const file = this.getFile(fileID);
		const isPaused = !(file.isPaused || false);
		this.setFileState(fileID, { isPaused });
		this.emit("upload-pause", file, isPaused);
		return isPaused;
	}
	pauseAll() {
		const updatedFiles = { ...this.getState().files };
		Object.keys(updatedFiles).filter((file) => {
			return !updatedFiles[file].progress.uploadComplete && updatedFiles[file].progress.uploadStarted;
		}).forEach((file) => {
			const updatedFile = {
				...updatedFiles[file],
				isPaused: true
			};
			updatedFiles[file] = updatedFile;
		});
		this.setState({ files: updatedFiles });
		this.emit("pause-all");
	}
	resumeAll() {
		const updatedFiles = { ...this.getState().files };
		Object.keys(updatedFiles).filter((file) => {
			return !updatedFiles[file].progress.uploadComplete && updatedFiles[file].progress.uploadStarted;
		}).forEach((file) => {
			const updatedFile = {
				...updatedFiles[file],
				isPaused: false,
				error: null
			};
			updatedFiles[file] = updatedFile;
		});
		this.setState({ files: updatedFiles });
		this.emit("resume-all");
	}
	#getFilesToRetry() {
		const { files } = this.getState();
		return Object.keys(files).filter((fileId) => {
			const file = files[fileId];
			return file.error && (!file.missingRequiredMetaFields || file.missingRequiredMetaFields.length === 0);
		});
	}
	async #doRetryAll() {
		const filesToRetry = this.#getFilesToRetry();
		const updatedFiles = { ...this.getState().files };
		filesToRetry.forEach((fileID) => {
			updatedFiles[fileID] = {
				...updatedFiles[fileID],
				isPaused: false,
				error: null
			};
		});
		this.setState({
			files: updatedFiles,
			error: null
		});
		this.emit("retry-all", this.getFilesByIds(filesToRetry));
		if (filesToRetry.length === 0) return {
			successful: [],
			failed: []
		};
		const uploadID = this.#createUpload(filesToRetry, { forceAllowNewUpload: true });
		return this.#runUpload(uploadID);
	}
	async retryAll() {
		const result = await this.#doRetryAll();
		this.emit("complete", result);
		return result;
	}
	cancelAll() {
		this.emit("cancel-all");
		const { files } = this.getState();
		const fileIDs = Object.keys(files);
		if (fileIDs.length) this.removeFiles(fileIDs);
		this.setState(defaultUploadState);
	}
	/**
	* Retry a specific file that has errored.
	*/
	retryUpload(fileID) {
		this.setFileState(fileID, {
			error: null,
			isPaused: false
		});
		this.emit("upload-retry", this.getFile(fileID));
		const uploadID = this.#createUpload([fileID], { forceAllowNewUpload: true });
		return this.#runUpload(uploadID);
	}
	logout() {
		this.iteratePlugins((plugin) => {
			plugin.provider?.logout?.();
		});
	}
	#handleUploadProgress = (file, progress) => {
		const fileInState = file ? this.getFile(file.id) : void 0;
		if (file == null || !fileInState) {
			this.log(`Not setting progress for a file that has been removed: ${file?.id}`);
			return;
		}
		if (fileInState.progress.percentage === 100) {
			this.log(`Not setting progress for a file that has been already uploaded: ${file.id}`);
			return;
		}
		const newProgress = {
			bytesTotal: progress.bytesTotal,
			percentage: progress.bytesTotal != null && Number.isFinite(progress.bytesTotal) && progress.bytesTotal > 0 ? Math.round(progress.bytesUploaded / progress.bytesTotal * 100) : void 0
		};
		if (fileInState.progress.uploadStarted != null) this.setFileState(file.id, { progress: {
			...fileInState.progress,
			...newProgress,
			bytesUploaded: progress.bytesUploaded
		} });
		else this.setFileState(file.id, { progress: {
			...fileInState.progress,
			...newProgress
		} });
		this.#updateTotalProgressThrottled();
	};
	#updateTotalProgress() {
		const totalProgress = this.#calculateTotalProgress();
		let totalProgressPercent = null;
		if (totalProgress != null) {
			totalProgressPercent = Math.round(totalProgress * 100);
			if (totalProgressPercent > 100) totalProgressPercent = 100;
			else if (totalProgressPercent < 0) totalProgressPercent = 0;
		}
		this.emit("progress", totalProgressPercent ?? 0);
		this.setState({ totalProgress: totalProgressPercent ?? 0 });
	}
	#updateTotalProgressThrottled = (0, import_throttle.default)(() => this.#updateTotalProgress(), 500, {
		leading: true,
		trailing: true
	});
	[Symbol.for("uppy test: updateTotalProgress")]() {
		return this.#updateTotalProgress();
	}
	#calculateTotalProgress() {
		const filesInProgress = this.getFiles().filter((file) => {
			return file.progress.uploadStarted || file.progress.preprocess || file.progress.postprocess;
		});
		if (filesInProgress.length === 0) return 0;
		if (filesInProgress.every((file) => file.progress.uploadComplete)) return 1;
		const isSizedFile = (file) => file.progress.bytesTotal != null && file.progress.bytesTotal !== 0;
		const sizedFilesInProgress = filesInProgress.filter(isSizedFile);
		const unsizedFilesInProgress = filesInProgress.filter((file) => !isSizedFile(file));
		if (sizedFilesInProgress.every((file) => file.progress.uploadComplete) && unsizedFilesInProgress.length > 0 && !unsizedFilesInProgress.every((file) => file.progress.uploadComplete)) return null;
		const totalFilesSize = sizedFilesInProgress.reduce((acc, file) => acc + (file.progress.bytesTotal ?? 0), 0);
		const totalUploadedSize = sizedFilesInProgress.reduce((acc, file) => acc + (file.progress.bytesUploaded || 0), 0);
		return totalFilesSize === 0 ? 0 : totalUploadedSize / totalFilesSize;
	}
	/**
	* Registers listeners for all global actions, like:
	* `error`, `file-removed`, `upload-progress`
	*/
	#addListeners() {
		const errorHandler = (error, file, response) => {
			let errorMsg = error.message || "Unknown error";
			if (error.details) errorMsg += ` ${error.details}`;
			this.setState({ error: errorMsg });
			if (file != null && file.id in this.getState().files) this.setFileState(file.id, {
				error: errorMsg,
				response
			});
		};
		this.on("error", errorHandler);
		this.on("upload-error", (file, error, response) => {
			errorHandler(error, file, response);
			if (typeof error === "object" && error.message) {
				this.log(error.message, "error");
				const newError = new Error(this.i18n("failedToUpload", { file: file?.name ?? "" }));
				newError.isUserFacing = true;
				newError.details = error.message;
				if (error.details) newError.details += ` ${error.details}`;
				this.#informAndEmit([newError]);
			} else this.#informAndEmit([error]);
		});
		let uploadStalledWarningRecentlyEmitted = null;
		this.on("upload-stalled", (error, files) => {
			const { message } = error;
			const details = files.map((file) => file.meta.name).join(", ");
			if (!uploadStalledWarningRecentlyEmitted) {
				this.info({
					message,
					details
				}, "warning", this.opts.infoTimeout);
				uploadStalledWarningRecentlyEmitted = setTimeout(() => {
					uploadStalledWarningRecentlyEmitted = null;
				}, this.opts.infoTimeout);
			}
			this.log(`${message} ${details}`.trim(), "warning");
		});
		this.on("upload", () => {
			this.setState({ error: null });
		});
		const onUploadStarted = (files) => {
			const filesFiltered = files.filter((file) => {
				const exists = file != null && this.getFile(file.id);
				if (!exists) this.log(`Not setting progress for a file that has been removed: ${file?.id}`);
				return exists;
			});
			const filesState = Object.fromEntries(filesFiltered.map((file) => [file.id, { progress: {
				uploadStarted: Date.now(),
				uploadComplete: false,
				bytesUploaded: 0,
				bytesTotal: file.size
			} }]));
			this.patchFilesState(filesState);
		};
		this.on("upload-start", onUploadStarted);
		this.on("upload-progress", this.#handleUploadProgress);
		this.on("upload-success", (file, uploadResp) => {
			if (file == null || !this.getFile(file.id)) {
				this.log(`Not setting progress for a file that has been removed: ${file?.id}`);
				return;
			}
			const currentProgress = this.getFile(file.id).progress;
			const needsPostProcessing = this.#postProcessors.size > 0;
			this.setFileState(file.id, {
				progress: {
					...currentProgress,
					postprocess: needsPostProcessing ? { mode: "indeterminate" } : void 0,
					uploadComplete: true,
					...!needsPostProcessing && { complete: true },
					percentage: 100,
					bytesUploaded: currentProgress.bytesTotal
				},
				response: uploadResp,
				uploadURL: uploadResp.uploadURL,
				isPaused: false
			});
			if (file.size == null) this.setFileState(file.id, { size: uploadResp.bytesUploaded || currentProgress.bytesTotal });
			this.#updateTotalProgressThrottled();
		});
		this.on("preprocess-progress", (file, progress) => {
			if (file == null || !this.getFile(file.id)) {
				this.log(`Not setting progress for a file that has been removed: ${file?.id}`);
				return;
			}
			this.setFileState(file.id, { progress: {
				...this.getFile(file.id).progress,
				preprocess: progress
			} });
		});
		this.on("preprocess-complete", (file) => {
			if (file == null || !this.getFile(file.id)) {
				this.log(`Not setting progress for a file that has been removed: ${file?.id}`);
				return;
			}
			const files = { ...this.getState().files };
			files[file.id] = {
				...files[file.id],
				progress: { ...files[file.id].progress }
			};
			delete files[file.id].progress.preprocess;
			this.setState({ files });
		});
		this.on("postprocess-progress", (file, progress) => {
			if (file == null || !this.getFile(file.id)) {
				this.log(`Not setting progress for a file that has been removed: ${file?.id}`);
				return;
			}
			this.setFileState(file.id, { progress: {
				...this.getState().files[file.id].progress,
				postprocess: progress
			} });
		});
		this.on("postprocess-complete", (fileIn) => {
			const file = fileIn && this.getFile(fileIn.id);
			if (file == null) {
				this.log(`Not setting progress for a file that has been removed: ${fileIn?.id}`);
				return;
			}
			const { postprocess: _deleted, ...newProgress } = file.progress;
			this.patchFilesState({ [file.id]: { progress: {
				...newProgress,
				complete: true
			} } });
		});
		this.on("restored", () => {
			this.#updateTotalProgressThrottled();
		});
		this.on("dashboard:file-edit-complete", (file) => {
			if (file) this.#checkRequiredMetaFieldsOnFile(file);
		});
		if (typeof window !== "undefined" && window.addEventListener) {
			window.addEventListener("online", this.#updateOnlineStatus);
			window.addEventListener("offline", this.#updateOnlineStatus);
			setTimeout(this.#updateOnlineStatus, 3e3);
		}
	}
	updateOnlineStatus() {
		if (!(window.navigator.onLine ?? true)) {
			this.emit("is-offline");
			this.info(this.i18n("noInternetConnection"), "error", 0);
			this.wasOffline = true;
		} else {
			this.emit("is-online");
			if (this.wasOffline) {
				this.emit("back-online");
				this.info(this.i18n("connectedToInternet"), "success", 3e3);
				this.wasOffline = false;
			}
		}
	}
	#updateOnlineStatus = this.updateOnlineStatus.bind(this);
	getID() {
		return this.opts.id;
	}
	/**
	* Registers a plugin with Core.
	*/
	use(Plugin, ...args) {
		if (typeof Plugin !== "function") throw new TypeError(`Expected a plugin class, but got ${Plugin === null ? "null" : typeof Plugin}. Please verify that the plugin was imported and spelled correctly.`);
		const plugin = new Plugin(this, ...args);
		const pluginId = plugin.id;
		if (!pluginId) throw new Error("Your plugin must have an id");
		if (!plugin.type) throw new Error("Your plugin must have a type");
		const existsPluginAlready = this.getPlugin(pluginId);
		if (existsPluginAlready) {
			const msg = `Already found a plugin named '${existsPluginAlready.id}'. Tried to use: '${pluginId}'.\nUppy plugins must have unique \`id\` options.`;
			throw new Error(msg);
		}
		if (Plugin.VERSION) this.log(`Using ${pluginId} v${Plugin.VERSION}`);
		if (plugin.type in this.#plugins) this.#plugins[plugin.type].push(plugin);
		else this.#plugins[plugin.type] = [plugin];
		plugin.install();
		this.emit("plugin-added", plugin);
		return this;
	}
	getPlugin(id) {
		for (const plugins of Object.values(this.#plugins)) {
			const foundPlugin = plugins.find((plugin) => plugin.id === id);
			if (foundPlugin != null) return foundPlugin;
		}
	}
	[Symbol.for("uppy test: getPlugins")](type) {
		return this.#plugins[type];
	}
	/**
	* Iterate through all `use`d plugins.
	*
	*/
	iteratePlugins(method) {
		Object.values(this.#plugins).flat(1).forEach(method);
	}
	/**
	* Uninstall and remove a plugin.
	*
	* @param {object} instance The plugin instance to remove.
	*/
	removePlugin(instance) {
		this.log(`Removing plugin ${instance.id}`);
		this.emit("plugin-remove", instance);
		if (instance.uninstall) instance.uninstall();
		const list = this.#plugins[instance.type];
		const index = list.findIndex((item) => item.id === instance.id);
		if (index !== -1) list.splice(index, 1);
		const updatedState = { plugins: {
			...this.getState().plugins,
			[instance.id]: void 0
		} };
		this.setState(updatedState);
	}
	/**
	* Uninstall all plugins and close down this Uppy instance.
	*/
	destroy() {
		this.log(`Closing Uppy instance ${this.opts.id}: removing all files and uninstalling plugins`);
		this.cancelAll();
		this.#storeUnsubscribe();
		this.iteratePlugins((plugin) => {
			this.removePlugin(plugin);
		});
		if (typeof window !== "undefined" && window.removeEventListener) {
			window.removeEventListener("online", this.#updateOnlineStatus);
			window.removeEventListener("offline", this.#updateOnlineStatus);
		}
	}
	hideInfo() {
		const { info } = this.getState();
		this.setState({ info: info.slice(1) });
		this.emit("info-hidden");
	}
	/**
	* Set info message in `state.info`, so that UI plugins like `Informer`
	* can display the message.
	*/
	info(message, type = "info", duration = 3e3) {
		const isComplexMessage = typeof message === "object";
		this.setState({ info: [...this.getState().info, {
			type,
			message: isComplexMessage ? message.message : message,
			details: isComplexMessage ? message.details : null
		}] });
		setTimeout(() => this.hideInfo(), duration);
		this.emit("info-visible");
	}
	/**
	* Passes messages to a function, provided in `opts.logger`.
	* If `opts.logger: Uppy.debugLogger` or `opts.debug: true`, logs to the browser console.
	*/
	log(message, type) {
		const { logger } = this.opts;
		switch (type) {
			case "error":
				logger.error(message);
				break;
			case "warning":
				logger.warn(message);
				break;
			default: logger.debug(message);
		}
	}
	#requestClientById = /* @__PURE__ */ new Map();
	registerRequestClient(id, client) {
		this.#requestClientById.set(id, client);
	}
	/** @protected */
	getRequestClientForFile(file) {
		if (!("remote" in file && file.remote)) throw new Error(`Tried to get RequestClient for a non-remote file ${file.id}`);
		const requestClient = this.#requestClientById.get(file.remote.requestClientId);
		if (requestClient == null) throw new Error(`requestClientId "${file.remote.requestClientId}" not registered for file "${file.id}"`);
		return requestClient;
	}
	/**
	* Restore an upload by its ID.
	*/
	async restore(uploadID) {
		this.log(`Core: Running restored upload "${uploadID}"`);
		const result = await this.#runUpload(uploadID);
		this.emit("complete", result);
		return result;
	}
	/**
	* Create an upload for a bunch of files.
	*
	*/
	#createUpload(fileIDs, opts = {}) {
		const { forceAllowNewUpload = false } = opts;
		const { allowNewUpload, currentUploads } = this.getState();
		if (!allowNewUpload && !forceAllowNewUpload) throw new Error("Cannot create a new upload: already uploading.");
		const uploadID = nanoid();
		this.emit("upload", uploadID, this.getFilesByIds(fileIDs));
		this.setState({
			allowNewUpload: this.opts.allowMultipleUploadBatches !== false && this.opts.allowMultipleUploads !== false,
			currentUploads: {
				...currentUploads,
				[uploadID]: {
					fileIDs,
					step: 0,
					result: {}
				}
			}
		});
		return uploadID;
	}
	[Symbol.for("uppy test: createUpload")](...args) {
		return this.#createUpload(...args);
	}
	#getUpload(uploadID) {
		const { currentUploads } = this.getState();
		return currentUploads[uploadID];
	}
	/**
	* Add data to an upload's result object.
	*/
	addResultData(uploadID, data) {
		if (!this.#getUpload(uploadID)) {
			this.log(`Not setting result for an upload that has been removed: ${uploadID}`);
			return;
		}
		const { currentUploads } = this.getState();
		const currentUpload = {
			...currentUploads[uploadID],
			result: {
				...currentUploads[uploadID].result,
				...data
			}
		};
		this.setState({ currentUploads: {
			...currentUploads,
			[uploadID]: currentUpload
		} });
	}
	/**
	* Remove an upload, eg. if it has been canceled or completed.
	*
	*/
	#removeUpload(uploadID) {
		const { [uploadID]: _deleted, ...currentUploads } = this.getState().currentUploads;
		this.setState({ currentUploads });
	}
	/**
	* Run an upload. This picks up where it left off in case the upload is being restored.
	*/
	async #runUpload(uploadID) {
		const getCurrentUpload = () => {
			const { currentUploads } = this.getState();
			return currentUploads[uploadID];
		};
		let currentUpload = getCurrentUpload();
		if (!currentUpload) throw new Error("Nonexistent upload");
		const steps = [
			...this.#preProcessors,
			...this.#uploaders,
			...this.#postProcessors
		];
		try {
			for (let step = currentUpload.step || 0; step < steps.length; step++) {
				const fn = steps[step];
				this.setState({ currentUploads: {
					...this.getState().currentUploads,
					[uploadID]: {
						...currentUpload,
						step
					}
				} });
				const { fileIDs } = currentUpload;
				await fn(fileIDs, uploadID);
				currentUpload = getCurrentUpload();
				if (!currentUpload) break;
			}
		} catch (err) {
			this.#removeUpload(uploadID);
			throw err;
		}
		if (currentUpload) {
			currentUpload.fileIDs.forEach((fileID) => {
				const file = this.getFile(fileID);
				if (file?.progress.postprocess) this.emit("postprocess-complete", file);
			});
			const files = currentUpload.fileIDs.map((fileID) => this.getFile(fileID));
			const successful = files.filter((file) => !file.error);
			const failed = files.filter((file) => file.error);
			this.addResultData(uploadID, {
				successful,
				failed,
				uploadID
			});
			currentUpload = getCurrentUpload();
		}
		let result;
		if (currentUpload) {
			result = currentUpload.result;
			this.#removeUpload(uploadID);
		}
		if (result == null) {
			this.log(`Not setting result for an upload that has been removed: ${uploadID}`);
			result = {
				successful: [],
				failed: [],
				uploadID
			};
		}
		return result;
	}
	/**
	* Start an upload for all the files that are not currently being uploaded.
	*/
	async upload() {
		if (!this.#plugins.uploader?.length) this.log("No uploader type plugins are used", "warning");
		let { files } = this.getState();
		if (this.#getFilesToRetry().length > 0) {
			const retryResult = await this.#doRetryAll();
			if (!(this.getFiles().filter((file) => file.progress.uploadStarted == null).length > 0)) {
				this.emit("complete", retryResult);
				return retryResult;
			}
			({files} = this.getState());
		}
		const onBeforeUploadResult = this.opts.onBeforeUpload(files);
		if (onBeforeUploadResult === false) throw new Error("Not starting the upload because onBeforeUpload returned false");
		if (onBeforeUploadResult && typeof onBeforeUploadResult === "object") {
			files = onBeforeUploadResult;
			this.setState({ files });
		}
		try {
			this.#restricter.validateMinNumberOfFiles(files);
			if (!this.#checkRequiredMetaFields(files)) throw new RestrictionError(this.i18n("missingRequiredMetaField"));
			const { currentUploads } = this.getState();
			const currentlyUploadingFiles = Object.values(currentUploads).flatMap((curr) => curr.fileIDs);
			const waitingFileIDs = Object.keys(files).filter((fileID) => {
				const file = this.getFile(fileID);
				return file && !file.progress.uploadStarted && !currentlyUploadingFiles.includes(fileID);
			});
			const uploadID = this.#createUpload(waitingFileIDs);
			const result = await this.#runUpload(uploadID);
			this.emit("complete", result);
			return result;
		} catch (err) {
			this.#informAndEmit([err]);
			throw err;
		}
	}
};
//#endregion
export { __toESM as S, l as _, UIPlugin as a, isDOMElement as b, debugLogger as c, C as d, F as f, k as g, W as h, require_debounce as i, EventManager as l, S as m, version as n, RestrictionError as o, M as p, nanoid as r, prettierBytes as s, Uppy as t, BasePlugin as u, getSafeFileId as v, __commonJSMin as x, getFileNameAndExtension as y };
