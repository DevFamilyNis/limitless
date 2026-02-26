<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
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

        <div class="flex gap-2">
            <flux:button
                variant="ghost"
                wire:click="setViewMode('{{ $viewMode === 'kanban' ? 'table' : 'kanban' }}')"
                :title="$viewMode === 'kanban' ? __('messages.issues.switch_to_table') : __('messages.issues.switch_to_kanban')"
            >
                @if ($viewMode === 'kanban')
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h16.5A1.5 1.5 0 0 1 21.75 6v12a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5V6a1.5 1.5 0 0 1 1.5-1.5Zm0 5.25h16.5m-11.25 9.75v-9.75m5.25 9.75v-9.75" />
                    </svg>
                @else
                    <x-ui.icons.columns-3 class="size-4" />
                @endif
            </flux:button>

            <flux:button variant="primary" :href="route('issues.create')" wire:navigate>@lang('messages.buttons.add')</flux:button>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-6">
        <flux:select wire:model.live="projectId" :label="__('messages.issues.project')">
            <option value="">@lang('messages.common.all')</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </flux:select>
        <flux:input wire:model.live.debounce.300ms="search" :label="__('messages.common.search')" :placeholder="__('messages.issues.search_placeholder')" />
        <flux:select wire:model.live="categoryId" :label="__('messages.issues.category')">
            <option value="">@lang('messages.common.all')</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </flux:select>
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
    </div>

    @if ($viewMode === 'kanban')
        <div class="grid gap-4 xl:grid-cols-4">
            @foreach ($statuses as $status)
                @php($statusColor = \App\Support\IssueLabelPalette::forStatus($status->key, $status->name))
                <div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="mb-3 flex items-center justify-between">
                        <flux:heading size="sm">
                            <span
                                class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                style="background-color: {{ $statusColor['soft_bg'] }}; border-color: {{ $statusColor['border'] }}; border-width: {{ $statusColor['border_width'] }}; color: {{ $statusColor['hex'] }}; font-weight: {{ $statusColor['font_weight'] }};"
                            >
                                {{ $status->name }}
                            </span>
                        </flux:heading>
                        <flux:text class="text-xs">{{ ($issuesByStatus[$status->id] ?? collect())->count() }}</flux:text>
                    </div>

                    <div class="space-y-3">
                        @forelse ($issuesByStatus[$status->id] ?? [] as $issue)
                            @php($projectColor = $issue->project ? \App\Support\ProjectColorPalette::for($issue->project) : null)
                            @php($priorityColor = \App\Support\IssueLabelPalette::forPriority($issue->priority?->key, $issue->priority?->name))
                            @php($categoryColor = \App\Support\IssueLabelPalette::forCategory($issue->category?->name))
                            <div
                                class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900"
                                @if ($projectColor)
                                    style="border-color: {{ $projectColor['border'] }}; background-color: {{ $projectColor['soft_bg'] }};"
                                @endif
                            >
                                <a
                                    href="{{ route('issues.show', $issue) }}"
                                    wire:navigate
                                    class="block text-sm font-semibold hover:underline"
                                    @if ($projectColor)
                                        style="color: {{ $projectColor['hex'] }};"
                                    @endif
                                >
                                    {{ $issue->title }}
                                </a>
                                <div class="mt-2 flex flex-wrap gap-1 text-xs">
                                    @if ($issue->project)
                                        <span
                                            class="rounded border px-2 py-1"
                                            @if ($projectColor)
                                                style="background-color: {{ $projectColor['strong_bg'] }}; border-color: {{ $projectColor['border'] }}; color: {{ $projectColor['hex'] }};"
                                            @endif
                                        >
                                            {{ $issue->project->name }}
                                        </span>
                                    @endif
                                    <span
                                        class="rounded border px-2 py-1"
                                        style="background-color: {{ $priorityColor['soft_bg'] }}; border-color: {{ $priorityColor['border'] }}; border-width: {{ $priorityColor['border_width'] }}; color: {{ $priorityColor['hex'] }}; font-weight: {{ $priorityColor['font_weight'] }};"
                                    >
                                        {{ $issue->priority?->name }}
                                    </span>
                                    <span
                                        class="rounded border px-2 py-1"
                                        style="background-color: {{ $categoryColor['soft_bg'] }}; border-color: {{ $categoryColor['border'] }}; border-width: {{ $categoryColor['border_width'] }}; color: {{ $categoryColor['hex'] }}; font-weight: {{ $categoryColor['font_weight'] }};"
                                    >
                                        {{ $issue->category?->name }}
                                    </span>
                                    @if ($issue->assignee)
                                        <span class="rounded bg-zinc-100 px-2 py-1 dark:bg-zinc-800">{{ $issue->assignee->name }}</span>
                                    @endif
                                </div>
                                @if ($issue->due_date)
                                    <div class="mt-2 text-xs text-zinc-500">@lang('messages.issues.due_date'): {{ $issue->due_date->format('d.m.Y') }}</div>
                                @endif

                                <flux:dropdown class="mt-3">
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
                    @php($projectColor = $issue->project ? \App\Support\ProjectColorPalette::for($issue->project) : null)
                    @php($statusColor = \App\Support\IssueLabelPalette::forStatus($issue->status?->key, $issue->status?->name))
                    @php($priorityColor = \App\Support\IssueLabelPalette::forPriority($issue->priority?->key, $issue->priority?->name))
                    @php($categoryColor = \App\Support\IssueLabelPalette::forCategory($issue->category?->name))
                    <x-ui.table.row wire:key="issue-{{ $issue->id }}">
                        <x-ui.table.td>
                            <div class="font-medium">{{ $issue->title }}</div>
                            @if ($issue->project)
                                <div
                                    class="mt-1 inline-flex rounded-full border px-2 py-1 text-xs font-medium"
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
                                class="inline-flex rounded-full border px-2 py-1 text-xs font-medium"
                                style="background-color: {{ $statusColor['soft_bg'] }}; border-color: {{ $statusColor['border'] }}; border-width: {{ $statusColor['border_width'] }}; color: {{ $statusColor['hex'] }}; font-weight: {{ $statusColor['font_weight'] }};"
                            >
                                {{ $issue->status?->name }}
                            </span>
                        </x-ui.table.td>
                        <x-ui.table.td>
                            <span
                                class="inline-flex rounded-full border px-2 py-1 text-xs font-medium"
                                style="background-color: {{ $priorityColor['soft_bg'] }}; border-color: {{ $priorityColor['border'] }}; border-width: {{ $priorityColor['border_width'] }}; color: {{ $priorityColor['hex'] }}; font-weight: {{ $priorityColor['font_weight'] }};"
                            >
                                {{ $issue->priority?->name }}
                            </span>
                        </x-ui.table.td>
                        <x-ui.table.td>
                            <span
                                class="inline-flex rounded-full border px-2 py-1 text-xs font-medium"
                                style="background-color: {{ $categoryColor['soft_bg'] }}; border-color: {{ $categoryColor['border'] }}; border-width: {{ $categoryColor['border_width'] }}; color: {{ $categoryColor['hex'] }}; font-weight: {{ $categoryColor['font_weight'] }};"
                            >
                                {{ $issue->category?->name }}
                            </span>
                        </x-ui.table.td>
                        <x-ui.table.td>{{ $issue->due_date?->format('d.m.Y') ?? '-' }}</x-ui.table.td>
                        <x-ui.table.td align="right">
                            <x-ui.table.actions>
                                <x-ui.buttons.icon-action :href="route('issues.show', $issue)" :title="__('messages.issues.open_issue')" color="primary" navigate>
                                    <x-ui.icons.check :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>
                                <x-ui.buttons.icon-action :href="route('issues.edit', $issue)" :title="__('messages.issues.edit_issue')" color="warning" navigate>
                                    <x-ui.icons.pen :class="$actionIconClass" />
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
