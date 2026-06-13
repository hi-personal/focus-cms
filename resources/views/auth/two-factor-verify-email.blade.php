<x-app-layout>
    <div class="max-w-md mx-auto mt-10">
        <h2 class="text-2xl font-bold mb-4">2FA Ellenőrzés</h2>

        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ url('/two-factor-auth-verify') }}">
            @csrf
            <div class="mb-4">
                <label class="block mb-1 font-semibold" for="otp">Hitelesítő kód</label>
                <input id="otp" name="otp" type="text" class="w-full border p-2 rounded" required>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                Ellenőrzés
            </button>
        </form>
    </div>
</x-app-layout>
