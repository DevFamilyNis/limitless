<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProjectRate extends Model
{
    /** @use HasFactory<\Database\Factories\ClientProjectRateFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'project_id',
        'billing_period_id',
        'price_amount',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'price_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function billingPeriod(): BelongsTo
    {
        return $this->belongsTo(BillingPeriod::class);
    }
}
