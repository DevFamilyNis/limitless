<?php

declare(strict_types=1);

namespace App\Domain\Kpo\Exceptions;

use RuntimeException;

final class LockedKpoReportException extends RuntimeException
{
    public static function forPeriod(int $year, int $month): self
    {
        return new self(sprintf('KPO izveštaj za %02d/%d je zaključan i ne može biti izmenjen.', $month, $year));
    }
}
