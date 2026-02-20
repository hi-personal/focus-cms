@php
    $uploadSettings = config('media.allowed_uploaded_images');
@endphp

<x-app-layout :includeTinymce="true">
    <div class="p-4 w-full max-w-[1200px] items-center">
        <div class="p-2">
            <h3>
                <a
                    href="{{ route('taxonomies.index', ['taxonomy_name' => $taxonomy_name]) }}"
                    class="text-blue-700 hover:text-blue-500"
                    target="_self"
                >{{ __(Str::ucfirst($taxonomy_name)) }}</a> / {{ $term->title }}
            </h3>

            @if(session('success'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 6000)"
                    x-show="show"
                    x-cloak
                    x-transition
                    class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4"
                    role="alert"
                >
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 10000)"
                    x-show="show"
                    x-cloak
                    x-transition
                    class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4"
                    role="alert"
                >
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <form method="post" action="{{ route('taxonomy.update', ['taxonomy_name'=>$taxonomy_name, 'term' => $term->id]) }}">
                @csrf
                @method('put')
                <div class="p-2 w-full rounded">
                    @if($hierarchial)
                        <label class="w-full">Szülő elem</label>
                        <select
                            name="parent_id"
                            class="mt-2 mb-3 w-full border border-gray-300 ú rounded"
                            placeholder="Szülő elem"
                        >
                            <option value="0">Nincs</option>
                            @foreach($allTerms as $item)
                                @continue($term->id == $item->id)
                                <option
                                    value="{{ $item->id }}"
                                    {{ $term->parent_id == $item->id ? "selected" : null }}
                                >{{ (empty($item->depth) ? null : str_repeat("- ", $item->depth)." ").$item->title}}</option>
                            @endforeach
                        </select>
                    @endif
                    <label class="w-full">Cím</label>
                    <input
                        type="text"
                        name="title"
                        value="{{ old('title', $term->title) }}"
                        class="mt-2 mb-3 w-full border border-gray-300 rounded"
                        placeholder="{{ __('Title') }}"
                    >
                    <label class="w-full">Keresőbarát név</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $term->name) }}"
                        class="mt-2 mb-3 w-full border border-gray-300 rounded"
                        placeholder="Keresőbarát név"
                    >
                    <label class="w-full">Leírás</label>
                    <div x-data="{ focused: false }">
                        <textarea
                            name="description"
                            x-on:focus="focused = true"
                            x-on:blur="focused = false"
                            :class="focused ? 'min-h-[300px]' : 'min-h-[30px]'"
                            class="my-2 w-full border border-gray-300 rounded transition-all duration-200 ease-in-out"
                            placeholder="Leírás"
                        >{{ old('description', $description) }}</textarea>
                    </div>
                    @if($hierarchial)
                        <div class="w-full my-4 p-4 border rounded" x-data>
                            @if(!empty($head_image))
                                <div class="w-full flex justify-center">
                                    <img
                                        class="my-4 max-h-[600px] rounded"
                                        src="{{ $head_image_url }}"
                                    >
                                </div>
                            @endif
                            <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-1 lg:gap-2 items-center">
                                <div class="p-2">
                                    <input
                                        type="text"
                                        id="profileImage"
                                        class="w-full rounded border border-gray-300"
                                        name="head_image"
                                        value="{{ $head_image }}"
                                    >
                                </div>
                                <div class="p-2 flex justify-start lg:justify-end flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="px-3 py-1 bg-blue-500 text-white rounded"
                                        onclick="openUploadModal()"
                                    >Feltöltés</button>
                                    <button
                                        type="button"
                                        class="insert-image-album px-3 py-1 bg-green-500 text-white rounded"
                                        data-mode="insert-input"
                                        data-input-id="#profileImage"
                                    >Kiválasztás</button>
                                    <button
                                        type="button"
                                        @click="document.getElementById('profileImage').value = ''"
                                        class="px-3 py-1 bg-red-500 text-white rounded"
                                    >Törlés</button>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="mt-8 mb-4 flex justify-end flex-wrap">
                        <button
                            type="button"
                            class="termDeleteButton my-2 py-2 mr-2 px-3 bg-red-500 hover:bg-red-400 hover:text-white border rounded"
                            data-href="{{ route('taxonomy.delete', ['taxonomy_name'=>$taxonomy_name, 'term' => $term->id]) }}"
                        >Törlés</button>
                        <button type="submit" class="my-2 py-2 px-3 bg-blue-500 hover:bg-blue-400 hover:text-white border rounded">Mentés</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Image And File Picker components -->
        <input
            type="hidden"
            data-maxFileSize="{{ $uploadSettings['max_size'] }}"
            data-allowedFileTypes="{{ implode(',', $uploadSettings['mimetypes']) }}"
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

        <div class="hidden" id="post_id" data-id="{{ $post->id }}"></div>
        <div class="hidden" id="post_type_name" data-post-type-name="{{ $post->post_type_name }}"></div>
    <!-- END:Image And File Picker components -->

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $(".termDeleteButton").on("click", function() {
                    $("#confirmModal").removeClass("hidden").data("caller", "deleteTerm");
                    $("#confirmModalTitle").html('<p>Művelet megerősítése</p>');
                    $("#confirmModalContent").html('<p>Biztosan törlöd a kategóriát?</p>');
                })

                $("#confirmModal .close-button").on("click", function() {
                    $("#confirmModal").addClass("hidden");
                });

                $("#confirmModal .confirm-button").on("click", function() {
                    if ($("#confirmModal").data("caller") == "deleteTerm") {
                        window.location = $(".termDeleteButton").first().data("href");
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>