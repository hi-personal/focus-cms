<!-- === TOC START ===
// 🔹 Table of Contents (Generated)
//
// === ANCHOR ===
// 11#ANCHOR: Image Picker Modal
// 124#ANCHOR: Image Preview Modal
//
=== TOC END === -->


<!-- ANCHOR Image Picker Modal -->
<div id="imagePickerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40">
    <div class="w-full max-w-full flex bg-tranparent mx-auto p-1 md:p-4 h-full max-h-full">
        <div class="bg-white w-full rounded-lg shadow-xl px-0 py-0 mx-auto h-full max-h-full flex flex-col">
            <input type="hidden" id="imageOrder" name="imageOrder" value="">

            <div x-data="{ activeTab: 'images' }"
                class="mx-auto w-full h-full max-h-full flex flex-col flex-1 px-0 pt-0 pb-2 md:px-4 md:pb-4 overflow-hidden prose"
                style="height: calc(100vh-50px);"
            >
                <!-- Lapfülek és accordion -->
                <!-- Lapfülek -->
                <div class="border-b border-blue-200 flex-shrink-0">
                    <nav class="flex justify-center items-center">
                        <label class="inline-flex items-center cursor-pointer mr-4">
                            <input type="checkbox" id="cropToggle" class="sr-only peer">
                            <div class="relative z-60 w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <i class="fa-solid fa-crop-simple mx-2"></i><span class="max-md:hidden ms-3 text-sm font-medium text-gray-900">Képek kivágása</span>
                        </label>
                        <button
                            id="tabImages"
                            @click="activeTab = 'images'"
                            :class="activeTab === 'images' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-2 px-4 border-b-2 font-medium"
                        >
                            <span class='mdi mdi-checkbox-multiple-outline md-18 max-sm:mx-2 sm:mr-2'></span><span class="max-md:hidden">Képek kiválasztása</span>
                        </button>
                        <button
                            id="tabEditAlbum"
                            @click="activeTab = 'edit-album'"
                            :class="activeTab === 'edit-album' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-2 px-4 border-b-2 font-medium"
                        >
                            <span class='mdi mdi-pencil md-18 max-sm:mx-2 sm:mr-2'></span><span class="max-md:hidden">Album szerkesztése</span>
                        </button>
                        <button id="closeModal" class="ml-4 text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </nav>
                </div>

                <!-- Tartalmak -->
                <div class="flex flex-col flex-1 overflow-hidden">
                    <!-- Képek kiválasztása fül -->
                    <div
                        x-show="activeTab === 'images'"
                        x-cloak
                        x-bind:class="{ 'hidden': activeTab !== 'images' }"
                        class="flex flex-col flex-1 overflow-hidden">

                        <div class="text-center my-2 md:my-4">
                            <button id="deleteSelectedImages" class="bg-red-500 hover:bg-red-400 text-white px-3 py-1 mx-2 rounded">
                                <span class='mdi mdi-delete-circle-outline max-sm:mx-2 sm:mr-2'></span><span class="max-sm:hidden">Kiválasztott képek törlése</span>
                            </button>
                            <button id="editImageAlbum" class="bg-blue-500 hover:bg-blue-400 text-white px-3 py-1 mx-2  rounded">
                                <span class='mdi mdi-chevron-triple-right max-sm:mx-2 sm:mr-2'></span><span class="max-sm:hidden">Kiválasztott képek beillesztése albumba</span>
                            </button>
                        </div>

                        <div id="loader" class="text-center">
                            <p><span class="mdi mdi-loading mdi-spin mr-1"></span>Képek betöltése: <span id="loadedCount">0</span> / <span id="totalCount">0</span></p>
                            <div class="w-full bg-gray-200 rounded-full h-4 mt-2">
                                <div id="progressBar" class="bg-blue-500 hover:bg-blue-400 h-4 rounded-full" style="width: 0%;"></div>
                            </div>
                        </div>

                        <!-- Görgethető képek listája -->
                        <div class="p-0 border-y md:border border-gray-300 md:rounded-md overflow-y-auto flex-1">
                            <div id="imageList" class=""></div>
                        </div>
                    </div>


                    <!-- Album szerkesztése fül -->
                    <div
                        id="editAlbumTabContent"
                        x-show="activeTab === 'edit-album'"
                        x-cloak
                        x-bind:class="{ 'hidden': activeTab !== 'edit-album' }"
                        class="flex flex-col flex-1 overflow-hidden"
                    >
                        <div class="text-center">
                            <button id="moveSelectedImageLeft" class="bg-gray-300 text-white px-3 py-1 my-4 mx-2 rounded" disabled>
                                <span class='mdi mdi-chevron-double-left md-18 max-sm:mx-2 sm:mr-2'></span><span class="max-sm:hidden">Kijelöltek mozgatása balra</span>
                            </button>
                            <button id="moveSelectedImageRight" class="bg-gray-300 text-white px-3 py-1 my-4 mx-2 rounded" disabled>
                                <span class='mdi mdi-chevron-double-right md-18 max-sm:mx-2 sm:mr-2'></span><span class="max-sm:hidden">Kijelöltek mozgatása jobbra</span>
                            </button>
                            <button id="removeImagesFromAlbum" class="bg-orange-500 hover:bg-orange-400 text-white px-3 py-1 my-4 mx-2 rounded">
                                <span class='mdi mdi-close-box-multiple-outline md-18 max-sm:mx-2 sm:mr-2'></span><span class="max-sm:hidden">Kijelölt Képek eltávolítása</span>
                            </button>
                            <button id="insertAlbum" class="text-white px-3 py-1 my-4 mx-2 rounded"
                                data-action="insert"
                                data-insert-text="<i class='mdi mdi-plus-box-multiple-outline md-18 fa-images max-sm:mx-2 sm:mr-2'></i><span class='max-sm:hidden'>Album beillesztése</span>"
                                data-update-text="<span class='mdi mdi-repeat-variant md-18 max-sm:mx-2 sm:mr-2'></span><span class='max-sm:hidden'>Album frissítése</span>"
                            >
                            </button>
                        </div>
                        <div id="manageAlbumImages" class="p-1 border border-gray-300 rounded-md overflow-y-auto flex-1"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ANCHOR Image Preview Modal -->
