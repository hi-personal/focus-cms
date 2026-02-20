<!-- === TOC START ===
// 🔹 Table of Contents (Generated)
//
// === ANCHOR ===
// 11#ANCHOR: file Picker Modal
// 44#ANCHOR: file Preview Modal
//
=== TOC END === -->


<!-- ANCHOR file Picker Modal -->
<div id="filePickerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40">
    <div class="w-full max-w-full flex bg-tranparent mx-auto p-1 md:p-4 h-full max-h-full">
        <div class="bg-white w-full md:max-w-[1200px] rounded-lg shadow-xl px-0 py-0 mx-auto h-full max-h-full flex flex-col">
            <input type="hidden" id="fileOrder" name="fileOrder" value="">
            <div class="flex flex-col flex-1 overflow-hidden">
                <div class="border-b border-blue-200 justify-between flex p-2">
                    <span class="flex-col items-center cursor-pointer">
                        <span class="max-md:hidden ms-3 text-sm font-medium text-gray-900">Fájlok kiválasztása</span>
                    </span>
                    <button id="closeFilePickerModal" class="flex-col ml-4 text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex flex-col flex-1 overflow-hidden">
                    <div class="text-center my-2 md:my-4">
                        <button id="deleteSelectedfiles" class="bg-red-500 disabled:bg-gray-400 text-white px-3 py-1 mx-2 rounded" disabled>
                            <span class='mdi mdi-delete-circle-outline max-sm:mx-2 sm:mr-2'></span><span class="max-sm:hidden">Kiválasztott fájlok törlése</span>
                        </button>
                    </div>
                    <div class="p-2 border-y md:border border-gray-300 md:rounded-md overflow-y-auto flex-1">
                        <div id="fileList" class=""></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ANCHOR file Preview Modal -->
<div id="filePreviewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div x-data="{ activeTab: 'tab-file-data' }" class="w-full max-w-full flex bg-tranparent mx-auto p-1 md:p-4 h-full max-h-full">
        <div class="bg-white w-full md:max-w-[1200px] rounded shadow-xl px-0 py-0 mx-auto h-full max-h-full flex flex-col">

            <!-- Fejléc -->
            <div class="bg-gray-100 rounded  flex justify-between items-center p-2">
                <span class="order-first text-lg font-bold px-3 py-1">Fájl előnézet</span>

                <button onclick="closeFilePreviewModal()" class="bg-red-500 text-white order-last px-3 py-1 rounded">Bezárás</button>
            </div>

            <!-- Lapfülek -->
            <div class="flex border-b justify-center">
                <button
                    id="tabfiles"
                    @click="activeTab = 'tab-file-data'"
                    :class="activeTab === 'tab-file-data' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-2 px-4 border-b-2 font-medium"
                >
                    Adatok
                </button>
                <button
                    id="tabFileDetails"
                    @click="activeTab = 'tab-file-content-details'"
                    :class="activeTab === 'tab-file-content-details' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-2 px-4 border-b-2 font-medium"
                >
                    Fájl adatai
                </button>
            </div>

            <!-- Tartalom -->
            <div class="flex-1 overflow-y-auto p-4 rounded mb-2">
                <!-- Visszajelzés -->
                <div id="previewFileFeedback" class="hidden mb-4 p-2 rounded"></div>

                <!-- Méretek fül -->
                <div x-show="activeTab === 'tab-file-data'" class="space-y-4 flex-1 overflow-y-auto prose max-w-none">
                    <div id="sizeButtons" class="flex flex-wrap gap-2 justify-center">
                        <!-- A méretek gombjai itt jelennek meg dinamikusan -->
                    </div>
                    <div class="max-w-4xl mx-auto space-y-4">
                        <div>
                            <button
                                type="button"
                                id="copyFileUrlToClipboard"
                                class="p-2 bg-blue-300 hover:bg-blue-500 hover:text-white m-1 rounded"
                            >Url másololása vágólapra</button>
                            <button
                                type="button"
                                id="insertFileUrlToEditor"
                                class="p-2 bg-blue-300 hover:bg-blue-500 hover:text-white m-1 rounded"
                            >Url beillesztése</button>
                            <div class="p-1 border border-gray-800 inline-block">
                                <!--label class="inline-block font-semibold px-1">
                                    Cím <input type="checkbox" id="inserFileSCTitle" name="inserFileSCTitle" class="ml-1" name="" value="" />
                                </label-->
                                <label class="inline-block font-semibold px-1">
                                    Letöltés <input type="checkbox" id="inserFileSCDL" name="inserFileSCDL" class="ml-1" name="" value="" />
                                </label>
                                <label class="inline-block font-semibold px-1">
                                    Megnyitás <input type="checkbox" id="inserFileSCOpen" name="inserFileSCOpen" class="ml-1" name="" value="" />
                                </label>
                                <button
                                    type="button"
                                    id="insertFilSHToEditor"
                                    class="mx-2 p-2 bg-blue-300 hover:bg-blue-500 hover:text-white m-1 rounded"
                                >Beillesztés rövidkódként</button>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold">URL</label>
                            <input
                                id="previewFileUrl"
                                class="w-full p-2 border rounded"
                                type="text"
                                value=""
                                readonly
                            >
                        </div>
                        <div>
                            <label class="block font-semibold">Cím</label>
                            <input
                                id="previewFileTitle"
                                class="w-full p-2 border rounded"
                                type="text"
                                value=""
                                readonly
                            >
                        </div>
                        <div>
                            <label class="block font-semibold">Név</label>
                            <input
                                id="previewFileName"
                                class="w-full p-2 border rounded"
                                type="text"
                                value=""
                                readonly
                            >
                        </div>
                        <div>
                            <label class="block font-semibold">Mime típus</label>
                            <input
                                id="previewFileMimeType"
                                class="w-full p-2 border rounded"
                                type="text"
                                value=""
                                readonly
                            >
                        </div>
                        <div>
                            <label class="block font-semibold">Kiterjesztés</label>
                            <input
                                id="previewFileFileExtension"
                                class="w-full p-2 border rounded"
                                type="text"
                                value=""
                                readonly
                            >
                        </div>
                        <div>
                            <label class="block font-semibold">Fájlnév</label>
                            <input
                                id="previewFileFileName"
                                class="w-full p-2 border rounded"
                                type="text"
                                value=""
                                readonly
                            >
                        </div>
                        <div>
                            <label class="block font-semibold">Fájlméret</label>
                            <input
                                id="previewFileSize"
                                class="w-full p-2 border rounded"
                                type="text"
                                value=""
                                readonly
                            >
                        </div>
                        <div>
                            <label class="block font-semibold">Alt szöveg</label>
                            <div
                                id="previewFileAltText"
                                class="w-full p-2 border border-black rounded overflow-hidden h-auto"
                                readonly
                            ></div>
                        </div>
                        <div>
                            <label class="block font-semibold">Leírás</label>
                            <div
                                id="previewFileDescription"
                                class="w-full p-2 border border-black rounded overflow-hidden h-auto"
                                readonly
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Kép adatai fül -->
                <div x-show="activeTab === 'tab-file-content-details'" class=" space-y-4">
                    <form id="fileDetailsForm" class="max-w-4xl mx-auto">
                        <div class="space-y-4">
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
                                <textarea name="description" class="w-full p-2 border rounded h-auto min-h-[220px] overflow-y-auto resize-y"></textarea>
                            </div>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Mentés</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>