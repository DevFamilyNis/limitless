<?php

declare(strict_types=1);

namespace App\Infrastructure\Qr;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

final class QrCodeGenerator
{
    public function generate(string $payload): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,

            // bolje za štampu
            'eccLevel' => EccLevel::M,

            // veličina QR
            'scale' => 6,

            // margin (quiet zone)
            'addQuietzone' => true,
            'quietzoneSize' => 4,

            'imageBase64' => false,
        ]);

        $png = (new QRCode($options))->render($payload);

        return 'data:image/png;base64,'.base64_encode((string) $png);
    }
}
