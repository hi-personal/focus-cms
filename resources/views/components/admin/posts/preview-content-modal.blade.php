<div id="previewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="max-w-full p-1 my-0 mx-auto h-full max-h-full">
        <div class="bg-white rounded-lg shadow-xl pt-0 pb-1 px-0 h-full flex flex-col">
            <!-- Fejléc -->
            <div class="flex justify-between items-center border-bottom p-2">
                <span class="order-first text-lg font-bold px-3 py-1">Előnézet</span>

                <button id="closeModal_2" class="closeModal bg-red-500 text-white order-last px-3 py-1 rounded">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Tartalom -->
            <div id="previewContent" class="bg-re-600 flex-1 overflow-y-auto  prose max-w-none">
            </div>
        </div>
    </div>
</div>