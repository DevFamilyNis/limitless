<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory;

    protected $fillable = [
        'lead_status_id',
        'company_name',
        'email',
        'phone',
        'last_contacted_at',
        'last_contact_method',
        'last_response_at',
        'converted_at',
    ];

    protected $casts = [
        'last_contacted_at' => 'datetime',
        'last_response_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(LeadComment::class)->latest('contacted_at')->latest('id');
    }
}
