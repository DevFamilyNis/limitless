@php use App\Support\IssueLabelPalette;use App\Support\ProjectColorPalette; @endphp
<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div>
        <flux:heading size="xl">@lang('messages.menu.tasks')</flux:heading>
        <flux:text>
            @if ($viewMode === 'kanban')
                @lang('messages.issues.board_subtitle')
            @else
                @lang('messages.issues.table_subtitle')
            @endif
        </flux:text>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,1.1fr)_minmax(0,0.95fr)_minmax(0,0.95fr)_minmax(0,0.95fr)_auto_auto]">
        <flux:select wire:model.live="projectId" :label="__('messages.issues.project')">
            <option value="">@lang('messages.common.all')</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </flux:select>
        <flux:input wire:model.live.debounce.300ms="search" :label="__('messages.common.search')" :placeholder="__('messages.issues.search_placeholder')"/>
        <flux:select wire:model.live="priorityId" :label="__('messages.issues.priority')">
            <option value="">@lang('messages.common.all')</option>
            @foreach ($priorities as $priority)
                <option value="{{ $priority->id }}">{{ $priority->name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="clientId" :label="__('messages.issues.client')">
            <option value="">@lang('messages.common.all')</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}">{{ $client->display_name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="assigneeId" :label="__('messages.issues.assignee')">
            <option value="">@lang('messages.common.all')</option>
            @foreach ($assignees as $assignee)
                <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
            @endforeach
        </flux:select>
        <flux:button
                class="self-end"
                variant="ghost"
                wire:click="setViewMode('{{ $viewMode === 'kanban' ? 'table' : 'kanban' }}')"
                :title="$viewMode === 'kanban' ? __('messages.issues.switch_to_table') : __('messages.issues.switch_to_kanban')"
        >
            @if ($viewMode === 'kanban')
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h16.5A1.5 1.5 0 0 1 21.75 6v12a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5V6a1.5 1.5 0 0 1 1.5-1.5Zm0 5.25h16.5m-11.25 9.75v-9.75m5.25 9.75v-9.75"/>
                </svg>
            @else
                <x-ui.icons.columns-3 class="size-4"/>
            @endif
        </flux:button>
        <flux:button class="self-end" variant="primary" :href="route('issues.create')" wire:navigate>@lang('messages.buttons.add')</flux:button>
    </div>

    @if ($viewMode === 'kanban')
        <div class="grid gap-4 xl:grid-cols-4">
            @foreach ($statuses as $status)
                @php($statusColor = IssueLabelPalette::forStatus($status->key, $status->name))
                <div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700 bg-zinc-50">
                    <div class="mb-3 flex items-center justify-between">
                        <flux:heading size="sm">
                            <span
                                    class="inline-flex rounded-lg border px-2 py-1 text-xs font-semibold"
                                    style="background-color: {{ $statusColor['soft_bg'] }}; border-color: {{ $statusColor['border'] }}; border-width: {{ $statusColor['border_width'] }}; color: {{ $statusColor['hex'] }}; font-weight: {{ $statusColor['font_weight'] }};"
                            >
                                {{ $status->name }}
                            </span>
                        </flux:heading>
                         <div class="rounded-xl border-zinc-200 p-1 py-1 dark:border-zinc-700 bg-zinc-50">
                        <flux:text class="text-xs font-bold">
                            {{ ($issuesByStatus[$status->id] ?? collect())->count()}}
                        </flux:text>
                         </div>
                    </div>

                    <div class="max-h-[53rem] space-y-3 overflow-y-auto pr-1">
                        @forelse ($issuesByStatus[$status->id] ?? [] as $issue)
                            @php($priorityColor = IssueLabelPalette::forPriority($issue->priority?->key, $issue->priority?->name))
                            @php($isDone = $issue->status?->key === 'done')
                            @php($isUrgent = $issue->priority?->key === 'urgent' && ! $isDone)
                            @php($previewDescription = \Illuminate\Support\Str::limit(trim(strip_tags((string)$issue->description)), 100))
                            <div
                                    class="rounded-lg border border-zinc-300 bg-slate-300/20 p-3 text-zinc-700
                                    transition-all dark:border-zinc-700 dark:bg-zinc-900/70 dark:text-zinc-200"
                            >
                                <div class="flex h-full flex-col gap-3">
                                    <div>
                                        <span
                                                class="inline-flex rounded-lg border px-2 py-1 text-[11px] font-semibold"
                                                style="background-color: {{ $priorityColor['soft_bg'] }}; border-color: {{ $priorityColor['border'] }}; border-width: {{ $priorityColor['border_width'] }}; color: {{ $priorityColor['hex'] }}; font-weight: {{ $priorityColor['font_weight'] }};"
                                        >
                                            {{ $issue->priority?->name }}
                                        </span>
                                    </div>
                                    @if ($issue->client)
                                        <div>
                                            <span class="inline-flex rounded-lg border border-zinc-200 bg-white px-2 py-1 text-[11px] font-medium text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900/80 dark:text-zinc-300">
                                                {{ $issue->client->display_name }}
                                            </span>
                                        </div>
                                    @endif
                                    <div>
                                        <a
                                                href="{{ route('issues.show', $issue) }}"
                                                wire:navigate
                                                class="block text-sm font-semibold text-zinc-800 hover:underline dark:text-zinc-100"
                                        >
                                            {{ $issue->title }}
                                        </a>
                                        @if ($previewDescription !== '')
                                            <div class="mt-1 text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                                                {{ $previewDescription }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-auto space-y-2">
                                        @if ($issue->due_date)
                                            <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                                                    <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>{{ $issue->due_date->format('d.m.Y') }}</span>
                                            </div>
                                        @endif
                                        @if ($issue->comments_count > 0)
                                            <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
                                                </svg>
                                                <span>{{ $issue->comments_count }}</span>
                                            </div>
                                        @endif
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon-trailing="chevron-down">@lang('messages.issues.move_to')</flux:button>
                                            <flux:menu>
                                                @foreach ($statuses as $moveStatus)
                                                    @if ($moveStatus->id !== $issue->status_id)
                                                        <flux:menu.item wire:click="moveIssue({{ $issue->id }}, {{ $moveStatus->id }})">
                                                            {{ $moveStatus->name }}
                                                        </flux:menu.item>
                                                    @endif
                                                @endforeach
                                            </flux:menu>
                                        </flux:dropdown>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-500 dark:border-zinc-700">
                                @lang('messages.issues.empty_column')
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <x-ui.table>
            <x-ui.table.head>
                <tr>
                    <x-ui.table.th>@lang('messages.issues.title_label')</x-ui.table.th>
                    <x-ui.table.th>@lang('messages.issues.status')</x-ui.table.th>
                    <x-ui.table.th>@lang('messages.issues.priority')</x-ui.table.th>
                    <x-ui.table.th>@lang('messages.issues.category')</x-ui.table.th>
                    <x-ui.table.th>@lang('messages.issues.due_date')</x-ui.table.th>
                    <x-ui.table.th align="right">@lang('messages.common.action')</x-ui.table.th>
                </tr>
            </x-ui.table.head>
            <x-ui.table.body>
                @forelse ($issues as $issue)
                    @php($projectColor = $issue->project ? ProjectColorPalette::for($issue->project) : null)
                    @php($statusColor = IssueLabelPalette::forStatus($issue->status?->key, $issue->status?->name))
                    @php($priorityColor = IssueLabelPalette::forPriority($issue->priority?->key, $issue->priority?->name))
                    @php($categoryColor = IssueLabelPalette::forCategory($issue->category?->name))
                    <x-ui.table.row wire:key="issue-{{ $issue->id }}">
                        <x-ui.table.td>
                            <div class="font-medium">{{ $issue->title }}</div>
                            @if ($issue->project)
                                <div
                                        class="mt-1 inline-flex rounded-lg border px-2 py-1 text-xs font-medium"
                                        @if ($projectColor)
                                            style="background-color: {{ $projectColor['soft_bg'] }}; border-color: {{ $projectColor['border'] }}; color: {{ $projectColor['hex'] }};"
                                        @endif
                                >
                                    {{ $issue->project->name }}
                                </div>
                            @endif
                        </x-ui.table.td>
                        <x-ui.table.td>
                            <span
                                    class="inline-flex rounded-lg border px-2 py-1 text-xs font-medium"
                                    style="background-color: {{ $statusColor['soft_bg'] }}; border-color: {{ $statusColor['border'] }}; border-width: {{ $statusColor['border_width'] }}; color: {{ $statusColor['hex'] }}; font-weight: {{ $statusColor['font_weight'] }};"
                            >
                                {{ $issue->status?->name }}
                            </span>
                        </x-ui.table.td>
                        <x-ui.table.td>
                            <span
                                    class="inline-flex rounded-lg border px-2 py-1 text-xs font-medium"
                                    style="background-color: {{ $priorityColor['soft_bg'] }}; border-color: {{ $priorityColor['border'] }}; border-width: {{ $priorityColor['border_width'] }}; color: {{ $priorityColor['hex'] }}; font-weight: {{ $priorityColor['font_weight'] }};"
                            >
                                {{ $issue->priority?->name }}
                            </span>
                        </x-ui.table.td>
                        <x-ui.table.td>
                            <span
                                    class="inline-flex rounded-lg border px-2 py-1 text-xs font-medium"
                                    style="background-color: {{ $categoryColor['soft_bg'] }}; border-color: {{ $categoryColor['border'] }}; border-width: {{ $categoryColor['border_width'] }}; color: {{ $categoryColor['hex'] }}; font-weight: {{ $categoryColor['font_weight'] }};"
                            >
                                {{ $issue->category?->name }}
                            </span>
                        </x-ui.table.td>
                        <x-ui.table.td>{{ $issue->due_date?->format('d.m.Y') ?? '-' }}</x-ui.table.td>
                        <x-ui.table.td align="right">
                            <x-ui.table.actions>
                                <x-ui.buttons.icon-action :href="route('issues.show', $issue)" :title="__('messages.issues.open_issue')" color="primary" navigate>
                                    <x-ui.icons.check :class="$actionIconClass"/>
                                </x-ui.buttons.icon-action>
                                <x-ui.buttons.icon-action :href="route('issues.edit', $issue)" :title="__('messages.issues.edit_issue')" color="warning" navigate>
                                    <x-ui.icons.pen :class="$actionIconClass"/>
                                </x-ui.buttons.icon-action>
                            </x-ui.table.actions>
                        </x-ui.table.td>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="6">@lang('messages.issues.empty')</x-ui.table.empty>
                @endforelse
            </x-ui.table.body>
        </x-ui.table>

        <div>{{ $issues?->links() }}</div>
    @endif
</div>
