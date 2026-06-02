<?php

declare(strict_types=1);

namespace App\Domain\Contract\Enums;

enum ContractStatus: string
{
    case Aktivan = 'Aktivan';
    case Neaktivan = 'Neaktivan';
}