<div id="imagePreviewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div x-data="{ activeTab: 'tab-content-sizes' }" class="w-full max-w-full flex bg-tranparent mx-auto p-1 md:p-4 h-full max-h-full">
        <div class="bg-white w-full rounded shadow-xl px-0 py-0 mx-auto h-full max-h-full flex flex-col">

            <!-- Fejléc -->
           <div class="bg-gray-100 rounded flex justify-between items-center px-2 h-12">
                <div class="inline-block align-middle px-2 space-x-1">
                    <span class="text-lg font-bold">Kép előnézet</span>
                    <span id="previewImageId" class="min-h-[1.5rem] min-w-[1ch] text-lg font-bold">123</span>
                </div>
                <button onclick="closeImagePreviewModal()" class="bg-red-500 text-white order-last px-3 py-1 rounded">Bezárás</button>
            </div>

            <!-- Lapfülek -->
            <div class="flex border-b justify-center">
                <button
                    id="tabImages"
                    @click="activeTab = 'tab-content-sizes'"
                    :class="activeTab === 'tab-content-sizes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-2 px-4 border-b-2 font-medium">
                    Méretek
                </button>
                <button
                    id="tabEditAlbum"
                    @click="activeTab = 'tab-content-details'"
                    :class="activeTab === 'tab-content-details' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-2 px-4 border-b-2 font-medium">
                    Kép adatai
                </button>
            </div>

            <!-- Tartalom -->
            <div class="flex-1 overflow-y-auto p-4 rounded mb-2">
                <!-- Visszajelzés -->
                <div id="previewFeedback" class="hidden mb-4 p-2 rounded"></div>

                <!-- Méretek fül -->
                <div x-show="activeTab === 'tab-content-sizes'" class="space-y-4 flex-1 overflow-y-auto prose max-w-none">
                    <div class="">

                        <div id="previewImageUrl" class=""></div>
                    </div>
                    <div id="sizeButtons" class="flex flex-wrap gap-2 justify-center">
                        <!-- A méretek gombjai itt jelennek meg dinamikusan -->
                    </div>
                    <!-- Kép előnézet -->
                    <div class="mt-4 flex justify-center">
                        <img id="previewImage" src="" alt="Preview" class="max-w-full">
                    </div>
                </div>

                <!-- Kép adatai fül -->
                <div x-show="activeTab === 'tab-content-details'" class=" space-y-4">
                    <form id="imageDetailsForm" class="max-w-4xl mx-auto">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-full">
                                    <label class="block font-semibold mb-1">URL (original)</label>
                                    <textarea id="original_url" name="original_url" class="w-full p-2 border rounded min-w-2" readonly></textarea>
                                </div>
                                <button
                                    type="button"
                                    id="copyBtn"
                                    class="h-10 px-3 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center justify-center"
                                    title="Másolás vágólapra"
                                >
                                    Másolás vágólapra
                                </button>
                            </div>

                            <div>
                                <label class="block font-semibold">Cím</label>
                                <input type="text" name="title" class="w-full p-2 border rounded">
                            </div>
                            <div>
                                <label class="block font-semibold">Név</label>
                                <input type="text" name="name" class="w-full p-2 border rounded">
                            </div>
                            <div>
                                <label class="block font-semibold">Mime típus</label>
                                <input type="text" name="mime_type" class="w-full p-2 border rounded" readonly>
                            </div>
                            <div>
                                <label class="block font-semibold">Fájlméret</label>
                                <input type="text" name="file_size" class="w-full p-2 border rounded" readonly>
                            </div>
                            <div>
                                <label class="block font-semibold">Alt szöveg</label>
                                <input type="text" name="alt_text" class="w-full p-2 border rounded">
                            </div>
                            <div>
                                <label class="block font-semibold">Leírás</label>
                                <textarea name="description" class="w-full p-2 border rounded"></textarea>
                            </div>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Mentés</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
