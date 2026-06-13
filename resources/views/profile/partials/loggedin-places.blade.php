<h2 class="text-lg font-medium text-gray-900">Bejelentkezett helyek, eszközök</h2>
<div class="w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($userSessions as $session)
        @php
            $agent = userAgent($session->user_agent);
        @endphp
        <div class="w-full p-4 border rounded text-sm">
            <p class="text-base">
                <span>Ip-cím: {{ $session->ip_address }}</span><br>
                <span>Böngésző: {{ $agent->browser() }}</span><br>
                <span>Böngésző verzió: {{ $agent->browserVersion() }}</span><br>
                <span>Eszköz: {{ __($agent->deviceType()) }}</span><br>

                @if($session->last_activity)
                    <span>Utolsó aktivitás: {{ $session->last_activity->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i:s') }}</span><br>
                @else
                    <span>Nincs utolsó aktivitás érték</span><br>
                @endif
                @if($session->login_valid_date_time)
                    <span>Session érényesség: {{ $session->login_valid_date_time->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i:s') }}</span><br>
                @else
                    <span>Nincs érvényességi dátumidő</span><br>
                @endif
            </p>
            <p class="text-base">
                @if($session->id !== session()->getId())
                    <form method="POST" action="{{ route('sessions.destroy', $session->id) }}">
                        @csrf
                        @method('POST')
                        <button
                            class="my-2 py-2 px-4 bg-gray-300 hover:bg-blue-500 hover:text-white"
                            type="submit"
                        >Kijelentkezés erről az eszközről</button>
                    </form>
                @else
                    <p class="text-base text-green-600">(Jelenlegi eszköz)</p>
                @endif
            </p>
        </div>
    @empty
        <p>Nincsenek bejelentkezett helyek</p>
    @endforelse
</div>