<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\Exceptions;

use RuntimeException;

final class WorkSessionNotStartedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No work session has been started for today.');
    }
}
