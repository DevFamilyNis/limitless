<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadComment extends Model
{
    /** @use HasFactory<\Database\Factories\LeadCommentFactory> */
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'author_id',
        'lead_status_id',
        'event_type',
        'contact_method',
        'outcome',
        'body',
        'contacted_at',
        'responded_at',
        'next_follow_up_at',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'responded_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }
}
