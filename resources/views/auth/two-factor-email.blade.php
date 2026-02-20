<x-mail::message>
# Kétfaktoros hitelesítés aktiválás

<br>

Az Ön hitelesítési kódja: **{{ $token }}**

<br>

@isset($url)
<x-mail::button :url="$url">
    Hitelesítés most
</x-mail::button>
@endisset

Köszönjük!<br>
{{ config('app.name') }}
</x-mail::message>