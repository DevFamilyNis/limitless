<?php

declare(strict_types=1);

namespace App\Domain\Leads\Actions;

use App\Domain\Leads\DTO\AddLeadCommentData;
use App\Models\Lead;
use App\Models\LeadComment;
use App\Models\LeadStatus;
use Illuminate\Support\Facades\DB;

final class AddLeadCommentAction
{
    public function execute(AddLeadCommentData $dto): LeadComment
    {
        return DB::transaction(function () use ($dto): LeadComment {
            $lead = Lead::query()->findOrFail($dto->leadId);
            $statusId = $dto->leadStatusId ?? $lead->lead_status_id;

            $comment = $lead->comments()->create([
                'author_id' => $dto->userId,
                'lead_status_id' => $statusId,
                'event_type' => $dto->eventType,
                'contact_method' => $dto->contactMethod,
                'outcome' => $dto->outcome,
                'body' => $dto->body,
                'contacted_at' => $dto->contactedAt,
                'responded_at' => $dto->respondedAt,
                'next_follow_up_at' => $dto->nextFollowUpAt,
            ]);

            $statusKey = LeadStatus::query()->whereKey($statusId)->value('key');

            $lead->fill([
                'lead_status_id' => $statusId,
                'last_contact_method' => $dto->contactMethod ?? $lead->last_contact_method,
                'last_contacted_at' => $dto->contactedAt ?? $lead->last_contacted_at,
                'last_response_at' => $dto->respondedAt ?? $lead->last_response_at,
                'converted_at' => $statusKey === 'converted' ? ($lead->converted_at ?? now()) : null,
            ]);

            $lead->save();

            return $comment;
        });
    }
}
