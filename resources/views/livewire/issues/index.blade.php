<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.issues.table_title')</flux:heading>
            <flux:text>@lang('messages.issues.table_subtitle')</flux:text>
        </div>

        <div class="flex gap-2">
            <flux:button variant="ghost" :href="route('issues.board')" wire:navigate>@lang('messages.actions.board')</flux:button>
            <flux:button variant="primary" :href="route('issues.create')" wire:navigate>@lang('messages.actions.new_issue')</flux:button>
        </div>
    </div>

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
                <x-ui.table.row wire:key="issue-{{ $issue->id }}">
                    <x-ui.table.td>
                        <div class="font-medium">{{ $issue->title }}</div>
                        <div class="text-xs text-zinc-500">{{ $issue->project?->name }}</div>
                    </x-ui.table.td>
                    <x-ui.table.td>{{ $issue->status?->name }}</x-ui.table.td>
                    <x-ui.table.td>{{ $issue->priority?->name }}</x-ui.table.td>
                    <x-ui.table.td>{{ $issue->category?->name }}</x-ui.table.td>
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

    <div>{{ $issues->links() }}</div>
</div>
