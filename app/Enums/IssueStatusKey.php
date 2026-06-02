<?php

declare(strict_types=1);

namespace App\Enums;

enum IssueStatusKey: string
{
    case Backlog = 'backlog';
    case Todo = 'todo';
    case Doing = 'doing';
    case Done = 'done';
}
