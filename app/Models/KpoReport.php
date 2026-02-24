<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class KpoReport extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\KpoReportFactory> */
    use HasFactory;

    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'period_from',
        'period_to',
        'services_total',
        'products_total',
        'activity_total',
        'currency',
        'locked_at',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'services_total' => 'decimal:2',
        'products_total' => 'decimal:2',
        'activity_total' => 'decimal:2',
        'locked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(KpoReportRow::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('pdf')->singleFile();
    }
}
