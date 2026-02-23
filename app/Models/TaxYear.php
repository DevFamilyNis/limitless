<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxYear extends Model
{
    /** @use HasFactory<\Database\Factories\TaxYearFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'first_threshold_amount',
        'second_threshold_amount',
    ];

    protected $casts = [
        'first_threshold_amount' => 'decimal:2',
        'second_threshold_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
