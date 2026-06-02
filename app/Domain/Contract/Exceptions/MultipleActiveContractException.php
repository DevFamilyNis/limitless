<?php

declare(strict_types=1);

namespace App\Domain\Contract\Exceptions;

use RuntimeException;

final class MultipleActiveContractException extends RuntimeException
{
    public static function forClient(): self
    {
        return new self('Ovaj klijent već ima aktivan ugovor.');
    }
}
