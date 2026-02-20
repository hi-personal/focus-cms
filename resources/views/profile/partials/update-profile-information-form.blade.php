<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="login" :value="__('Login')" />
            <x-text-input id="login" name="login" type="text" class="mt-1 block w-full" :value="old('login', $user->login)" required autofocus autocomplete="login" />
            <x-input-error class="mt-2" :messages="$errors->get('login')" />
        </div>

        <div>
            <x-input-label for="nicename" :value="__('Nicename')" />
            <x-text-input id="nicename" name="nicename" type="text" class="mt-1 block w-full" :value="old('nicename', $user->nicename)" required autofocus autocomplete="nicename" />
            <x-input-error class="mt-2" :messages="$errors->get('nicename')" />
        </div>

        <div>
            <x-input-label for="display_name" :value="__('Display name')" />
            <x-text-input id="display_name" name="display_name" type="text" class="mt-1 block w-full" :value="old('display_name', $user->display_name)" autofocus autocomplete="display_name" />
            <x-input-error class="mt-2" :messages="$errors->get('display_name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Meta adatok -->
        <div>
            <x-input-label for="bio" :value="__('Bio')" />
            <textarea
                id="bio"
                name="bio"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                rows="4"
                maxlength="1000"
            >{{ old('bio', $metas['bio'] ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div>
            <x-input-label for="website" :value="__('Website')" />
            <x-text-input
                id="website"
                name="website"
                type="url"
                class="mt-1 block w-full"
                :value="old('website', $metas['website'] ?? '')"
                placeholder="https://example.com"
            />
            <x-input-error class="mt-2" :messages="$errors->get('website')" />
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone')" />
            <x-text-input
                id="phone"
                name="phone"
                type="tel"
                class="mt-1 block w-full"
                :value="old('phone', $metas['phone'] ?? '')"
                placeholder="+36 123 4567"
                maxlength="20"
            />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="auth_2fa_mode" :value="__('Auth 2FA Mode')" />
            @if($metas['auth_2fa_status'])
            <input
                    type="text"
                    class="mt-1 mb-5 block w-full border rounded"
                    name="_auth_2fa_mode"
                    value="{{ $metas['auth_2fa_mode'] }}"
                    readonly
                >
            @else
            <select
                    id="auth_2fa_mode"
                    name="auth_2fa_mode"
                    class="mt-1 mb-5 block w-full border rounded"
                >
                    <option value="email" {{ $metas['auth_2fa_mode'] == "email" ? "selected" : null }} default>Email</option>
                    <option value="2fa_app" {{ $metas['auth_2fa_mode'] == "2fa_app" ? "selected" : null }}>Authenticator</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />

            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    <div class="my-4 p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <div class="max-w-xl">
            <header>
                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Two-Factor Authentication') }}
                </h2>
            </header>

            @if($metas['auth_2fa_status'] == "1")
                <div class="mt-6">
                    <p>A 2 faktoros hitelesítés jelenleg aktív.</p>
                    <form method="post" action="{{ route('2fa.disable') }}" class="mt-2 space-y-6">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <x-primary-button type="submit" variant="danger">
                            {{ __('Disable Two-Factor Authentication') }}
                        </x-primary-button>
                    </form>
                </div>
            @else
                @if($metas['auth_2fa_mode'] == "email")
                    <a
                        href="{{ route('2fa.setup', ['mode' => 'email']) }}" target="_self"
                        class="my-2 py-2 px-3 block-inline bg-gray-300 hover:bg-blue-500 hover :text-white border rounded"
                    >
                        {{ __('Enable Email 2FA') }}
                    </a>
                @endif

                @if($metas['auth_2fa_mode'] == "2fa_app")
                    <a
                        href="{{ route('2fa.setup', ['mode' => '2FaApp']) }}" target="_self"
                        class="my-2 py-2 px-3 block-inline bg-gray-300 hover:bg-blue-500 hover :text-white border rounded"
                    >
                        {{ __('Enable Two-Factor Authentication App') }}
                    </a>
                @endif
            @endif
        </div>
    </div>
</section>