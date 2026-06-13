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

        @php
            $viteAssets = [
                'resources/css/app.css',
                'resources/css/style.css',
                'resources/js/app.js',
            ];

            if ($includeTinymce ?? false) {
                $viteAssets[] = 'resources/js/uppy.js';
            }
        @endphp

        @vite($viteAssets)

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
