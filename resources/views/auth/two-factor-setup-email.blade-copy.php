<x-app-layout>
    <div class="max-w-md mx-auto mt-10">
        <h2 class="text-2xl font-bold mb-4">2FA Beállítás</h2>

        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">{{ session('status') }}</div>
        @endif

        @if($status == false)
            @if($mode == 'email')
                <p class="mb-2">Megerősítő email elküldve a(z) {{ $mail_address ?? null }} címre. Ellenőrizd a postafiókod.
            @endif

            @if($mode == '2fa_app')
                @isset($qrCode)
                    <div class="mb-4">
                        <p class="mb-2">Olvasd be az alábbi QR kódot az Authenticator app segítségével:</p>
                        <div class="border p-4 bg-white rounded shadow">
                            <img src="{{ $qrCode }}" alt="QR Kód" class="mx-auto" />
                        </div>
                    </div>

                    <form method="POST" action="{{ url('/two-factor-auth-setup') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block mb-1 font-semibold" for="otp">Hitelesítő kód</label>
                            <input id="otp" name="otp" type="text" class="w-full border p-2 rounded" required>
                        </div>
                        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                            Aktiválás
                        </button>
                    </form>
                @endisset
            @endif
        @else
            @if($mode == 'email')
                <p class="mb-6"><i class="text-green-600 mdi mdi-check-bold"></i> Email cím megerősítve, az email-es 2FA belépési mód aktiválva.
                <p class="">
                    <a
                        href="{{ route('profile.edit') }}"
                        target="_self"
                        class="py-1 px-2 bg-gray-200 hover:bg-blue-600 hover:text-white border rounded"
                    ><i class="mdi mdi-arrow-left"></i> Vissza</a>
                </p>
            @endif


        @endif

    </div>
</x-app-layout>
