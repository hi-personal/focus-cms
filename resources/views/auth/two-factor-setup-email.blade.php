<x-app-layout>
    <div class="max-w-md mx-auto mt-10">
        <h2 class="text-2xl font-bold mb-4">2FA Beállítás</h2>

        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">{{ session('status') }}</div>
        @endif

        @if($status == false)
            <p class="mb-2">Megerősítő email elküldve a(z) {{ $mail_address ?? null }} címre. Ellenőrizd a postafiókod.
        @else
            <p class="mb-6"><i class="text-green-600 mdi mdi-check-bold"></i> Email cím megerősítve, az email-es 2FA belépési mód aktiválva.
            <p class="">
                <a
                    href="{{ route('profile.edit') }}"
                    target="_self"
                    class="py-1 px-2 bg-gray-200 hover:bg-blue-600 hover:text-white border rounded"
                ><i class="mdi mdi-arrow-left"></i> Vissza</a>
            </p>
        @endif
    </div>
</x-app-layout>
