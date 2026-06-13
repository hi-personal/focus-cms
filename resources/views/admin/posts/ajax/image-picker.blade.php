<div id="imagePickerContent">
    <div id="imagePickerContentJsonData" class="hidden">{{ json_encode($modalImageSizes) }}</div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-1 sm:gap-4 p-2">
        @foreach($images as $image)
            <div class="selectorImageDiv border rounded flex items-center justify-center p-1 sm:p-2 cursor-pointer bg-black bg-opacity-10 hover:bg-gray-100 overflow-hidden relative">
                <!-- Kép megjelenítése -->
                <label
                    x-data="{ checked: false }"
                    :class="{
                        'border-blue-500 shadow-inner ring-2 ring-white rounded relative image-checkbox-checked': checked,
                        'border-transparent': !checked
                    }"
                    class="image-checkbox select-image flex items-center justify-center p-0.5 cursor-pointer border-4 box-border outline-none transition-all"
                >
                    <!-- Rejtett trigger elem -->
                    <div @click="checked = !checked" class="js-checkbox-trigger hidden"></div>

                    <img
                        src="{{ $image->image->getImageUrl('thumbnail') }}"
                        alt="Image"
                        class="selectorImage w-full h-auto min-w-full min-h-full object-cover object-center"
                        data-id="{{ $image->image->id }}"
                        data-original-url="{{ $image->image->getImageUrl('original') }}"
                        data-medium-url="{{ $image->image->getImageUrl('medium') }}"
                    >

                    <!-- Az ikon csak kijelölt állapotban látszik -->
                    <i
                        :class="{
                            'flex': checked,
                            'hidden': !checked
                        }"
                        class="mdi mdi-checkbox-outline md-24 absolute top-[-14px] right-[-14px] py-0.5 px-1.5 bg-blue-600 border rounded border-blue-400 shadow-md text-white text-md font-extrabold">
                    </i>

                    <div class="selectorImageOpenPreviewModal touch:hidden absolute p-2 top-0 left-0 w-full h-full rounded bg-transparent hover:bg-gray-500 hover:bg-opacity-10 flex items-center justify-center text-transparent hover:text-black transition-colors duration-200">
                        <i class="mdi mdi-arrow-top-right-thin-circle-outline text-[4rem]"></i>
                    </div>
                </label>
            </div>
        @endforeach
    </div>
</div>
