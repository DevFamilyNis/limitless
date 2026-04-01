<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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
        'next_follow_up_at',
        'converted_at',
    ];

    protected $casts = [
        'last_contacted_at' => 'datetime',
        'last_response_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
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

    public function determineNextFollowUpAt(): ?CarbonInterface
    {
        if ($this->relationLoaded('comments')) {
            return $this->determineNextFollowUpAtFromCollection($this->comments);
        }

        $future = $this->comments()
            ->whereNotNull('next_follow_up_at')
            ->where('next_follow_up_at', '>=', now())
            ->orderBy('next_follow_up_at')
            ->value('next_follow_up_at');

        if ($future !== null) {
            return Carbon::parse($future);
        }

        $past = $this->comments()
            ->whereNotNull('next_follow_up_at')
            ->where('next_follow_up_at', '<', now())
            ->orderByDesc('next_follow_up_at')
            ->value('next_follow_up_at');

        return $past !== null ? Carbon::parse($past) : null;
    }

    public function getCurrentNextFollowUpAtAttribute(): ?CarbonInterface
    {
        return $this->determineNextFollowUpAt();
    }

    /**
     * @param  Collection<int, LeadComment>  $comments
     */
    private function determineNextFollowUpAtFromCollection(Collection $comments): ?CarbonInterface
    {
        $sortedComments = $comments
            ->filter(fn (LeadComment $comment): bool => $comment->next_follow_up_at !== null)
            ->sortBy('next_follow_up_at')
            ->values();

        /** @var LeadComment|null $futureComment */
        $futureComment = $sortedComments->first(
            fn (LeadComment $comment): bool => $comment->next_follow_up_at !== null && $comment->next_follow_up_at->greaterThanOrEqualTo(now())
        );

        if ($futureComment?->next_follow_up_at !== null) {
            return $futureComment->next_follow_up_at;
        }

        /** @var LeadComment|null $pastComment */
        $pastComment = $sortedComments
            ->sortByDesc('next_follow_up_at')
            ->first();

        return $pastComment?->next_follow_up_at;
    }
}
