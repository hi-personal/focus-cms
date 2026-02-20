<x-app-layout :includeTinymce="true">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="p-4 shadow-sm sm:rounded-lg">
            <div class="p-0 text-gray-900">
                {{ __("You're logged in!") }}
            </div>
            <br><br><br>
            <div id="twteszt">TW TESZT</div>
            <!-- jQuery teszt -->
            <button id="test-button">Kattints ide (jQuery)</button>

            <!-- Alpine.js teszt -->
            <div x-data="dropdown">
                <button @click="toggle">Toggle (Alpine.js)</button>
                <div x-show="open">Hello Alpine.js!</div>
            </div>
        </div>
    </div>
</x-app-layout>
