<?php

namespace App\Livewire\Issues;

use App\Domain\Issues\Actions\AddIssueCommentAction;
use App\Domain\Issues\Actions\DeleteIssueAttachmentAction;
use App\Domain\Issues\Actions\DeleteIssueCommentAction;
use App\Domain\Issues\Actions\UploadIssueAttachmentsAction;
use App\Domain\Issues\DTO\AddIssueCommentData;
use App\Domain\Issues\DTO\DeleteIssueAttachmentData;
use App\Domain\Issues\DTO\DeleteIssueCommentData;
use App\Domain\Issues\DTO\UploadIssueAttachmentsData;
use App\Models\Issue;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Issue $issue;

    public string $comment = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $attachments = [];

    public function mount(Issue $issue): void
    {
        $issue->load('project');

        if ($issue->project->user_id !== Auth::id()) {
            abort(404);
        }

        $this->issue = $issue;
    }

    protected function rules(): array
    {
        return [
            'comment' => ['nullable', 'string'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    public function addComment(): void
    {
        $validated = $this->validate([
            'comment' => ['required', 'string', 'min:2'],
        ]);

        app(AddIssueCommentAction::class)->execute(
            AddIssueCommentData::fromArray([
                'user_id' => Auth::id(),
                'issue_id' => $this->issue->id,
                'body' => $validated['comment'],
            ])
        );

        $this->comment = '';
        $this->issue->refresh();
    }

    public function deleteComment(int $commentId): void
    {
        app(DeleteIssueCommentAction::class)->execute(
            DeleteIssueCommentData::fromArray([
                'user_id' => Auth::id(),
                'issue_id' => $this->issue->id,
                'comment_id' => $commentId,
            ])
        );

        $this->issue->refresh();
    }

    public function uploadAttachments(): void
    {
        $this->validate([
            'attachments.*' => ['required', 'file', 'max:10240'],
        ]);

        app(UploadIssueAttachmentsAction::class)->execute(
            UploadIssueAttachmentsData::fromArray([
                'user_id' => Auth::id(),
                'issue_id' => $this->issue->id,
                'files' => $this->attachments,
            ])
        );

        $this->attachments = [];
        $this->issue->refresh();
    }

    public function deleteAttachment(int $mediaId): void
    {
        app(DeleteIssueAttachmentAction::class)->execute(
            DeleteIssueAttachmentData::fromArray([
                'user_id' => Auth::id(),
                'issue_id' => $this->issue->id,
                'media_id' => $mediaId,
            ])
        );
        $this->issue->refresh();
    }

    public function render(): View
    {
        $this->issue->load([
            'status',
            'priority',
            'category',
            'project',
            'client',
            'clientContact',
            'author',
            'assignee',
            'comments.author',
            'media',
        ]);

        return view('livewire.issues.show')
            ->layout('layouts.app', [
                'title' => __('messages.issues.show_title'),
            ]);
    }
}
