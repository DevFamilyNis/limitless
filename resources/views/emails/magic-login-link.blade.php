<x-mail::message>
<div style="text-align: center; margin-bottom: 18px;">
    <img src="{{ asset('app_logo.png') }}" alt="Limitless logo" width="84" style="display: block; margin: 0 auto 14px;">
    <h1 style="margin: 0; font-size: 26px; line-height: 1.25; font-weight: 700; color: #111827;">Prijava na aplikaciju</h1>
</div>

Klikni na dugme ispod da se prijaviš bez lozinke. Link važi 10 minuta i može da se iskoristi samo jednom.

<x-mail::button :url="$url">
Prijavi se
</x-mail::button>

Ako nisi ti zatražio prijavu, slobodno ignoriši ovaj email.

Pozdrav,<br>
Dev-Family
</x-mail::message>
