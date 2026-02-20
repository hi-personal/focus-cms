<div class="gallery-box">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 p-2">
        @foreach($images as $image)
            <div class="border rounded items-center justify-center p-2 cursor-pointer bg-black bg-opacity-10 hover:bg-gray-100 overflow-hidden">
                <div class="justify-center aspect-square flex">
                    <img
                        src="{{ $image->getImageUrl('medium') }}"
                        alt="Image"
                        class="w-full h-auto min-w-full min-h-full object-cover object-center"
                        data-id="{{ $image->id }}"
                        data-original-url="{{ $image->getImageUrl('original') }}"
                        data-medium-url="{{ $image->getImageUrl('medium') }}"
                        load="lazy"
                    >
                </div>
            </div>
        @endforeach
    </div>
</div>