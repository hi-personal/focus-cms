<x-app-layout>
    <div class="container max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 pt-1 pb-8 md:px-8 bg-white min-h-screen">
        <h1 class="text-2xl font-bold mb-4">Oldalsávok</h1>

        <div>
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <form method="post" action="{{ route('admin.settings.sidebars.update') }}">
                @csrf
                @method('post')

                <div class="py-3">
                    @forelse($sidebars as $name => $value)
                        <h2>{{ __("theme::titles.{$name}") }}</h2>

                        <textarea
                            class="block w-full h-[300px] border rounded overflow-x-hidden overflow-y-auto resize-y"
                            name="{{ $name }}"
                        >{{ old($name, $value) }}</textarea>
                    @empty
                        <h2>Nincsenek oldalsávok</h2>
                    @endforelse
                </div>

                <div class="my-4 flex justify-end">
                    <button
                        type="submit"
                        class="py-3 px-5 text-lg text-black bg-blue-400 hover:bg-blue-500 hover:text-white rounded border"
                    >Mentés</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>