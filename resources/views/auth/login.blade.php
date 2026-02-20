<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" id="login-form">
        @csrf

        <input type="hidden" name="recaptcha_token" id="recaptcha_token">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            @isset($passwordInput)
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $email ?? null)" readonly />
            @else
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $email ?? null)" required autofocus autocomplete="username" />
            @endisset
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        @isset($passwordInput)
            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <div x-data="{ showPassword: false }" class="relative">
                <input
                    id="password"
                    class="block mt-1 w-full pr-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :type="showPassword ? 'text' : 'password'"
                    name="password"
                    required
                    autofocus
                    autocomplete="current-password"
                />

                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                    :class="{ 'text-gray-600': showPassword }"
                >
                    <span class="material-icons-outlined text-base">
                        <span class="mdi mdi-eye-off md-36" x-show="!showPassword"></span>
                        <span class="mdi mdi-eye-outline md-36" x-show="showPassword"></span>
                    </span>
                </button>
            </div>

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
        @else
            <input type="hidden" name="password" value="hidden">
        @endisset

        @if(isset($auth2FaStatus) && $auth2FaStatus == true)
            <!-- Verify -->
            <div class="mt-4">
                <x-input-label for="verify" :value="__('Megerősító kód')" />

                <x-text-input id="password" class="block mt-1 w-full"
                    type="text"
                    name="verify"
                    required autocomplete="" />
            </div>
        @endif

        @isset($passwordInput)
            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>
        @endisset

        @if(config('cloudflare.enabled') || !in_array(config('app.env'), config('cloudflare.skip_env')))
            <div
                class="my-2 cf-turnstile"
                data-sitekey="{{ config('cloudflare.site_key') }}"
                data-callback="javascriptCallback"
            ></div>
        @endif

        <div class="flex items-center justify-end mt-4">
            @isset($passwordInput)
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            @endisset

            <button
                id="login-submit"
                type="submit"
                class="ms-3 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 g-recaptcha disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                <i class="mr-1 mdi mdi-login"></i> Belépés
            </button>
        </div>
    </form>

    @push('head_scripts')
        @if(config('cloudflare.enabled') && !in_array(config('app.env'), config('cloudflare.skip_env')))
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
        @endif
    @endpush

    @push('scripts')
        @if(config('recaptcha.enabled') && !in_array(config('app.env'), config('recaptcha.skip_env')))
            <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}"></script>
            <script>
                grecaptcha.ready(() => {
                    // reCAPTCHA script betöltődött és használatra kész
                    const loginButton = document.querySelector('#login-submit');
                    if (loginButton) {
                        loginButton.disabled = false;
                    }
                });

                document.getElementById('login-form').addEventListener('submit', function(e) {
                    const tokenField = document.getElementById('recaptcha_token');

                    e.preventDefault();
                    grecaptcha.ready(() => {
                        grecaptcha.execute('{{ config("recaptcha.site_key") }}', {
                            action: 'login'
                        }).then(token => {
                            tokenField.value = token;
                            document.getElementById('login-form').submit();
                        });
                    });
                });
            </script>
        @endif

        @if(config('cloudflare.enabled') || !in_array(config('app.env'), config('cloudflare.skip_env')))
            <script>
                function javascriptCallback(token) {
                    document.querySelector('#login-submit').disabled = false;
                }
            </script>
        @endif
    @endpush

</x-guest-layout>
