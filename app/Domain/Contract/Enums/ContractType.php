<?php

declare(strict_types=1);

namespace App\Domain\Contract\Enums;

enum ContractType: string
{
    case Ugovor = 'Ugovor';
    case Aneks = 'Aneks';
}
