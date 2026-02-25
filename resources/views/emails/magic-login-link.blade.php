<x-mail::message>
<p align="center">
    <img src="{{ asset('app_logo.png') }}" alt="Limitless logo" width="160">
</p>

# Prijava na aplikaciju

Klikni na dugme ispod da se prijaviš bez lozinke. Link važi 10 minuta i može da se iskoristi samo jednom.

<x-mail::button :url="$url">
Prijavi se
</x-mail::button>

Ako nisi ti zatražio prijavu, slobodno ignoriši ovaj email.

Pozdrav,<br>
Dev-Family
</x-mail::message>
