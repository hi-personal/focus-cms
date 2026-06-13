<x-app-layout>
    <div class="container max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 pt-1 pb-8 md:px-8 bg-white min-h-screen">

        @if(session('success'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 3000)"
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
                <p class="font-bold">{{ session('error') }}</p>

                @if($errors->any())
                    <ul class="list-disc list-inside mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <div
            x-data="{
                activeTab: window.location.hash
                    ? window.location.hash.replace('#', '')
                    : 'tab-website'
            }"
            x-init="
                window.addEventListener('hashchange', () => {
                    activeTab = window.location.hash.replace('#', '') || 'tab-website';
                });
            "
            class="w-full mx-auto"
        >

            <!-- Tabs -->

            <div class="mb-8 md:mb-10 max-sm:grid max-sm:grid-cols-1 sm:flex sm:justify-center">

                <button
                    @click="
                        activeTab = 'tab-website';
                        window.location.hash = 'tab-website';
                    "
                    :class="{
                        'max-sm:border-l-2 max-sm:bg-gray-100 sm:border-b-2 border-indigo-500 text-indigo-600': activeTab === 'tab-website',
                        'text-gray-500 hover:text-gray-700': activeTab !== 'tab-website'
                    }"
                    class="px-4 py-2 font-normal text-md"
                >
                    Weboldal
                </button>

                <button
                    @click="
                        activeTab = 'tab-mailers';
                        window.location.hash = 'tab-mailers';
                    "
                    :class="{
                        'max-sm:border-l-2 max-sm:bg-gray-100 sm:border-b-2 border-indigo-500 text-indigo-600': activeTab === 'tab-mailers',
                        'text-gray-500 hover:text-gray-700': activeTab !== 'tab-mailers'
                    }"
                    class="px-4 py-2 font-normal text-md"
                >
                    Levél küldő
                </button>

                <button
                    @click="
                        activeTab = 'tab-maintenance';
                        window.location.hash = 'tab-maintenance';
                    "
                    :class="{
                        'max-sm:border-l-2 max-sm:bg-gray-100 sm:border-b-2 border-indigo-500 text-indigo-600': activeTab === 'tab-maintenance',
                        'text-gray-500 hover:text-gray-700': activeTab !== 'tab-maintenance'
                    }"
                    class="px-4 py-2 font-normal text-md"
                >
                    Karbantartás mód
                </button>

                <button
                    @click="
                        activeTab = 'tab-cache';
                        window.location.hash = 'tab-cache';
                    "
                    :class="{
                        'max-sm:border-l-2 max-sm:bg-gray-100 sm:border-b-2 border-indigo-500 text-indigo-600': activeTab === 'tab-cache',
                        'text-gray-500 hover:text-gray-700': activeTab !== 'tab-cache'
                    }"
                    class="px-4 py-2 font-normal text-md"
                >
                    Cache
                </button>

            </div>

            <!-- CONTENT -->

            <div class="mt-6">

                <!-- WEBSITE -->

                <div
                    x-show="activeTab === 'tab-website'"
                    x-transition
                >

                    <form method="post" action="{{ route('admin.settings.website.update') }}">

                        @csrf
                        @method('post')

                        <div class="py-3">

                            <h3>Kezdőlap</h3>

                            <label class="block mt-4 mb-1 text-md font-medium text-gray-700">
                                Kezdőlap oldal ID
                            </label>

                            <select
                                name="website_setting_start_page_id"
                                class="w-full p-2 border border-gray-300 rounded-md shadow-sm"
                            >
                                <option value="0">
                                    Válassz!
                                </option>

                                @foreach($pages as $page)

                                    <option
                                        value="{{ $page->id }}"
                                        {{ $website_setting_start_page_id == $page->id ? 'selected' : null }}
                                    >
                                        {{ $page->title }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <button
                            type="submit"
                            class="mt-10 mb-2 py-2 px-4 bg-blue-600 hover:bg-blue-400 text-white hover:text-black rounded border"
                        >
                            Mentés
                        </button>

                    </form>

                </div>

                <!-- MAILERS -->

                <div
                    x-show="activeTab === 'tab-mailers'"
                    x-transition
                    style="display:none;"
                >

                    <div class="py-3">

                        <h3 class="text-xl font-semibold">
                            Levélküldő beállítások
                        </h3>

                        <p class="text-gray-600 mt-3">
                            SMTP és Mailjet konfiguráció.
                        </p>

                    </div>

                </div>

                <!-- MAINTENANCE -->

                <div
                    x-show="activeTab === 'tab-maintenance'"
                    x-transition
                    style="display:none;"
                >

                    <form method="post" action="{{ route('admin.enableMaintenance') }}">

                        @csrf
                        @method('post')

                        <div class="py-3">

                            @if($maintenanceStatus)

                                <p class="text-red-600 mb-4">
                                    A karbantartás mód aktív
                                </p>

                                <button
                                    type="submit"
                                    formaction="{{ route('admin.disableMaintenance') }}"
                                    class="py-3 px-5 text-lg text-black bg-blue-400 hover:bg-blue-500 hover:text-white rounded border"
                                >
                                    Kikapcsolás
                                </button>

                            @else

                                <p class="text-green-600 mb-4">
                                    A karbantartás mód kikapcsolva
                                </p>

                                <button
                                    type="submit"
                                    class="py-3 px-5 text-lg text-black bg-blue-400 hover:bg-blue-500 hover:text-white rounded border"
                                >
                                    Bekapcsolás
                                </button>

                            @endif

                        </div>

                    </form>

                </div>

                <!-- CACHE -->

                <div
                    x-show="activeTab === 'tab-cache'"
                    x-transition
                    style="display:none;"
                >

                    <div class="py-3">

                        <h3 class="text-xl font-semibold mb-4">
                            Oldal cache kezelése
                        </h3>

                        <p class="text-gray-600 mb-6">
                            A teljes statikus oldal cache törlése és előgenerálása.
                        </p>

                        <!-- CLEAR CACHE -->

                        <form
                            method="post"
                            action="{{ route('admin.cache.clear') }}"
                        >

                            @csrf
                            @method('post')

                            <button
                                type="submit"
                                class="py-3 px-5 text-lg text-black bg-red-400 hover:bg-red-500 hover:text-white rounded border"
                            >
                                Teljes cache törlése
                            </button>

                        </form>

                        <!-- CACHE WARMUP -->

                        <div
                            x-data="cacheWarmup()"
                            class="mt-10 border-t border-gray-200 pt-8"
                        >

                            <h3 class="text-xl font-semibold mb-4">
                                Cache előgenerálás
                            </h3>

                            <p class="text-gray-600 mb-6">
                                Az összes publikus oldal cache automatikus előgenerálása.
                            </p>

                            <div class="flex flex-wrap gap-4 items-center">

                                <button
                                    type="button"
                                    @click="start()"
                                    :disabled="running"
                                    class="py-3 px-5 text-lg text-black bg-blue-400 hover:bg-blue-500 hover:text-white rounded border disabled:opacity-50"
                                >
                                    Cache generálás indítása
                                </button>

                                <div
                                    x-show="running"
                                    class="text-sm text-gray-700"
                                >
                                    Folyamatban...
                                </div>

                            </div>

                            <div
                                x-show="started"
                                class="mt-6"
                            >

                                <div class="w-full bg-gray-200 rounded h-6 overflow-hidden">

                                    <div
                                        class="bg-green-500 h-6 transition-all duration-300"
                                        :style="`width:${percent}%`"
                                    ></div>

                                </div>

                                <div class="mt-3 text-sm text-gray-700">

                                    <strong x-text="done"></strong>
                                    /
                                    <strong x-text="total"></strong>

                                    oldal kész

                                    (
                                    <span x-text="percent"></span>%
                                    )

                                </div>

                                <div class="mt-3 text-xs break-all text-gray-500">

                                    <span x-text="current"></span>

                                </div>

                                <div
                                    x-show="finished"
                                    class="mt-4 text-green-700 font-semibold"
                                >
                                    Cache generálás befejezve.
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script>
    function cacheWarmup()
    {
        return {

            started: false,
            running: false,
            finished: false,

            index: 0,
            done: 0,
            total: 0,
            percent: 0,

            current: '',

            async start()
            {
                this.started = true;
                this.running = true;
                this.finished = false;

                this.index = 0;
                this.done = 0;
                this.percent = 0;

                await this.processNext();
            },

            async processNext()
            {
                try {

                    const response = await fetch(
                        '{{ route('admin.cache.warmup') }}',
                        {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                index: this.index
                            })
                        }
                    );

                    const data = await response.json();

                    this.current = data.current || '';
                    this.done = data.done || 0;
                    this.total = data.total || 0;
                    this.percent = data.percent || 0;

                    if (data.finished) {

                        this.running = false;
                        this.finished = true;

                        return;

                    }

                    this.index++;

                    await this.processNext();

                } catch (e) {

                    console.error(e);

                    this.running = false;

                }
            }
        }
    }
    </script>

</x-app-layout>