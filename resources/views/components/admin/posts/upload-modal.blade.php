<div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="w-full lg:max-w-[1280px] p-1 my-0 mx-auto h-full max-h-full">
        <div
            id="uploadModalContent"
            class="bg-white rounded-lg p-4 shadow-xl h-full flex flex-col overflow-hidden"
        >
            <!-- Modal fejléc -->
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold mt-0 mb-2 ml-1">Fájlok feltöltése</h3>
                <button onclick="closeUploadModal()" class="text-gray-500 hover:text-gray-700 mt-0 mb-2 mr-2">
                    ✕
                </button>
            </div>

            <!-- Uppy Dashboard konténer (görgethető) -->
            <div id="uppy-dashboard" class="flex-1 overflow-auto"></div>

            <!-- Kiegészítő információk -->
            <div id="upload-info" class="mt-4 text-sm text-gray-600"></div>
        </div>
    </div>
</div>

