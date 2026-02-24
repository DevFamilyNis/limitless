<div class="mx-auto w-full max-w-6xl space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <flux:heading size="xl">{{ $issue->title }}</flux:heading>
            <flux:text>{{ $issue->project?->name }} | {{ $issue->status?->name }}</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" :href="route('issues.board')" wire:navigate>@lang('messages.actions.board')</flux:button>
            <flux:button variant="primary" :href="route('issues.edit', $issue)" wire:navigate>@lang('messages.actions.edit')</flux:button>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <div class="text-xs text-zinc-500">@lang('messages.issues.priority')</div>
                <div>{{ $issue->priority?->name }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-500">@lang('messages.issues.category')</div>
                <div>{{ $issue->category?->name }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-500">@lang('messages.issues.due_date')</div>
                <div>{{ $issue->due_date?->format('d.m.Y') ?? '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-500">@lang('messages.issues.author')</div>
                <div>{{ $issue->author?->name }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-500">@lang('messages.issues.assignee')</div>
                <div>{{ $issue->assignee?->name ?? '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-500">@lang('messages.issues.client')</div>
                <div>{{ $issue->client?->display_name ?? '-' }}</div>
            </div>
        </div>

        @if ($issue->description)
            <div class="mt-4 whitespace-pre-line text-sm">{{ $issue->description }}</div>
        @endif
    </div>

    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:heading size="sm" class="mb-3">@lang('messages.issues.attachments')</flux:heading>

        <form wire:submit="uploadAttachments" class="flex flex-col gap-3 md:flex-row md:items-end">
            <flux:input type="file" wire:model="attachments" multiple :label="__('messages.issues.add_files')" />
            <flux:button type="submit" variant="primary">@lang('messages.actions.upload')</flux:button>
        </form>

        <div class="mt-4 space-y-2">
            @forelse ($issue->getMedia('attachments') as $media)
                <div class="flex items-center justify-between rounded border border-zinc-200 p-2 text-sm dark:border-zinc-700">
                    <a href="{{ $media->getUrl() }}" target="_blank" class="hover:underline">{{ $media->file_name }}</a>
                    <flux:button size="xs" variant="danger" wire:click="deleteAttachment({{ $media->id }})">@lang('messages.actions.delete')</flux:button>
                </div>
            @empty
                <div class="text-sm text-zinc-500">@lang('messages.issues.no_files')</div>
            @endforelse
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:heading size="sm" class="mb-3">@lang('messages.issues.comments')</flux:heading>

        <form wire:submit="addComment" class="space-y-3">
            <flux:textarea wire:model="comment" rows="3" :label="__('messages.issues.new_comment')" />
            <flux:button type="submit" variant="primary">@lang('messages.actions.add_comment')</flux:button>
        </form>

        <div class="mt-4 space-y-3">
            @forelse ($issue->comments as $comment)
                <div class="rounded border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="mb-2 flex items-center justify-between text-xs text-zinc-500">
                        <span>{{ $comment->author?->name }} | {{ $comment->created_at?->format('d.m.Y H:i') }}</span>
                        @if ($comment->author_id === auth()->id())
                            <flux:button size="xs" variant="danger" wire:click="deleteComment({{ $comment->id }})">@lang('messages.actions.delete')</flux:button>
                        @endif
                    </div>
                    <div class="whitespace-pre-line text-sm">{{ $comment->body }}</div>
                </div>
            @empty
                <div class="text-sm text-zinc-500">@lang('messages.issues.no_comments')</div>
            @endforelse
        </div>
    </div>
</div>
