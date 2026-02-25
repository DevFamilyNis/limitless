<?php

declare(strict_types=1);

namespace App\Domain\Issues\Actions;

use App\Domain\Issues\DTO\AddIssueCommentData;
use App\Models\Issue;
use App\Models\IssueComment;
use Illuminate\Auth\Access\AuthorizationException;

final class AddIssueCommentAction
{
    public function execute(AddIssueCommentData $dto): IssueComment
    {
        $authorId = auth()->id();

        if (! is_int($authorId)) {
            throw new AuthorizationException('Nije moguće odrediti autora komentara.');
        }

        Issue::query()->findOrFail($dto->issueId);

        return IssueComment::query()->create([
            'issue_id' => $dto->issueId,
            'author_id' => $authorId,
            'body' => $dto->body,
        ]);
    }
}
