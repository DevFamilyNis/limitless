<?php

declare(strict_types=1);

namespace App\Domain\Leads\Queries;

use App\Domain\Leads\DTO\LeadFiltersData;
use App\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class LeadListQuery
{
    /**
     * @return LengthAwarePaginator<int, \App\Models\Lead>
     */
    public function execute(LeadFiltersData $dto): LengthAwarePaginator
    {
        return Lead::query()
            ->where('lead_campaign_id', $dto->campaignId)
            ->with('status')
            ->with(['comments' => fn ($query) => $query
                ->select(['id', 'lead_id', 'next_follow_up_at'])
                ->whereNotNull('next_follow_up_at')
                ->orderBy('next_follow_up_at')])
            ->withCount('comments')
            ->when($dto->search !== null, function (Builder $query) use ($dto): void {
                $query->where(function (Builder $innerQuery) use ($dto): void {
                    $innerQuery
                        ->where('company_name', 'like', '%'.$dto->search.'%')
                        ->orWhere('email', 'like', '%'.$dto->search.'%')
                        ->orWhere('phone', 'like', '%'.$dto->search.'%');
                });
            })
            ->when($dto->statusKey !== null, fn (Builder $query) => $query->whereHas('status', fn (Builder $statusQuery) => $statusQuery->where('key', $dto->statusKey)))
            ->orderByDesc('id')
            ->paginate(10);
    }
}
