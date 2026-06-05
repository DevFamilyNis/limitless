<?php

declare(strict_types=1);

namespace App\Domain\Leads\Queries;

use App\Models\Lead;
use App\Models\LeadStatus;

final class LeadStatisticsQuery
{
    /**
     * @return array{
     *     total: int,
     *     converted: int,
     *     responded: int,
     *     conversion_rate: float,
     *     by_status: array<string, int>
     * }
     */
    public function get(int $campaignId): array
    {
        $base = Lead::query()->where('lead_campaign_id', $campaignId);

        $total = (clone $base)->count();
        $converted = (clone $base)->whereNotNull('converted_at')->count();
        $responded = (clone $base)->whereNotNull('last_response_at')->count();

        $byStatus = LeadStatus::query()
            ->get()
            ->mapWithKeys(fn (LeadStatus $status): array => [
                $status->key => (clone $base)->where('lead_status_id', $status->id)->count(),
            ])
            ->all();

        return [
            'total' => $total,
            'converted' => $converted,
            'responded' => $responded,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0.0,
            'by_status' => $byStatus,
        ];
    }
}
