<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\Exceptions;

use RuntimeException;

final class WorkSessionAlreadyStartedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Work session for today has already been started.');
    }
}
