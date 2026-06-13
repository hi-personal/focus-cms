<x-app-layout>
    <div
        x-data="{
            isOpen: false,
            groupAction: 'none',
            showGroupActionButtonTooltip: false,
            perPage: {{ $perPage }}
        }"
        class="container max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 pt-1 pb-8 md:px-8 bg-white min-h-screen"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-1 md:gap-4 items-center">
            <div class="text-center md:text-left">
                <h1 class="text-2xl font-bold mb-4">{{ __(Str::ucfirst($postType."s")) }}</h1>
            </div>
            <div class="text-center md:text-right max-md:mb-4">
                <a href="{{ route('post.create', ['post_type'=>$postType]) }}" class="ml-2 py-2 px-4 bg-blue-500 hover:bg-blue-400 hover:text-white border rounded">
                    <i class="mr-1 mdi mdi-plus"></i>{{ __('Új létrehozása') }}
                </a>
            </div>
        </div>
        <!-- Szűrő Form -->
        <form
            id="postListForm"
            method="GET"
            action="{{ route('posts.index', ['post_type'=>$postType]) }}"
            class="mb-4"
            x-ref="perPageForm"
        >
            @csrf

            <input
                type="hidden"
                id="groupActionFormUrl"
                name="groupActionFormUrl"
                value="{{ route('post.group-action', ['post_type'=>$postType]) }}"
            >

            <!-- Filter section in 3-column grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <!-- Mindig látható elem (Per Page) -->
                <div class="">
                    <label for="per_page" class="block text-md font-medium text-gray-700"><i class="mdi mdi-numeric"></i> Megjelenített bejegyzések</label>
                   <select
                        name="per_page"
                        id="per_page"
                        x-model="perPage"
                        @change="$refs.perPageForm.submit()"
                        class="w-full py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        @foreach ([2, 3, 20, 40, 60, 100, 500] as $size)
                            <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>

                </div>

                <!-- Gomb a mobil nézetben -->
                <div class="md:hidden col-span-full">
                    <button
                        type="button"
                        class="w-full bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-md shadow-sm flex items-center justify-between"
                        @click="isOpen = !isOpen"
                    >
                        <span>
                            <i class="mdi" :class="isOpen ? 'mdi-chevron-up' : 'mdi-chevron-down'"></i>
                            További lehetőségek
                        </span>
                    </button>
                </div>

                <!-- Összecsukható elemek csoportja -->
                <template x-if="true">
                    <div class="contents pt-5 pb-8 px-1 my-3 rounded border-solid border-slate-300  border shadow-lg overflow-hidden" :class="{ 'max-md:hidden': !isOpen }">
                        <!-- Status -->
                        <div class="">
                            <label for="status" class="block text-md font-medium text-gray-700"><i class="mdi mdi-file-settings-outline"></i> Állapot</label>
                            <select id="status" name="status" class="w-full py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Összes</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publikált</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Vázlat</option>
                                <option value="private" {{ request('status') == 'private' ? 'selected' : '' }}>Privát</option>
                                <option value="trash" {{ request('status') == 'trash' ? 'selected' : '' }}>Lomtárban</option>
                            </select>
                        </div>

                        <!-- Author -->
                        <div class="">
                            <label for="author" class="block text-md font-medium text-gray-700"><i class="mdi mdi-account-outline"></i> Szerző</label>
                            <select name="author" id="author" class="w-full py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Összes</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ request('author') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="">
                            <label for="category" class="block text-md font-medium text-gray-700"><i class="mdi mdi-shape-outline"></i> Kategória</label>
                            <select name="category" id="category" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Összes</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sort Order -->
                        <div class="">
                            <label for="sort_order" class="block text-md font-medium text-gray-700">
                                <i class="mdi mdi-sort-{{ request('sort_order') == 'asc' ? 'reverse-variant' : 'variant' }}"></i>
                            Sorrend dátum szerint</label>
                            <select id="sort_order" name="sort_order" class="w-full py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Csökkenő (Legújabb elöl)</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Növekvő (Legrégebbi elöl)</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="">
                            <label class="block text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> Dátum tartomány</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 w-full">
                                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Válassz">
                                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <div x-show="groupAction === 'setNewCategory'" class="">
                            <label for="new_category" class="block text-md text-blue-800 font-extrabold"><i class="mdi mdi-shape-outline"></i> Új Kategória</label>
                            <select name="new_category" id="new_category" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Válassz</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div x-show="groupAction === 'setNewAuthor'" class="">
                            <label for="new_author" class="block text-md text-blue-800 font-extrabold"><i class="mdi mdi-shape-outline"></i> Új szerző</label>
                            <select name="new_author" id="new_author" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Válassz</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </template>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-6 lg:gap-10 mt-6 mb-6">
                <div class="" :class="{ 'max-md:hidden': !isOpen }">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-6 w-full">
                        <select
                            id="group_actions_select"
                            name="group_actions_select"
                            x-model="groupAction"
                            class="flex py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="none">Csoportos művelet (válassz)</option>
                            <optgroup label="Új állapot">
                                <option value="published">Közzévtéve</option>
                                <option value="draft">Vázlat</option>
                                <option value="private">Privát</option>
                                <option value="trash">Lomtár</option>
                            </optgroup>
                            @if (request('status') == 'trash')
                                <optgroup label="Lomtár elemei">
                                    <option value="delete">Kijelöltek végleges törlése</option>
                                </optgroup>
                            @endif
                            <optgroup label="Taxonómia">
                                <option value="setNewCategory">Kategória</option>
                            </optgroup>
                            <optgroup label="Meta">
                                <option value="setNewAuthor">Szerző</option>
                            </optgroup>
                        </select>

                        <div x-data="{ showGroupActionButtonTooltip: false }" class="relative inline-block">
                            <button
                                type="button"
                                id="groupActionSubmit"
                                @mouseenter="if (groupAction == 'none') showGroupActionButtonTooltip = true"
                                @mouseleave="showGroupActionButtonTooltip = false"
                                class="flex w-full text-white px-4 py-2 rounded-md shadow-sm justify-center"
                                :class="{
                                    'bg-gray-500 cursor-not-allowed': groupAction == 'none',
                                    'bg-indigo-600 hover:bg-indigo-700': groupAction != 'none'
                                }"
                                :disabled="groupAction == 'none'"
                                formaction="{{ route('post.group-action', ['post_type'=>$postType]) }}"
                            >
                                <i class="mdi mdi-chevron-right-box-outline"></i> Végrehajtás
                            </button>

                            <!-- Tooltip -->
                            <div
                                x-show="showGroupActionButtonTooltip"
                                x-transition
                                class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-4 py-2 text-normal text-black bg-yellow-300 rounded shadow-lg z-50"
                                style="display: none;"
                            >
                                Válassz csoportos műveletet
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-6 w-full">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md shadow-sm">
                        <i class="mdi mdi-filter-outline"></i> Szűrés
                    </button>
                    <a href="{{ route('posts.index', ['post_type'=>$postType]) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md shadow-sm text-center">
                        <i class="mdi mdi-filter-off-outline"></i> Alapértelmezett
                    </a>
                </div>
            </div>

            <!-- Csoportos műveletek vezérlő elemei -->
            <div class="">
                <table id="postListTable" class="w-full border-collapse table-auto text-sm sm:text-base">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="post-selector-col border p-2">
                            <button
                                type="button"
                                name=""
                                id="selectAllPost"
                                class="py-1 px-2 bg-white text-black hover:bg-blue-600 hover:text-white hover:border-white shadow shadow-gray-500 hover:shadow-blue-500 rounded border border-gray-500"
                            >
                                <i class="mdi mdi-checkbox-marked-outline"></i>
                            </button>
                            </th>
                            <th class="border p-2 max-md:hidden">ID</th>
                            <!-- Mobile-only header cell -->
                            <th class="border p-2 md:hidden">További adatok</th>
                            <!-- Desktop headers -->
                            <th class="border p-2 max-md:hidden">Cím</th>
                            <th class="border p-2 max-md:hidden">Szerző</th>
                            <th class="border p-2 max-md:hidden">Kategória</th>
                            <th class="border p-2 max-md:hidden">Dátum</th>
                            <th class="border p-2 max-md:hidden">Állapot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr class="hover:bg-gray-600 hover:font-bold <x-admin.posts.post-status-text-color-class status='{{ $post->status }}' class_only='true' only='text' />">
                                <!-- First 2 columns (always visible) -->
                                <td class="border p-2 text-center">
                                    <input type="checkbox" name="selected_post[]" value="{{ $post->id }}" class="post-checkbox" />
                                </td>
                                <td
                                    class="border p-2 text-center max-md:hidden hover:cursor-pointer hover:bg-gray-100"
                                    @click="window.open('{{ url($post->name) }}', '_blank')"
                                >
                                    {{ $post->id }}
                                </td>

                                <!-- Mobile: Collapsed data cell -->
                                <td class="border p-3 md:hidden">
                                    <div class="grid grid-cols-1 gap-1">
                                        <!-- Clickable title -->
                                        <div class="mb-2 text-base">
                                            <span class="font-semibold">Cím:</span>
                                            <a href="{{ route('post.edit', ['post_type' => $postType, 'post' => $post->id]) }}" target="_self">
                                                <span class="cursor-pointer hover:underline max-md:font-bold">
                                                    <x-admin.posts.post-status-text-color-class
                                                        status="{{ $post->status }}"
                                                        icon="true"
                                                        only="icon"
                                                        class="ml-1"
                                                    />{{ $post->title }}</span>
                                            </a>
                                        </div>
                                        <!-- Other fields -->
                                        <div>
                                            <span class="font-semibold">ID:</span> {{ $post->id }} |
                                            <span class="font-semibold">Szerző:</span> {{ $post->author->name ?? 'N/A' }}
                                        </div>
                                        <div><span class="font-semibold">Kategória:</span> {{ $post->terms()->where('post_taxonomy_name', 'categories')->first()->name ?? 'N/A' }}</div>
                                        <div><span class="font-semibold">Dátum:</span> {{ $post->created_at->format('Y-m-d') }} |
                                            <span class="font-semibold">Állapot:</span>
                                            <span class="inline-flex items-center">
                                                <x-admin.posts.post-status-text-color-class
                                                    status="{{ $post->status }}"
                                                    icon="true"
                                                    only="text"
                                                    class="ml-1"
                                                >
                                                    {{ __(ucfirst($post->status)) }}
                                                </x-admin.posts.post-status-text-color-class>
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Desktop: Last 5 columns -->
                                <td class="border flex py-2 max-md:hidden cursor-pointer">
                                    <a class="w-full h-full px-2" href="{{ route('post.edit', ['post_type' => $postType, 'post' => $post->id]) }}" target="_self">
                                        {{ $post->title }}
                                    </a>
                                </td>
                                <td class="border p-2 max-md:hidden">{{ $post->author->name ?? 'N/A' }}</td>
                                <td class="border p-2 max-md:hidden">{{ $post->terms()->where('post_taxonomy_name', 'categories')->first()->title ?? 'N/A' }}</td>
                                <td class="border p-2 text-center max-md:hidden">{{ $post->created_at->format('Y-m-d') }}</td>
                                <td class="border p-2 max-md:hidden <x-admin.posts.post-status-text-color-class status='{{ $post->status }}' class_only='true' only='bg' />">
                                    <x-admin.posts.post-status-text-color-class
                                        status="{{ $post->status }}"
                                        icon="true"
                                        only="text"
                                    >
                                        {{ __(ucfirst($post->status)) }}
                                    </x-admin.posts.post-status-text-color-class>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="border p-2 text-center text-gray-500">Nincs találat</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Lapozás -->
        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    </div>

    @push('scripts')
        <!-- jQuery AJAX kód a beállítás mentéséhez -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                $("#groupActionSubmit").on("click", function(){
                    $("#postListForm")
                        .attr("method", "post")
                        .attr("action", $("#groupActionFormUrl").val())
                        .append('<input type="hidden" name="_method" value="post">')
                        .submit();
                });


                $("#selectAllPost").on("click", function() {
                    var table = $(this).closest('table');
                    var checkboxes = table.find('.post-checkbox');
                    var allChecked = checkboxes.length === checkboxes.filter(':checked').length;

                    checkboxes.prop("checked", !allChecked);
                });

                if (window.$) { // Ellenőrizzük, hogy jQuery elérhető-e
                    $(document).ready(function() {
                        $('#per_page').change(function() {
                            let perPageValue = $(this).val();

                            $.ajax({
                                url: "{{ route('admin.savePerPage') }}",
                                method: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    per_page: perPageValue
                                },
                                success: function(response) {
                                    console.log("Beállítás mentve:", response);
                                },
                                error: function(error) {
                                    console.error("Hiba történt:", error);
                                }
                            });
                        });
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>

