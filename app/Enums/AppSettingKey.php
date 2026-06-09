<?php

declare(strict_types=1);

namespace App\Enums;

enum AppSettingKey: string
{
    case OfficialSignerUserId = 'official_signer_user_id';
    case WorkSessionReminderDelayMinutes = 'work_session_reminder_delay_minutes';
}
