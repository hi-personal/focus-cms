<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Többfájl feltöltés') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div x-data="fileUploader">
            <input type="file" multiple @change="handleFiles($event)" class="mb-4">

            <button @click="uploadFiles()" :disabled="files.length === 0" class="bg-blue-600 text-white px-4 py-2 rounded">
                Feltöltés indítása (max 4 egyszerre)
            </button>

            <template x-for="upload in uploads" :key="upload.id">
                <div class="mt-4 p-4 border rounded">
                    <div class="flex justify-between items-center mb-2">
                        <span x-text="upload.name"></span>
                        <span x-text="upload.statusText"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full transition-all"
                            :class="{
                                'bg-blue-600': upload.status === 'uploading',
                                'bg-green-600': upload.status === 'done',
                                'bg-red-600': upload.status === 'error'
                            }"
                            :style="`width: ${upload.progress}%`">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('fileUploader', () => ({
                    files: [],
                    uploads: [],
                    maxParallel: 4,

                    handleFiles(event) {
                        this.files = Array.from(event.target.files);
                    },

                    async uploadFiles() {
                        const chunks = this.chunkArray(this.files, this.maxParallel);
                        for (const chunk of chunks) {
                            await Promise.all(chunk.map(file => this.uploadFile(file)));
                        }
                    },

                    async uploadFile(file) {
                        const upload = Alpine.raw({
                            id: Date.now() + Math.random(),
                            name: file.name,
                            progress: 0,
                            status: 'uploading',
                            statusText: 'Feltöltés...'
                        });

                        this.uploads.push(upload);

                        const formData = new FormData();
                        formData.append('file', file);

                        const xhr = new XMLHttpRequest();

                        // Progress handler
                        xhr.upload.addEventListener('progress', (e) => {
                            if (e.lengthComputable) {
                                upload.progress = Math.round((e.loaded / e.total) * 100);

                                // Force Alpine reactivity
                                this.uploads = [...this.uploads];
                            }
                        });

                        // Success handler
                        xhr.onload = () => {
                            if (xhr.status === 200) {
                                upload.status = 'done';
                                upload.statusText = 'Kész!';
                            } else {
                                upload.status = 'error';
                                upload.statusText = `Hiba (${xhr.status})`;
                            }
                            this.uploads = [...this.uploads];
                        };

                        // Error handler
                        xhr.onerror = () => {
                            upload.status = 'error';
                            upload.statusText = 'Hálózati hiba';
                            this.uploads = [...this.uploads];
                        };

                        xhr.open('POST', '{{ route('file-upload-handler') }}');
                        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                        xhr.send(formData);
                    },

                    chunkArray(arr, size) {
                        return arr.reduce((acc, _, i) => {
                            if (i % size === 0) acc.push(arr.slice(i, i + size));
                            return acc;
                        }, []);
                    }
                }));
            });
            </script>

        </div>
    </div>
</x-app-layout>