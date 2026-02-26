<div class="mx-auto w-full max-w-6xl space-y-6">
    @php($projectColor = $issue->project ? \App\Support\ProjectColorPalette::for($issue->project) : null)
    @php($projectHeadingStyle = $projectColor ? "color: {$projectColor['hex']};" : null)
    @php($statusColor = \App\Support\IssueLabelPalette::forStatus($issue->status?->key, $issue->status?->name))
    @php($priorityColor = \App\Support\IssueLabelPalette::forPriority($issue->priority?->key, $issue->priority?->name))
    @php($categoryColor = \App\Support\IssueLabelPalette::forCategory($issue->category?->name))

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <flux:heading size="xl" :style="$projectHeadingStyle">{{ $issue->title }}</flux:heading>
            <flux:text>
                @if ($issue->project)
                    <span
                        class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                        @if ($projectColor)
                            style="background-color: {{ $projectColor['soft_bg'] }}; border-color: {{ $projectColor['border'] }}; color: {{ $projectColor['hex'] }};"
                        @endif
                    >
                        {{ $issue->project->name }}
                    </span>
                @endif
                <span
                    class="ml-2 inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                    style="background-color: {{ $statusColor['soft_bg'] }}; border-color: {{ $statusColor['border'] }}; border-width: {{ $statusColor['border_width'] }}; color: {{ $statusColor['hex'] }}; font-weight: {{ $statusColor['font_weight'] }};"
                >
                    {{ $issue->status?->name }}
                </span>
            </flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" :href="route('issues.index')" wire:navigate>@lang('messages.actions.issues')</flux:button>
            <flux:button variant="primary" :href="route('issues.edit', $issue)" wire:navigate>@lang('messages.actions.edit')</flux:button>
        </div>
    </div>

    <div
        class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
        @if ($projectColor)
            style="border-color: {{ $projectColor['border'] }};"
        @endif
    >
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <div class="text-xs text-zinc-500">@lang('messages.issues.priority')</div>
                <div>
                    <span
                        class="inline-flex rounded-full border px-2 py-1 text-xs font-medium"
                        style="background-color: {{ $priorityColor['soft_bg'] }}; border-color: {{ $priorityColor['border'] }}; border-width: {{ $priorityColor['border_width'] }}; color: {{ $priorityColor['hex'] }}; font-weight: {{ $priorityColor['font_weight'] }};"
                    >
                        {{ $issue->priority?->name }}
                    </span>
                </div>
            </div>
            <div>
                <div class="text-xs text-zinc-500">@lang('messages.issues.category')</div>
                <div>
                    <span
                        class="inline-flex rounded-full border px-2 py-1 text-xs font-medium"
                        style="background-color: {{ $categoryColor['soft_bg'] }}; border-color: {{ $categoryColor['border'] }}; border-width: {{ $categoryColor['border_width'] }}; color: {{ $categoryColor['hex'] }}; font-weight: {{ $categoryColor['font_weight'] }};"
                    >
                        {{ $issue->category?->name }}
                    </span>
                </div>
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
