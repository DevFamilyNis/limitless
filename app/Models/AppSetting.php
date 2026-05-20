<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppSettingKey;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(AppSettingKey $key, mixed $default = null): mixed
    {
        return static::where('key', $key->value)->value('value') ?? $default;
    }

    public static function setValue(AppSettingKey $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key->value], ['value' => (string) $value]);
    }

    public static function officialSignerUserId(): ?int
    {
        $value = static::getValue(AppSettingKey::OfficialSignerUserId);

        return $value !== null ? (int) $value : null;
    }

    public static function officialSignerUser(): ?User
    {
        $id = static::officialSignerUserId();

        return $id ? User::find($id) : null;
    }

    /**
     * Vraća User koji je official signer, ili abortuje sa 503.
     * Proverava i da ID postoji u settings-ima i da User zaista postoji u bazi.
     */
    public static function resolveOfficialSignerOrFail(): User
    {
        $user = static::officialSignerUser();

        abort_if($user === null, 503, __('messages.errors.official_signer_not_configured'));

        return $user;
    }
}
