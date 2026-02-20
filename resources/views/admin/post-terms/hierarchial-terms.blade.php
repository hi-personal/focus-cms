@php
    function getChildsRecursive($term, $parentId = 0, $i = 0)
    {
        $i++;
        $childrens = $term->directChildren;

        if(!empty($childrens)) {
            foreach($childrens as $children) {
                echo '<p>';
                    echo str_repeat('-', $i)," ";
                echo $children->title;
                echo '</p>';
                getChildsRecursive($children, $children->parent_id, $i);
            }
        }
    }
@endphp

<x-app-layout :includeTinymce="false">
    <div class="container max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 pt-0 pb-8 bg-white min-h-screen">

        <div class="lg:hidden text-center lg:text-left">
            <h1 class="my-0 text-2xl font-bold md:my-2">{{ __(Str::ucfirst($taxonomy_name)) }}</h1>
        </div>

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

        <div class="grid grid-cols-1 lg:grid-cols-[2fr_3fr] gap-1 lg:gap-2">
            <div class="p-2">
                <h3 class="lg:mt-0 lg:mb-3">Új kategória létrehozása</h3>
                <form method="post" action="{{ route('taxonomy.create', ['taxonomy_name'=>$taxonomy_name]) }}">
                    @csrf
                    @method('post')
                    <div class="p-4 border border-gray-350 rounded">
                        @if($hierarchial)
                            <label class="w-full">Szülő elem</label>
                            <select
                                name="parent_id"
                                class="mt-2 mb-3 w-full border border-gray-300 ú rounded"
                                placeholder="Szülő elem"
                            >
                                <option value="0">Nincs</option>
                                @foreach($allTerms as $term)
                                    <option
                                        value="{{ $term->id }}"
                                        {{ !empty(session('parent_id')) && session('parent_id') == $term->id ? "selected" : null }}
                                    >{{ (empty($term->depth) ? null : str_repeat("- ", $term->depth)." ").$term->title}}</option>
                                @endforeach
                            </select>
                        @endif
                        <label class="w-full">Cím</label>
                        <div
                            x-data="{
                                value: '',
                                hierarchial: $el.querySelector('input').dataset.hierarchial === '0',
                                showNotice: false,
                                update(e) {
                                    const original = e.target.value;
                                    const converted = this.hierarchial ? original.toLowerCase() : original;

                                    if (this.hierarchial && original !== converted) {
                                        this.showNotice = true;
                                        setTimeout(() => this.showNotice = false, 3000);
                                    }

                                    this.value = converted;
                                }
                            }"
                            class="relative"
                        >
                            <input
                                type="text"
                                name="title"
                                x-model="value"
                                @input="update"
                                class="mt-2 mb-1 w-full border border-gray-300 rounded px-2 py-1"
                                placeholder="{{ __('Title') }}"
                                data-hierarchial="{{ $hierarchial ? 1 : 0 }}"
                                autofocus
                            >

                            <!-- Figyelmeztetés -->
                            <div
                                x-show="showNotice"
                                x-transition
                                class="absolute bottom-full left-0 mt-1 text-sm text-orange-600 bg-orange-100 px-2 py-1 rounded shadow"
                            >
                                Csak kis betűk engedélyezettek!
                            </div>
                        </div>

                        <label class="w-full">Leírás</label>
                        <div x-data="{ focused: false }">
                            <textarea
                                name="description"
                                x-on:focus="focused = true"
                                x-on:blur="focused = false"
                                :class="focused ? 'min-h-[300px]' : 'min-h-[30px]'"
                                class="my-2 w-full border border-gray-300 rounded transition-all duration-200 ease-in-out"
                                placeholder="Leírás"
                            ></textarea>
                        </div>
                        <button type="submit" class="my-2 py-2 px-3 bg-blue-500 hover:bg-blue-400 hover:text-white border rounded">Mentés</button>
                    </div>
                </form>
            </div>
            <div class="p-2">
                <h3 class="lg:mt-0 lg:mb-4">{{ __(ucfirst($taxonomy_name)) }}</h3>

                @if($hierarchial || true)
                    <table id="postListTable" class="w-full border-collapse table-auto text-sm sm:text-base">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="post-selector-col border p-2">
                                <button
                                    type="button"
                                    name=""
                                    id="selectAllPostTerm"
                                    class="py-1 px-2 bg-white text-black hover:bg-blue-600 hover:text-white hover:border-white shadow shadow-gray-500 hover:shadow-blue-500 rounded border border-gray-500"
                                >
                                    <i class="mdi mdi-checkbox-marked-outline"></i>
                                </button>
                                </th>

                                <!-- Mobile-only header cell -->
                                <th class="border p-2">Cím, további adatok</th>
                                <!-- Desktop headers -->

                                <th class="border p-2">ID</th>
                                <th class="border p-2 max-md:hidden">P (n)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allTerms as $term)
                                @php
                                    $posts = $term->posts;
                                    $postsN = empty($posts) ? 0 : count($posts);
                                    $parent = $term->parent;
                                @endphp
                                <tr class="hover:bg-gray-100 hover:font-bold hover:text-black">
                                    <td class="w-[10px] border p-2 text-center">
                                        <input type="checkbox" name="selected_post[]" value="{{ $term->id }}" class="post-term-checkbox" />
                                    </td>
                                    <td class="border p-3 " x-data="{ open: false }">
                                        <div class="flex flex-col gap-2">
                                            <div class="flex justify-between items-start gap-4">
                                                <div class="flex items-center gap-2">
                                                    <span class="hidden font-semibold">Cím:</span>
                                                    <a
                                                        class="hover:text-blue-600"
                                                        href="{{ route('taxonomy.edit', ['taxonomy_name' => $taxonomy_name, 'term' => $term->id]) }}"
                                                    >{{ (empty($term->depth) ? null : str_repeat("- ", $term->depth)." ").$term->title }}</a>
                                                    </span>
                                                </div>

                                                <div class="text-sm space-y-1">
                                                    <button
                                                        type="button"
                                                        class="py-1 px-3 hover:bg-gray-300 rounded border"
                                                        @click="open = !open"
                                                        :aria-expanded="open"
                                                    >
                                                        <i :class="open ? 'mdi mdi-arrow-up mdi-18' : 'mdi mdi-arrow-down mdi-18'"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="text-sm" x-cloak x-show="open" x-transition>
                                                @if(!empty($parent))
                                                    <div><span class="my-2 font-semibold">Szülő elem:</span> {{ $parent->title }}</div>
                                                @endif
                                                <div><span class="my-2 font-semibold">Bejegyzések száma:</span> {{ $postsN }}</div>
                                                <div><span class="my-2 font-semibold">Keresőbarát név:</span> {!! Str::limit($term->name, 240, '<span class="ml-1 text-gray-400">[...]</span>') !!}</div>
                                                <div class="my-4">
                                                    <a
                                                        type="button"
                                                        class="py-2 px-3 bg-blue-500 text-white hover:bg-blue-400 border rounded hover:cursor-pointer"
                                                        href="{{ route('taxonomy.edit', ['taxonomy_name' => $taxonomy_name, 'term' => $term->id]) }}"
                                                    ><i class="mdi mdi-pencil-outline mdi-18 mr-1"></i>Szerkesztés</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Desktop: Last columns -->
                                    <td class="border text-center p-3">{{ $term->id }}</td>
                                    <td class="border text-center p-2 max-md:hidden">{{ $postsN }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="border p-2 text-center text-gray-500">Nincs találat</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <div class="flex flex-wrap">
                        @foreach($terms as $term)
                            <a
                                class="my-1 mx-2 py-2 px-3 bg-blue-500 text-white hover:bg-blue-400 border rounded hover:cursor-pointer"
                                href="{{ route('taxonomy.edit', ['taxonomy_name' => $taxonomy_name, 'term' => $term->id]) }}"
                            >{{ $term->title }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- jQuery AJAX kód a beállítás mentéséhez -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                $("#selectAllPostTerm").on("click", function() {
                    var table = $(this).closest('table');
                    var checkboxes = table.find('.post-term-checkbox');
                    var allChecked = checkboxes.length === checkboxes.filter(':checked').length;

                    checkboxes.prop("checked", !allChecked);
                });
            });
        </script>
    @endpush
</x-app-layout>

