<?php

declare(strict_types=1);

namespace App\Domain\Leads\Queries;

use App\Models\Lead;
use App\Models\LeadComment;
use App\Models\LeadStatus;

final class LeadStatisticsQuery
{
    /**
     * @return array{
     *     total:int,
     *     converted:int,
     *     responded:int,
     *     conversion_rate:float,
     *     by_status: array<string, int>,
     *     by_outcome: array<string, int>
     * }
     */
    public function get(): array
    {
        $total = Lead::query()->count();
        $converted = Lead::query()->whereNotNull('converted_at')->count();
        $responded = Lead::query()->whereNotNull('last_response_at')->count();

        $byStatus = LeadStatus::query()
            ->get()
            ->mapWithKeys(fn (LeadStatus $status): array => [
                $status->key => Lead::query()->where('lead_status_id', $status->id)->count(),
            ])
            ->all();

        $byOutcome = LeadComment::query()
            ->whereNotNull('outcome')
            ->selectRaw('outcome, COUNT(*) as aggregate')
            ->groupBy('outcome')
            ->pluck('aggregate', 'outcome')
            ->map(fn (int|string $count): int => (int) $count)
            ->all();

        return [
            'total' => $total,
            'converted' => $converted,
            'responded' => $responded,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0.0,
            'by_status' => $byStatus,
            'by_outcome' => $byOutcome,
        ];
    }
}
