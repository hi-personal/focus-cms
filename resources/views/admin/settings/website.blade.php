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

        <div x-data="{ activeTab: 'tab-website' }" class="w-full mx-auto">
            <!-- Lapfülek -->
            <div class="mb-8 md:mb-10 max-sm:grid max-sm:grid-cols-1 sm:flex sm:justify-center">
                <button
                    @click="activeTab = 'tab-website'"
                    :class="{
                    'max-sm:border-l-2 max-sm:bg-gray-100 sm:border-b-2 border-indigo-500 text-indigo-600': activeTab === 'tab-website',
                    'text-gray-500 hover:text-gray-700': activeTab !== 'tab-website'
                    }"
                    class="px-4 py-2 font-normal text-md"
                >
                    Weboldal
                </button>

                <button
                    @click="activeTab = 'tab-mailers'"
                    :class="{
                    'max-sm:border-l-2 max-sm:bg-gray-100 sm:border-b-2 border-indigo-500 text-indigo-600': activeTab === 'tab-mailers',
                    'text-gray-500 hover:text-gray-700': activeTab !== 'tab-mailers'
                    }"
                    class="px-4 py-2 font-normal text-md"
                >
                    Levél küldő
                </button>

                <button
                    @click="activeTab = 'tab-maintenance'"
                    :class="{
                    'max-sm:border-l-2 max-sm:bg-gray-100 sm:border-b-2 border-indigo-500 text-indigo-600': activeTab === 'tab-maintenance',
                    'text-gray-500 hover:text-gray-700': activeTab !== 'tab-maintenance'
                    }"
                    class="px-4 py-2 font-normal text-md"
                >
                    Karbantartás mód
                </button>
            </div>

            <!-- Tartalom -->
            <div class="mt-6">
                <div x-show="activeTab === 'tab-website'"
                    x-transition:enter="transition ease-out duration-400"
                    x-transition:enter-start="opacity-0 -translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="  duration-10"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-4"
               >
                    <div class="">
                        <form method="post" action="{{ route("admin.settings.website.update") }}">
                            @csrf
                            @method("post")

                            <div class="py-3">
                                <h3>Kezdőlap</h3>
                                <div class="">
                                    <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-home-variant-outline mdi-18"></i> Kezdőlap oldal ID</label>
                                    <select
                                        name="website_setting_start_page_id"
                                        class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option value="0" {{ $website_setting_start_page_id == 0 ? 'selected' : null }}>Válassz!</option>
                                        @if(!empty($pages))
                                            @foreach($pages as $page)
                                                <option value="{{ $page->id }}" {{ $website_setting_start_page_id == $page->id ? 'selected' : null }}>{{ $page->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="py-3">
                                <h3>Publikus regisztráció</h3>
                                <div x-data="{ enabled: {{ $website_setting_public_registration_status ? 'true' : 'false' }} }">
                                    <div class="flex items-center text-lg space-x-6">
                                        <input type="hidden" name="website_setting_public_registration_status" :value="enabled ? 1 : 0">

                                        <i
                                            class="mdi scale-[200%]"
                                            :class="enabled ? 'mdi-account-lock-open-outline text-green-700' : 'mdi-account-lock-outline text-red-700'"
                                        ></i>

                                        <div
                                            @click="enabled = !enabled"
                                            class="w-14 h-7 p-1 rounded border-2 border-gray-400 flex items-center cursor-pointer transition-colors duration-100"
                                            :class="enabled ? 'bg-green-500 border-green-600' : 'bg-red-500 border-red-600'"
                                        >
                                            <div
                                                class="rounded w-5 h-5 bg-white transition-transform duration-100"
                                                :class="enabled ? 'translate-x-6' : 'translate-x-0'"

                                            ></div>

                                        </div>

                                        <span
                                            class="text-lg text-gray-700"
                                            :class="enabled ? 'text-green-700' : 'text-red-700'"
                                            x-text="enabled ? 'Engedélyezve' : 'Tiltva'"
                                        ></span>
                                    </div>

                                </div>
                            </div>

                            <div class="py-3" x-data="{
                                perPage: '{{ in_array($website_setting_posts_per_page, ['10', '20', '40']) ? $website_setting_posts_per_page : 'manual' }}',
                                customValue: '{{ !in_array($website_setting_posts_per_page, ['10', '20', '40']) ? $website_setting_posts_per_page : '' }}'
                            }">
                                <h3>Bejegyzések száma oldalanként</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label for="website_setting_posts_per_page" class="md:col-span-2 block mt-4 mb-1 text-md font-medium text-gray-700">
                                        Bejegyzések száma oldalanként
                                    </label>

                                    <!-- Select elem - mindig küldjük, de csak akkor használjuk ha nem 'manual' -->
                                    <select
                                        id="website_setting_posts_per_page"
                                        name="website_setting_posts_per_page"
                                        x-model="perPage"
                                        class="block w-full py-2 h-[42px] border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option value="10" {{ $website_setting_posts_per_page == '10' ? 'selected' : '' }}>10</option>
                                        <option value="20" {{ $website_setting_posts_per_page == '20' ? 'selected' : '' }}>20</option>
                                        <option value="40" {{ $website_setting_posts_per_page == '40' ? 'selected' : '' }}>40</option>
                                        <option value="manual" {{ !in_array($website_setting_posts_per_page, ['10', '20', '40']) ? 'selected' : '' }}>Egyedi érték</option>
                                    </select>

                                    <!-- Egyedi érték input - csak akkor küldjük ha 'manual' van kiválasztva -->
                                    <div x-show="perPage === 'manual'" x-cloak x-transition class="items-center h-[42px]">
                                        <input
                                            type="number"
                                            id="custom_per_page"
                                            name="custom_perPage"
                                            x-model="customValue"
                                            x-bind:disabled="perPage !== 'manual'"
                                            min="1"
                                            class="w-full py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="Adja meg a kívánt értéket"
                                            required
                                        >
                                        <!-- Rejtett input ami ténylegesen tartalmazza az értéket -->
                                        <input type="hidden" name="website_setting_posts_per_page" x-bind:value="perPage === 'manual' ? customValue : perPage">
                                    </div>
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="mt-10 mb-2 py-2 px-4 bg-blue-600 hover:bg-blue-400 text-white hover:text-black rounded border"
                            >Mentés</button>
                        </form>
                    </div>
                </div>

                <div
                    x-show="activeTab === 'tab-mailers'"
                    x-transition:enter="transition ease-out duration-400"
                    x-transition:enter-start="opacity-0 -translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="  duration-10"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-4"
                    style="display: none;"
                >
                    <div class="">
                        <form method="post" action="{{ route("admin.settings.website.update") }}">
                            @csrf
                            @method("post")

                            <div class="py-3">
                                <h3>SMTP levélküldő szerver</h3>
                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> SMTP URL</label>
                                <input type="text" name="website_setting_smtp_url" value="{{ $website_setting_smtp_url }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> SMTP Host</label>
                                <input type="text" name="website_setting_smtp_host" value="{{ $website_setting_smtp_host }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> SMTP Port</label>
                                <input type="text" name="website_setting_smtp_port" value="{{ $website_setting_smtp_port }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> SMTP Encryption</label>
                                <input type="text" name="website_setting_smtp_encryption" value="{{ $website_setting_smtp_encryption }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> SMTP username</label>
                                <input type="text" name="website_setting_smtp_username" value="{{ $website_setting_smtp_username }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> SMTP Password</label>
                                <div x-data="{ showPassword: false }" class="relative">
                                    <input
                                        :type="showPassword ? 'text' : 'password'"
                                        name="website_setting_smtp_password"
                                        value="{{ $website_setting_smtp_password }}"
                                        class="w-full p-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                                        :aria-label="showPassword ? 'Jelszó elrejtése' : 'Jelszó megjelenítése'"
                                    >
                                        <span x-show="!showPassword" class="mdi mdi-eye"></span>
                                        <span x-show="showPassword" class="mdi mdi-eye-off" style="display: none;"></span>
                                    </button>
                                </div>

                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> </label>
                                <input type="text" name="website_setting_smtp_timeout" value="{{ $website_setting_smtp_timeout }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> SMTP Local Domain</label>
                                <input type="text" name="website_setting_smtp_local_domain" value="{{ $website_setting_smtp_local_domain }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> SMTP Küldő email cím</label>
                                <input type="text" name="website_setting_smtp_from_address" value="{{ $website_setting_smtp_from_address }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> SMTP Küldő név/label>
                                <input type="text" name="website_setting_smtp_from_name" value="{{ $website_setting_smtp_from_name }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div class="py-3">
                                <h3>MAILJET levélküldő szerver</h3>
                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> Mailjet API Key</label>
                                <div x-data="{ showPassword: false }" class="relative">
                                    <input
                                        :type="showPassword ? 'text' : 'password'"
                                        name="website_setting_mailjet_apikey"
                                        value="{{ $website_setting_mailjet_apikey }}"
                                        class="w-full p-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                                        :aria-label="showPassword ? 'Jelszó elrejtése' : 'Jelszó megjelenítése'"
                                    >
                                        <span x-show="!showPassword" class="mdi mdi-eye"></span>
                                        <span x-show="showPassword" class="mdi mdi-eye-off" style="display: none;"></span>
                                    </button>
                                </div>

                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> Mailjet API Secret</label>
                                <div x-data="{ showPassword: false }" class="relative">
                                    <input
                                        :type="showPassword ? 'text' : 'password'"
                                        name="website_setting_mailjet_apisecret"
                                        value="{{ $website_setting_mailjet_apisecret }}"
                                        class="w-full p-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                                        :aria-label="showPassword ? 'Jelszó elrejtése' : 'Jelszó megjelenítése'"
                                    >
                                        <span x-show="!showPassword" class="mdi mdi-eye"></span>
                                        <span x-show="showPassword" class="mdi mdi-eye-off" style="display: none;"></span>
                                    </button>
                                </div>

                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> Mailjet Küldő email cím</label>
                                <input type="text" name="website_setting_mailjet_from_address" value="{{ $website_setting_mailjet_from_address }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

                                <label class="block mt-4 mb-1 text-md font-medium text-gray-700"><i class="mdi mdi-calendar-range"></i> Mailjet Küldő név/label>
                                <input type="text" name="website_setting_mailjet_from_name" value="{{ $website_setting_mailjet_from_name }}" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <button type="submit" class="my-2 py-2 px-4 bg-blue-400 hover:bg-blue-700 hover:text-white">Mentés</button>
                        </form>
                    </div>
                </div>

                <div
                    x-show="activeTab === 'tab-maintenance'"
                    x-transition:enter="transition ease-out duration-400"
                    x-transition:enter-start="opacity-0 -translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="duration-10"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-4"
                    style="display: none;"
                >
                    <div class="">
                        <form method="post" action="{{ route("admin.enableMaintenance") }}">
                            @csrf
                            @method("post")

                            <div class="py-3">
                                @if($maintenanceStatus)
                                    <p class="text-red-600">A karbantartás mód aktív</p>
                                    <button
                                        type="submit"
                                        class="py-3 px-5 text-lg text-black bg-blue-400 hover:bg-blue-500 hover:text-white rounded border"
                                        formaction="{{ route("admin.disableMaintenance") }}"
                                    >Kikapcsolás</button>
                                @else
                                    <p class="text-green-600">A karbantartás mód kikapcsolva</p>
                                    <button
                                        type="submit"
                                        class="py-3 px-5 text-lg text-black bg-blue-400 hover:bg-blue-500 hover:text-white rounded border"
                                    >Bekapcsolás</button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>