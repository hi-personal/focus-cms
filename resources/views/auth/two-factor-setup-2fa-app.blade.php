<x-app-layout>
    <div class="max-w-md mx-auto mt-10">
        <h2 class="text-2xl font-bold mb-4">2FA Beállítás</h2>

        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">{{ session('status') }}</div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if(session('errors'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
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

        @if($status == false)
            @if(!session('success'))
                @isset($qrCode)
                    <div class="mb-4">
                        <p class="mb-2">Olvasd be az alábbi QR kódot az Authenticator app segítségével:</p>
                        <div class="border p-4 bg-white rounded shadow">
                            <img src="{{ $qrCode }}" alt="QR Kód" class="mx-auto" />
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-1 font-semibold" for="otp">Beállító kulcs</label>
                        <input id="secret" name="secret" type="text" value="{{ $secret }}" class="w-full border p-2 rounded" readonly>
                    </div>

                    <form method="POST" action="{{ route('2fa.setup.store', ['mode' => '2FaApp']) }}">
                        @csrf
                        @method('post')
                        <div class="mb-4">
                            <label class="block mb-1 font-semibold" for="otp">Hitelesítő kód</label>
                            <input id="otp" name="otp" type="text" class="w-full border p-2 rounded" required>
                        </div>
                        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                            Aktiválás
                        </button>
                    </form>
                @endisset
            @else
                <div class="mb-4">
                    <p class="">
                        <a
                            href="{{ route('profile.edit') }}"
                            target="_self"
                            class="py-1 px-2 bg-gray-200 hover:bg-blue-600 hover:text-white border rounded"
                        ><i class="mdi mdi-arrow-left"></i> Vissza</a>
                    </p>
                </div>
            @endif
        @else
            @isset($recoveryKey)
                <div class="mb-4">
                    <p class="my-4">
                        Figyelem!<br><br>
                        Az alábbi helyreállítási kulcscsal lehet a kétfaktoros hitelesítést inaktiválni, ha az applikáció nem elérhető.<br>
                        Ezért ezt mentsd el jó helyre!<br>
                        Ennek hiányában csak az adminok egyike, vagy rendszergazda tudja inaktiválni a szolgáltatást!<br><br>
                        <i>
                            Ez a kulcs, csak most (egyszer) jelenik meg, nem kerül tárolásra a szerveren!<br>
                            Amennyiben elveszik, aktiváld újra a kétfaktoros hitelesítést!
                        </i>
                    </p>
                    <label class="block mb-1 font-semibold" for="otp">Helyreállítási kulcs</label>
                    <input id="recoveryKey" name="recoveryKey" type="text" value="{{ $recoveryKey }}" class="w-full border p-2 rounded" readonly>
                </div>
                <div class="mb-4">
                    <p class="">
                        <a
                            href="{{ route('profile.edit') }}"
                            target="_self"
                            class="py-1 px-2 bg-gray-200 hover:bg-blue-600 hover:text-white border rounded"
                        ><i class="mdi mdi-arrow-left"></i> Vissza</a>
                    </p>
                </div>
            @endisset
        @endif
    </div>
</x-app-layout>
