<?php

namespace App\Livewire\Issues;

use App\Models\Issue;
use App\Models\IssueComment;
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

        IssueComment::query()->create([
            'issue_id' => $this->issue->id,
            'author_id' => Auth::id(),
            'body' => trim($validated['comment']),
        ]);

        $this->comment = '';
        $this->issue->refresh();
    }

    public function deleteComment(int $commentId): void
    {
        $comment = IssueComment::query()
            ->where('issue_id', $this->issue->id)
            ->findOrFail($commentId);

        if ($comment->author_id !== Auth::id()) {
            abort(403);
        }

        $comment->delete();

        $this->issue->refresh();
    }

    public function uploadAttachments(): void
    {
        $this->validate([
            'attachments.*' => ['required', 'file', 'max:10240'],
        ]);

        foreach ($this->attachments as $file) {
            $this->issue->addMedia($file->getRealPath())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('attachments');
        }

        $this->attachments = [];
        $this->issue->refresh();
    }

    public function deleteAttachment(int $mediaId): void
    {
        $media = $this->issue->media()->findOrFail($mediaId);
        $media->delete();
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
                'title' => 'Issue detalji',
            ]);
    }
}
