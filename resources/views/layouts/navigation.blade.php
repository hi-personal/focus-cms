<nav x-data="{ isOpenAdminPrimResponsiveNav: false, isisOpenAdminPrimResponsiveNavAdminPrimNav: false }" class="">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl bg-gray-50 border-b border-gray-100 mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                 <div class="flex mx-2 -my-pxs">
                    <a
                        href="{{ url('') }}"
                        class="inline-flex items-center px-3 py-2 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                        target="_blank"
                    ><i class="mdi mdi-home md-24"></i></a>
                </div>
                <div class="hidden space-x-8 sm:-my-px sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="inline-flex items-center">
                    <a
                        href="#"
                        class="inline-flex items-center px-3 py-2 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                        x-on:click="isisOpenAdminPrimResponsiveNavAdminPrimNav = !isisOpenAdminPrimResponsiveNavAdminPrimNav"
                        :class="{ 'bg-gray-100': isisOpenAdminPrimResponsiveNavAdminPrimNav }"
                    >
                    <span class="mr-2 max-md:hidden">Menü</span> <i class="mdi mdi-menu md-24 md:hidden"></i>
                    </a>
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                    <div class="inline-flex items-center">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-transparent hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                        </div>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="mr-1 mdi mdi-account"></i>{{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                    this.closest('form').submit();"
                            >
                                <i class="mr-1 mdi mdi-logout"></i>{{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <a
                    href="#"
                    class="inline-flex items-center px-3 py-2 mr-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                    x-on:click="isisOpenAdminPrimResponsiveNavAdminPrimNav = !isisOpenAdminPrimResponsiveNavAdminPrimNav"
                    :class="{ 'bg-gray-100': isisOpenAdminPrimResponsiveNavAdminPrimNav }"
                >
                    <i class="mdi mdi-menu md-24"></i>
                </a>
                <button @click="isOpenAdminPrimResponsiveNav = ! isOpenAdminPrimResponsiveNav" class="inline-flex items-center px-3 py-2 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                    <i class="mdi mdi-account-outline md-24"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': isOpenAdminPrimResponsiveNav, 'hidden': ! isOpenAdminPrimResponsiveNav}" class="hidden relative sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>

    <div
        id="dropdown-menu"
        x-show="isisOpenAdminPrimResponsiveNavAdminPrimNav"
        x-cloak
        @click.away="isisOpenAdminPrimResponsiveNavAdminPrimNav = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 rigleft-0 min-w-64 max-w-4xl bg-gray-200 rounded-r-md shadow-lg overflow-y-auto z-40"
    >
        <!-- Menü fejléc -->
        <div class="py-4 px-8 border-b border-gray-300">
            <div class="flex w-full items-center justify-end text-gray-800">
                <div class="flex items-center">
                    <span class="text-sm">Tartalom</span>
                </div>
                <button type="button" @click="isisOpenAdminPrimResponsiveNavAdminPrimNav = false" class="hover:text-gray-900">
                    <i class="mdi mdi-menu-close mdi-flip-h md-24 ml-1"></i>
                </button>
            </div>
        </div>

        <!-- OffCanvas menü -->
        <div class="max-w-7xl text-black mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-4">
                <!-- Csoport 1 -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase"># {{ __('Tartalmak') }}</h3>
                    <div class="flex flex-col space-y-2"> <!-- Függőleges elrendezés -->
                        <a href="{{ route('taxonomies.index', ['taxonomy_name'=>'categories']) }}" class="text-black hover:text-blue-900">
                            <i class="mr-1 mdi mdi-format-list-bulleted"></i>{{ __('Categories') }}
                        </a>
                        <a href="{{ route('taxonomies.index', ['taxonomy_name'=>'tags']) }}" class="text-black hover:text-blue-900">
                            <i class="mr-1 mdi mdi-format-list-bulleted"></i>{{ __('Tags') }}
                        </a>
                        <a href="{{ route('posts.index', ['post_type'=>'post']) }}" class="text-black hover:text-blue-900">
                            <i class="mr-1 mdi mdi-format-list-bulleted"></i>{{ __('Posts') }}
                        </a>
                        <a href="{{ route('posts.index', ['post_type'=>'page']) }}" class="text-black hover:text-blue-900">
                            <i class="mr-1 mdi mdi-format-list-bulleted"></i>{{ __('Pages') }}
                        </a>
                        <a href="{{ route('posts.index', ['post_type'=>'image_container']) }}" class="text-black hover:text-blue-900">
                            <i class="mr-1 mdi mdi-image-multiple-outline"></i>{{ __('Containers') }}
                        </a>
                        <a href="{{ route('posts.index', ['post_type'=>'sidebar_widget']) }}" class="text-gray-700 hover:text-gray-900">
                            <i class="mr-1 mdi mdi-widgets-outline"></i>{{ __('Widgets') }}
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase"># {{ __('Settings') }}</h3>
                    <div class="flex flex-col space-y-2"> <!-- Függőleges elrendezés -->
                        <a href="{{ route('admin.settings.sidebars') }}" class="text-gray-700 hover:text-gray-900">
                            <i class="mr-1 mdi mdi-widgets-outline"></i>{{ __('Sidebars') }}
                        </a>
                        <a href="{{ route('admin.settings.website') }}" class="text-gray-700 hover:text-gray-900">
                            <i class="mr-1 mdi mdi-cog"></i>{{ __('Weboldal') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

