<x-mail::message>
# Sikeres bejelentkezés történt

<br>

Emal: **{{ $user->email}}**

<br>

Időpont: **{{ $now }}**

IP-cím: **{{ $ip }}**

Böngésző: **{{ userAgent()->browser() }} - {{ userAgent()->browserVersion() }}**

Platform: **{{ userAgent()->platform() }}**

Eszköz: **{{ userAgent()->device() }} - {{ userAgent()->deviceType() }}**

<br>

{{ config('app.name') }}
</x-mail::message>