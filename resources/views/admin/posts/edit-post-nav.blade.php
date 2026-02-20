<div
    x-data="{ isSticky: false }"
    x-init="
        window.addEventListener('scroll', () => {
            isSticky = window.scrollY > 3000000000000;
            if (isSticky) {
                isOpen = false; // Bezárja az accordion-t, ha a sticky mód aktív lesz
            }
        });
    "
    :class="isSticky ? '-mx-6 py-2 px-3 bg-gray-50 shadow-lg top-0' : 'py-1 bg-transparent'"
    class="sticky flex z-10 justify-center transition-all duration-300"
    id="postEditNavContainer"
>

    <div class="flex-grow grid grid-flow-col grid-rows-2 xl:grid-rows-1 gap-3 items-center max-md:hidden">
        <!-- Fájlok feltöltése gomb -->
        <x-admin.posts.post-editor-menu-item
            type="button"
            icon="mdi mdi-upload md-24"
            text="{{ __('Upload') }}"
            onclick="openUploadModal()"
        />

        <!-- Képek beillesztése gomb -->
        <x-admin.posts.post-editor-menu-item
            type="button"
            class="insert-image-album"
            icon="mdi mdi-file-image-plus-outline md-24"
            text="{{ __('Images') }}"
            data-mode="insert"
        />

        <!-- Fájlok beillesztése gomb -->
        <x-admin.posts.post-editor-menu-item
            type="button"
            class="insert-file-album"
            icon="mdi mdi-file-plus-outline md-24"
            text="{{ __('Files') }}"
        />

        <!-- Mentés gomb -->
        <x-admin.posts.post-editor-menu-item
            x-init="
                window.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                        e.preventDefault();
                        document.getElementById('post-edit-form').submit();
                    }
                });
            "
            type="submit"
            icon="mdi mdi-content-save-outline md-24"
            text="{{ __('Save') }}"
        />

        <!-- Törlés link -->
        <x-admin.posts.post-editor-menu-item
            href="#"
            class="deletePostButton"
            data-href="{{ route('post.delete', ['post_type'=>$post->post_type_name, 'post'=>$post->id]) }}"
            icon="mdi mdi-delete-outline md-24"
            text="{{ __('Delete') }}"
        />

        <!-- Megnyit link -->
        <x-admin.posts.post-editor-menu-item
            href="{{ route('post.show', ['slug'=>$post->name]) }}"
            target="_blank"
            icon="mdi mdi-link-variant md-24"
            text="{{ __('Open') }}"
        />

        <!-- Előnézet gomb -->
        <x-admin.posts.post-editor-menu-item
            type="button"
            class="previewButton"
            icon="mdi mdi-eye-outline md-24"
            text="{{ __('Preview') }}"
        />
    </div>

    <!-- Részletek gomb -->
    <button
        type="button"
        @click="$nextTick(() => {
            if (isSticky) {
                const target = document.querySelector('#post-title-label');
                if (target) {
                    const offset = 20;
                    const targetPosition = target.getBoundingClientRect().top + window.scrollY;

                    window.scrollTo({
                        top: targetPosition - offset,
                        behavior: 'smooth'
                    });
                }
            }
            isOpen = !isOpen;
        })"
        class="inline-flex items-center px-4 py-2 md:ml-3 text-sm font-medium border border-gray-900 rounded transition-colors duration-200
            bg-white text-black hover:bg-gray-900 hover:text-white"
        :class="{
            'bg-black-400 text-black hover:bg-orange-600': isOpen,
            'bg-white text-black': !isOpen
        }"
        id="detailsInputsOpen"
    >
        <i
            class="mdi mdi-arrow-down mdi-18 transition-transform duration-300"
            :class="{
                'mdi-chevron-down rotate-180': isOpen,
                'mdi-chevron-down rotate-0': !isOpen
            }"
        ></i>
        Részletek
    </button>
    <!-- END: Részletek gomb -->

    <button
        id="exit-editor"
        @click="isOpen = false"
        class="hidden items-center px-4 py-2 ml-3 text-sm font-medium text-black bg-transparent border border-gray-900 rounded hover:bg-gray-900 hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white"
        type="button"><i class="mdi mdi-close"></i></button>

    <!-- Mentés gomb -->
    <x-admin.posts.post-editor-menu-item
        type="submit"
        id="postEditNavWMiddleSaveButton"
        icon="mdi mdi-content-save-outline md-18"
        text="{{ __('Save '.Str::ucfirst($post->post_type_name)) }}"
        class="ml-3 max-sm:hidden md:hidden"
    />

    <!-- Offcanvas menü konténer -->
    <div class="inline-flex md:hidden relative">
        <!-- Gomb a menü megnyitásához -->
        <button
            type="button"
            @click="isOpenOCEditMenu = !isOpenOCEditMenu"
            class="ml-3 px-6 py-2 rounded-md border border-black hover:bg-blue-600 focus:outline-none"
        >
            <i class="mdi mdi-dots-vertical md-18"></i>
        </button>

        <!-- Offcanvas menü -->
        <div
            x-show="isOpenOCEditMenu"
            x-cloak
            @click.away="isOpenOCEditMenu = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="-translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="-translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 min-w-64 bg-gray-200 rounded-r-md shadow-lg overflow-y-auto z-40"
        >
            <!-- Menü fejléc -->
            <div class="py-4 px-8 border-b border-gray-300">
                <div class="flex items-center justify-left text-gray-800">
                    <button type="button" @click="isOpenOCEditMenu = false" class="hover:text-gray-900">
                        <i class="mdi mdi-menu-close md-24 mr-1"></i>
                    </button>
                    <div class="flex items-center">
                        <span class="text-sm">Szerkesztő menü</span>
                    </div>
                </div>
            </div>

            <!-- Menü elemek -->
            <div class="p-4">
                <x-admin.posts.post-editor-menu-item
                    type="submit"
                    icon="mdi mdi-content-save-outline md-24"
                    text="{{ __('Save '.Str::ucfirst($post->post_type_name)) }}"
                    class="sm:hidden"
                    offcanvasItem
                />

                <!-- Előnézet gomb -->
                <x-admin.posts.post-editor-menu-item
                    type="button"
                    class="previewButton"
                    icon="mdi mdi-eye-outline md-24"
                    text="{{ __('Preview') }}"
                    offcanvasItem
                />

                <x-admin.posts.post-editor-menu-item
                    href="{{ route('post.show', ['slug'=>$post->name]) }}"
                    target="_blank"
                    icon="mdi mdi-link md-24"
                    text="{{ __('Megnyit') }}"
                    offcanvasItem
                />

                <!-- Fájlok feltöltése gomb -->
                <x-admin.posts.post-editor-menu-item
                    type="button"
                    icon="mdi mdi-upload md-24"
                    text="{{ __('Upload files') }}"
                    onclick="openUploadModal()"
                    offcanvasItem
                />

                <!-- Képek beillesztése gomb -->
                <x-admin.posts.post-editor-menu-item
                    type="button"
                    class="insert-image-album"
                    icon="mdi mdi-file-image-plus-outline md-24"
                    text="{{ __('Insert images') }}"
                    offcanvasItem
                />

                <!-- Fájlok beillesztése gomb -->
                <x-admin.posts.post-editor-menu-item
                    type="button"
                    class="insert-file-album"
                    icon="mdi mdi-file-plus-outline md-24"
                    text="{{ __('Insert files') }}"
                    offcanvasItem
                />

                <!-- Törlés gomb -->
                <x-admin.posts.post-editor-menu-item
                    type="button"
                    class="deletePostButton"
                    icon="mdi mdi-delete-outline md-24"
                    data-href="{{ route('post.delete', ['post_type'=>$post->post_type_name, 'post'=>$post->id]) }}"
                    text="{{ __('Delete '.Str::ucfirst($post->post_type_name)) }}"
                    offcanvasItem
                />
            </div>
        </div>
    </div>
</div>