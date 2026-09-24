@php
    $viteIsActive = function_exists('check_module_vite_is_active') ? check_module_vite_is_active() : false;

    if ($viteIsActive === true) {
        // Ha aktív a Vite, átadjuk a fájlok listáját
        $viteAssets = [
            'resources/css/app.css',
            'resources/css/style.css',
            'resources/js/app.js',
        ];

        if ($includeTinymce ?? false) {
            $viteAssets[] = 'resources/js/uppy.js';
        }
    } else {
        // Ha nem aktív, betöltjük a manifest.json-t és átalakítjuk
        $manifestPath = ("public/build/admin/admin-manifest.json");
        $viteAssets = [];
        $viteAssets = $manifestPath;

        if (false && file_exists($manifestPath)) {
            $manifestContent = file_get_contents($manifestPath);
            $data = json_decode($manifestContent, true);

            // Kigyűjtjük az entry point-okat és a hozzájuk tartozó fájlokat
            foreach ($data as $key => $item) {
                if (isset($item['isEntry']) && $item['isEntry'] === true) {
                    // Az entry point-okhoz tartozó JS és CSS fájlok
                    if (isset($item['file'])) {
                        $viteAssets[] = 'build/admin/' . $item['file'];
                    }

                    // Ha vannak CSS fájlok
                    if (isset($item['css']) && is_array($item['css'])) {
                        foreach ($item['css'] as $css) {
                            $viteAssets[] = 'build/admin/' . $css;
                        }
                    }
                }
            }
        }
    }

    // Ellenőrizzük, hogy a $viteAssets egy tömb-e
    if (!is_array($viteAssets)) {
      //  $viteAssets = [];
    }
$pp = public_path("public/build/admin/admin-manifest.json");
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }} Admin</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @props(['includeTinymce' => false])

        @if ($viteIsActive === true)
            @vite($viteAssets, "build/admin")
        @else
            <!-- Ha nem aktív a Vite, manuálisan betöltjük a fájlokat -->
            @vite(['resources/js/app.js', 'resources/css/app.css'], 'build/admin/')
        @endif

        @if ($includeTinymce ?? false)
            <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
            <link rel="stylesheet" href="{{ asset('assets/prism.js/prism.css') }}" rel="stylesheet" />
            <script type="module" src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
            <script type="module" src="{{ asset('assets/prism.js/prism.js') }}"></script>
        @endif

        <!-- Head scripts -->
        @stack('head_scripts')

        @stack('my-styles')

    </head>
    <body class="html-body bg-gray-900 font-sans antialiased" data-page="@yield('page')">
        <div class="min-h-screen bg-gray-900">
            @include('layouts.navigation')

            <div class="container my-3 max-w-7xl mx-auto pb-1 px-0 pt-1 bg-white min-h-screen">
                <div class="py-0">
                    <!-- Page Heading -->
                    @isset($header)
                        <header class="bg-white shadow">
                            <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset
                </div>

                <!-- Page Content -->
                <main class="pt-6 pb-10">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>