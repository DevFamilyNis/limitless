<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Contract\Enums\ContractStatus;
use App\Domain\Contract\Enums\ContractType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Contract extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ContractFactory> */
    use HasFactory;

    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'client_id',
        'parent_id',
        'type',
        'status',
        'start_date',
        'end_date',
        'note',
    ];

    protected $casts = [
        'type' => ContractType::class,
        'status' => ContractStatus::class,
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function parentContract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'parent_id');
    }

    public function annexes(): HasMany
    {
        return $this->hasMany(Contract::class, 'parent_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('pdf')->singleFile();
    }
}
