<?php

declare(strict_types=1);

namespace App\Support;

final class IpsQrPayload
{
    public static function make(array $data): string
    {
        $payload = self::sanitize($data);

        // Nema hard-fail validacije ovde.
        // Eligibility će raditi Action (graceful fallback).

        return self::build($payload);
    }

    protected static function sanitize(array $d): array
    {
        return [
            'racun' => preg_replace('/\D/', '', $d['racun'] ?? ''),
            'primalac' => self::cleanText($d['primalac'] ?? '', 70),
            'iznos' => self::cleanAmount($d['iznos'] ?? ''),
            'platilac' => self::cleanText($d['platilac'] ?? '', 70),
            'sifra' => (int) ($d['sifra'] ?? 221), // default po tvojoj praksi
            'svrha' => self::cleanText($d['svrha'] ?? '', 35),
        ];
    }

    /**
     * Payload u jednom redu, polja odvojena sa "|"
     */
    protected static function build(array $p): string
    {
        return 'K:PR'
            .'|V:01'
            .'|C:1'
            .'|R:'.$p['racun']
            .'|N:'.$p['primalac']
            .'|I:RSD'.$p['iznos']
            .'|P:'.$p['platilac']
            .'|SF:'.$p['sifra']
            .'|S:'.$p['svrha'];
    }

    protected static function cleanText(string $value, int $limit): string
    {
        $value = trim($value);

        // zadrži kvačice + osnovnu interpunkciju
        $value = preg_replace('/[^A-Za-z0-9čćšđžČĆŠĐŽ .,\-\/\r\n]/u', '', $value) ?? '';

        return mb_substr($value, 0, $limit);
    }

    protected static function cleanAmount(string $amount): string
    {
        $amount = trim($amount);
        $amount = str_replace('.', ',', $amount);

        // ako nije validan format, vrati "0,00" (Action će to blokirati)
        if (! preg_match('/^\d+,\d{2}$/', $amount)) {
            return '0,00';
        }

        return $amount;
    }
}
