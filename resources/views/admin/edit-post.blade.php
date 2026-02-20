@php
    //Get upload config data to uppy.js
    $uploadSettings = config('media.allowed_uploaded_files');
@endphp
<x-app-layout :includeTinymce="true">
    <div class="container max-w-7xl mx-auto pb-20 px-2 md:px-4 lg:px-8 pt-1 bg-white min-h-screen">
        <h1 class="text-2xl font-bold mb-6">{{ __('Edit '.Str::ucfirst($post->post_type_name)) }}</h1>

        {{-- Breadcrumb --}}
        <x-admin.admin-breadcrumb :post="$post" />

        <div
            x-data="{
                show: {{ session('success') || session('error') ? 'true' : 'false' }},
                messageType: '{{ session('success') ? 'success' : (session('error') ? 'error' : '') }}',
                message: '{{ session('success') ?? session('error') }}',
                getClasses() {
                    return {
                        'success': 'bg-green-100 border-green-500 text-green-700',
                        'error': 'bg-red-100 border-red-500 text-red-700'
                    }[this.messageType]
                }
            }"
            x-init="if(show) setTimeout(() => show = false, 3000)"
            x-show="show"
            x-cloak
            x-transition
            class="fixed top-0 mt-[60px] w-auto h-auto py-4 px-8 z-50 left-1/2 -translate-x-1/2"
        >
            <div
                class="inline-block mx-auto
                    py-4 px-8
                    border-l-4 rounded-lg shadow-xl"
                :class="getClasses()"
                @click="show = false"
            >
                <p class="m-0" x-text="message"></p>
            </div>
        </div>

        <form
            id="post-edit-form"
            method="POST"
            action="{{ route('post.update', ['post_type'=>$post->post_type_name, 'post'=>$post->id]) }}"
        >
            @csrf
            @method('put')

            <input type="hidden" name="post_type_name" value="{{ $post->post_type_name }}">
            <input type="hidden" name="editor_status" value="{{ session('editorStatus') }}">

            <div class="mb-4">
                <label id="post-title-label" class="block text-gray-700 text-sm font-bold mb-2" for="title">
                    {{ __('Title') }}
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="title" name="title" type="text" value="{{ old('title', $post->title) }}" required>
            </div>
            <div
                x-data="{
                    isOpenOCEditMenu: false,
                    isOpen: false,
                    teszt: false,
                    showHeadImgPreviewModal: false,
                    openTagsContainer: false,
                    imgSrc: '',

                    resize(el) {
                        if (!el) return;
                        el.style.height = 'auto';
                        el.style.height = el.scrollHeight + 'px';
                    }
                }"
                x-init="
                    $watch('isOpen', value => {
                        if (value) {
                            $nextTick(() => resize($refs.metaDescription))
                        }
                    })
                "
                class="bg-white relative"
            >
                <!-- Include: Edit post nav -->
                @include('admin.posts.edit-post-nav')

                <div
                    x-show="isOpen"
                    x-transition:enter="transition-all duration-200 ease-in"
                    x-transition:enter-start="max-h-0 opacity-0"
                    x-transition:enter-end="max-h-[500px] opacity-100"
                    x-transition:leave="transition-all duration-200 ease-out"
                    x-transition:leave-start="max-h-[500px] opacity-100"
                    x-transition:leave-end="max-h-0 opacity-0"
                    x-ref="accordion"
                    x-cloak
                    class="absolute inset-x-0 z-30 max-w-7xl max-h-[calc(100%_-_100px)] bg-white py-12 px-8 my-3 rounded border border-slate-900 overflow-y-auto mx-auto"
                    style="box-shadow: 0 4px 16px rgba(0, 0, 0, 0.65);"
                >
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                        <div class="">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                                Status
                            </label>
                            <select class="w-full p-2 shadow border rounded text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="status" name="status" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ old('status', $post->status) === $status ? 'selected' : '' }}>
                                        {{ __(ucfirst($status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if (in_array($post->post_type_name, ['post', 'image_container']))
                            <div class="">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="created_at">
                                    {{ __('Creation Date') }}
                                </label>
                                <input class="w-full p-2 shadow appearance-none border rounded text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="created_at" name="created_at" type="datetime-local"
                                    value="{{ old('created_at', $post->created_at->format('Y-m-d\TH:i')) }}" required>
                            </div>

                            <div class="">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="user_id">
                                    {{ __('Author') }}
                                </label>
                                <select
                                    class="w-full p-2 shadow border rounded text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="user_id"
                                    name="user_id"
                                    required
                                >
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $user->id === (old('user_id') ?? $post->user_id ?? auth()->id()) ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="user_id" value="0">
                            <input type="hidden" name="" value="">
                        @endif
                    </div>

                    @if(in_array($post->post_type_name, ['post']))
                        <div class="block w-full mt-4">
                            <label class="block w-full text-gray-700 text-sm font-bold mb-2" for="name">
                                {{ __('Kategória') }}
                            </label>
                            <select
                                name="category_id"
                                class="w-full p-2 shadow border border-slate-600 rounded text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                placeholder="Kategória"
                            >
                                <option value="0">Nincs</option>
                                @foreach($allTCategories as $item)
                                    <option
                                        value="{{ $item->id }}"
                                        {{ $categoryId == $item->id ? "selected" : null }}
                                    >{{ (empty($item->depth) ? null : str_repeat("- ", $item->depth)." ").$item->title}}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="w-full my-4">
                        <label class="block w-full text-gray-700 text-sm font-bold mb-2" for="head_image">
                            {{ __('Kiemelt kép') }}
                        </label>
                        <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-1 lg:gap-2 items-center">
                            <div class="py-2">
                                <input
                                    type="text"
                                    id="postHeadImage"
                                    class="w-full rounded border border-gray-700"
                                    name="head_image"
                                    value="{{ $head_image }}"
                                >
                            </div>
                            <div class="pl-2 py-2 flex justify-start lg:justify-end flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="insert-image-album px-3 py-2 bg-green-500 text-white rounded"
                                    data-mode="insert-input"
                                    data-input-id="#postHeadImage"
                                >Kiválasztás</button>
                                <button
                                    type="button"
                                    @click="document.getElementById('postHeadImage').value = ''"
                                    class="px-3 py-2 bg-red-500 text-white rounded"
                                ><i class="mdi mdi-close"></i></button>
                                <button
                                    type="button"
                                    class="px-3 py-2 bg-blue-500 text-white rounded"
                                    id="openHeadImgPreview"
                                    data-url="{{ $head_image_url }}"
                                    @click="imgSrc = $el.dataset.url; showHeadImgPreviewModal = true"
                                    ><i class="mdi mdi-eye-outline"></i></button>

                                <!-- Post Head Image Preview Modal -->
                                <div
                                    x-show="showHeadImgPreviewModal"
                                    @keydown.escape.window="showHeadImgPreviewModal = false"
                                    class=""
                                    x-cloak
                                >
                                    <div
                                        x-transition
                                        class="fixed flex inset-0 p-2 bg-black bg-opacity-60 items-center justify-center z-50"
                                        @click="showHeadImgPreviewModal = false"
                                    >
                                        <div class="p-2 flex flex-col bg-white rounded shadow-lg w-full h-full">
                                            <div class="text-right justify-end  w-full">
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 bg-red-500 text-white rounded inline-block"
                                                    @click="showHeadImgPreviewModal = false"
                                                >
                                                    Bezárás
                                                </button>
                                            </div>
                                            <div class="pt-2 flex w-full h-full justify-center items-center">
                                                <img
                                                    :src="imgSrc"
                                                    alt="Előnézeti kép"
                                                    class="block relative w-auto h-auto mx-auto max-w-full max-h-[calc(100vh_-_6rem)] object-contain rounded"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END: Post Head Image Preview Modal -->
                            </div>
                        </div>
                    </div>

                    @if(in_array($post->post_type_name, ['post']))
                        <div class="block w-full mt-4">
                            <label class="block w-full text-gray-700 text-sm font-bold mb-2" for="name">
                                {{ __('Tags') }}
                            </label>
                            <div
                                x-data="multiSelect({
                                    items: @js($terms->map(fn($t) => ['id' => $t->id, 'title' => $t->title])),
                                    selectedIds: [
                                        @foreach($selectedTagIds as $tag)
                                            {{ $tag }}@if(!$loop->last),@endif
                                        @endforeach
                                    ]
                                })"
                                class="w-full relative"
                            >
                                <div class="grid grid-cols-[1fr_auto] gap-1 items-center">
                                    <!-- Kiválasztott elemek és rejtett checkboxok -->
                                    <div class="min-h-[42px] border border-slate-600 SHADOW rounded p-1 flex flex-wrap items-center">
                                        <template x-for="item in sortedSelected" :key="item.id">
                                            <div
                                                @click="removeById(item.id)"
                                                class="my-1 mx-2 px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-500 cursor-pointer relative"
                                            >
                                                <i class="mdi mdi-close"></i>
                                                <span x-text="item.title"></span>
                                                <!-- Rejtett checkbox -->
                                                <input type="checkbox" name="tags[]" :value="item.id" checked class="hidden">
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Gomb a lenyitáshoz -->
                                    <div class="flex items-center">
                                        <button
                                            type="button"
                                            @click="openTagsContainer = !openTagsContainer"
                                            class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600 flex items-center gap-1"
                                            :class="{
                                                'bg-orange-600 text-black hover:bg-orange-400': openTagsContainer,
                                                'bg-gray-700 text-white ': !openTagsContainer,
                                            }"
                                        >
                                            <i
                                                class="mdi mdi-arrow-down mdi-18 transition-transform duration-300"
                                                :class="{
                                                    'mdi-arrow-down rotate-180': openTagsContainer,
                                                    'mdi-arrow-down rotate-0': !openTagsContainer
                                                }"
                                            ></i>
                                            <span class="ml-1 lg:hidden">(+)</span>
                                            <span class="max-lg:hidden">Hozzáadás</span>
                                        </button>
                                    </div>
                                </div>
                                <!-- Elérhető elemek -->
                                <div
                                    x-show="openTagsContainer"
                                    x-transition
                                    class="w-full mt-2 bg-white border border-slate-600 rounded shadow py-8 px-3"
                                >
                                    <div class="flex flex-wrap">
                                        <template x-for="item in sortedAvailable" :key="item.id">
                                            <div
                                                @click="add(item)"
                                                class="my-1 mx-2 py-2 px-3 bg-blue-500 text-white hover:bg-blue-400 border rounded cursor-pointer"
                                                x-text="item.title"
                                            ></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="block w-full mt-4">
                        <label class="block w-full text-gray-700 text-sm font-bold mb-2" for="name">
                            {{ __('Megjelenített név (url-slug)') }}
                        </label>
                        <input
                            class="w-full p-2 shadow appearance-none border rounded text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $post->name) }}"
                        >
                    </div>

                    <div class="block w-full mt-4">
                        <label class="block w-full text-gray-700 text-sm font-bold mb-2" for="name">
                            {{ __('Meta | Title (opcionális)') }}
                        </label>
                        <input
                            class="w-full p-2 shadow appearance-none border rounded text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="meta_title"
                            name="meta_title"
                            type="text"
                            value="{{ old('meta_title', $meta_title) }}"
                        >
                    </div>

                    <div class="block w-full mt-4">
                        <label class="block w-full text-gray-700 text-sm font-bold mb-2" for="name">
                            {{ __('Meta | Description (opcionális)') }}
                        </label>
                        <textarea
                            id="meta_description"
                            name="meta_description"
                            x-ref="metaDescription"
                            @input="resize($el)"
                            class="w-full p-2 shadow appearance-none border rounded
                                text-gray-700 leading-tight
                                focus:outline-none focus:shadow-outline
                                resize-none overflow-hidden"
                        >{{ old('meta_description', $meta_description) }}</textarea>
                    </div>
                </div>
                <div id="myeditor-container" class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold my-2" for="content">
                        {{ __('Content') }}
                    </label>
                    <div class="border border-slate-600 rounded py-2 px-1 md:pl-3 md:pr-1 block relative h-auto">
                        <textarea
                            x-init="
                                window.addEventListener('keydown', (e) => {
                                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'e') {
                                        e.preventDefault();
                                        $('#myeditor').trigger('click');
                                    }
                                    if (
                                        e.key === 'Escape'
                                        && isOpen == false
                                        && document.querySelector('#previewModal').classList.contains('hidden') == true
                                    ) {
                                        $('#exit-editor').trigger('click');
                                    }
                                });
                            "
                            id="myeditor"
                            class="bg-white min-h-[300px] lg:min-h-[500px] pr-2 box-border appearance-none border-none p-0 w-full text-gray-700 resize-none overflow-x-hidden overflow-x border-0 outline-none ring-0 ring-offset-0 shadow-none focus:border-0 focus:outline-none focus:ring-0 focus:ring-offset-0 focus:shadow-none active:border-0 hover:border-0"
                            name="content"
                            style="line-height: 1.25; "
                            data-lh="1.25"
                        >{{ $post->content }}</textarea>
                    </div>
                </div>
            </div>
        </form>

        <!-- Rejtett input mező, amely átadja a szükséges beállításokat az uppy.js-nek -->
        <input
            type="hidden"
            data-maxFileSize="{{ $uploadSettings['max_size'] }}"
            data-allowedFileTypes="{{ implode(',', $uploadSettings['mimes']) }}"
        >

        <!-- Image Picker modal -->
        <x-admin.posts.image-picker-modal />

        <div id="selectedmediapreview" class="hidden"></div>
        <div id="selectedmediapreview-2" class="hidden"></div>
        <!-- END: Image Picker modal -->

        <!-- File Picker modal -->
        <x-admin.posts.file-picker-modal />

        <div id="selectedfilepreview" class="hidden"></div>
        <div id="selectedfilepreview-2" class="hidden"></div>
        <!-- END: Image Picker modal -->

        <!-- Preview (content) Modal ablak -->
        <x-admin.posts.preview-content-modal />
        <!-- END: Preview (content) Modal ablak -->

        <!-- Upload Modal -->
        <x-admin.posts.upload-modal />
        <!-- END: Upload Modal -->


        <!-- Confirm Modal -->
        <div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50" data-caller="">
            <div class="w-full h-full items-center flex bg-tranparent mx-auto p-1">
                <div class="bg-white rounded-lg shadow-xl p-3 mx-auto flex flex-col overflow-y-auto">
                    <div id="confirmModalTitle" class="font-bold text-lg text-left"></div>
                    <div id="confirmModalContent" class="py-5 text-left"></div>
                    <div class="flex justify-end">
                        <button type="button" class="confirm-button py-3 px-4 bg-red-500 hover:bg-red-600 text-white border rounded-lg">
                            <i class="mdi mdi-delete-outline md-18"></i> Igen
                        </button>
                        <button type="button" class="close-button ml-2 py-3 px-4 bg-gray-500 hover:bg-gray-600 text-white border rounded-lg">
                            <i class="mdi mdi-close md-18"></i> Mégse
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Confirm Modal -->
    </div>

    <div class="hidden" id="post_id" data-id="{{ $post->id }}"></div>
    <div class="hidden" id="post_type_name" data-post-type-name="{{ $post->post_type_name }}"></div>

    @push('scripts')
        <script>
            function multiSelect({ items, selectedIds }) {
                return {
                    open: false,
                    allItems: items,
                    selected: items.filter(i => selectedIds.includes(i.id)),

                    get sortedSelected() {
                        return [...this.selected].sort((a, b) => a.title.localeCompare(b.title));
                    },

                    get sortedAvailable() {
                        return this.allItems
                            .filter(i => !this.selected.find(s => s.id === i.id))
                            .sort((a, b) => a.title.localeCompare(b.title));
                    },

                    add(item) {
                        if (!this.selected.find(i => i.id === item.id)) {
                            this.selected.push(item);
                        }
                    },

                    removeById(id) {
                        this.selected = this.selected.filter(i => i.id !== id);
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>