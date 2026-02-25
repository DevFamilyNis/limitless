<x-mail::message>
<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('app_logo.png') }}" alt="Limitless logo" width="160" style="display: inline-block;">
    <h1 style="margin: 16px 0 0; font-size: 40px; line-height: 1.2; font-weight: 700; color: #111827;">Prijava na aplikaciju</h1>
</div>

Klikni na dugme ispod da se prijaviš bez lozinke. Link važi 10 minuta i može da se iskoristi samo jednom.

<x-mail::button :url="$url">
Prijavi se
</x-mail::button>

Ako nisi ti zatražio prijavu, slobodno ignoriši ovaj email.

Pozdrav,<br>
Dev-Family
</x-mail::message>
