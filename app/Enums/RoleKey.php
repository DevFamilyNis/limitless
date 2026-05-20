<?php

declare(strict_types=1);

namespace App\Enums;

enum RoleKey: string
{
    case SuperAdmin = 'super-admin';
    case User = 'user';
}
