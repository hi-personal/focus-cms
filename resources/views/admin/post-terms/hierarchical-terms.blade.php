@php
    $defaultLocale=config('app.locale');
    $supportedLocales=config('app.supported_locales',[]);
@endphp

<x-app-layout :includeTinymce="false">

    <div class="container max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 pb-8 bg-white min-h-screen">

        <div class="lg:hidden text-center">

            <h1 class="text-2xl font-bold">
                {{ __(Str::ucfirst($taxonomy_name)) }}
            </h1>

        </div>

        @if(session('success'))

            <div
                x-data="{ show:true }"
                x-init="setTimeout(()=>show=false,6000)"
                x-show="show"
                x-cloak
                x-transition
                class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4"
            >
                {{ session('success') }}
            </div>

        @endif

        @if(session('error'))

            <div
                x-data="{ show:true }"
                x-init="setTimeout(()=>show=false,10000)"
                x-show="show"
                x-cloak
                x-transition
                class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4"
            >
                {{ session('error') }}
            </div>

        @endif

        <div class="grid grid-cols-1 lg:grid-cols-[2fr_3fr] gap-2">

            <div class="p-2">

                <h3 class="lg:mb-3">
                    Új {{ $config['title']??$taxonomy_name }} létrehozása
                </h3>

                <form
                    method="post"
                    action="{{ route('taxonomy.create',['taxonomy_name'=>$taxonomy_name]) }}"
                >

                    @csrf

                    <div class="p-4 border border-gray-300 rounded">

                        @if($hierarchical)

                            <label>Szülő elem</label>

                            <select
                                name="parent_id"
                                class="mt-2 mb-3 w-full border border-gray-300 rounded"
                            >

                                <option value="0">Nincs</option>

                                @foreach($allTerms as $term)

                                    <option
                                        value="{{ $term->id }}"
                                        {{ session('parent_id')==$term->id ? "selected":null }}
                                    >
                                        {{ (empty($term->depth)?null:str_repeat("- ",$term->depth)." ").$term->localizedTitle() }}
                                    </option>

                                @endforeach

                            </select>

                        @endif

                        @foreach($supportedLocales as $locale)

                            @php

                                $isDefault=
                                    $locale===$defaultLocale;

                                $titleFieldName=
                                    "title-{$locale}";

                                $nameFieldName=
                                    "name-{$locale}";

                                $descriptionFieldName=
                                    "description-{$locale}";

                            @endphp

                            <div class="mb-6 p-4 border rounded bg-gray-50">

                                <div class="mb-4 text-lg font-bold">
                                    {{ strtoupper($locale) }}
                                </div>

                                <label>
                                    Cím ({{ strtoupper($locale) }})
                                </label>

                                <input
                                    type="text"
                                    name="{{ $titleFieldName }}"
                                    value="{{ old($titleFieldName) }}"
                                    class="mt-2 mb-2 w-full border border-gray-300 rounded px-2 py-1"
                                    placeholder="Title ({{ strtoupper($locale) }})"
                                    {{ $isDefault ? 'required' : null }}
                                >

                                <label>
                                    Keresőbarát név ({{ strtoupper($locale) }})
                                </label>

                                <input
                                    type="text"
                                    name="{{ $nameFieldName }}"
                                    value="{{ old($nameFieldName) }}"
                                    class="mt-2 mb-2 w-full border border-gray-300 rounded px-2 py-1"
                                    placeholder="Slug ({{ strtoupper($locale) }})"
                                >

                                <label>
                                    Leírás ({{ strtoupper($locale) }})
                                </label>

                                <textarea
                                    name="{{ $descriptionFieldName }}"
                                    class="my-2 w-full border border-gray-300 rounded"
                                    placeholder="Leírás ({{ strtoupper($locale) }})"
                                >{{ old($descriptionFieldName) }}</textarea>

                            </div>

                        @endforeach

                        <button
                            type="submit"
                            class="my-2 py-2 px-3 bg-blue-500 hover:bg-blue-400 text-white border rounded"
                        >
                            Mentés
                        </button>

                    </div>

                </form>

            </div>

            <div class="p-2">

                <h3 class="lg:mb-4">
                    {{ __(ucfirst($taxonomy_name)) }}
                </h3>

                <table class="w-full border-collapse table-auto">

                    <thead>

                        <tr class="bg-gray-200">

                            <th class="border p-2"></th>

                            <th class="border p-2">
                                Cím
                            </th>

                            <th class="border p-2">
                                ID
                            </th>

                            <th class="border p-2">
                                P
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($allTerms as $term)

                            @php
                                $postsN=$term->posts->count();
                            @endphp

                            <tr class="hover:bg-gray-100">

                                <td class="border p-2 text-center">

                                    <input type="checkbox">

                                </td>

                                <td class="border p-3">

                                    <a
                                        class="hover:text-blue-600"
                                        href="{{ route('taxonomy.edit',['taxonomy_name'=>$taxonomy_name,'term'=>$term->id]) }}"
                                    >
                                        {{ (empty($term->depth)?null:str_repeat("- ",$term->depth)." ").$term->localizedTitle() }}
                                    </a>

                                </td>

                                <td class="border text-center">
                                    {{ $term->id }}
                                </td>

                                <td class="border text-center">
                                    {{ $postsN }}
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="4"
                                    class="border p-2 text-center text-gray-500"
                                >
                                    Nincs találat
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-app-layout>