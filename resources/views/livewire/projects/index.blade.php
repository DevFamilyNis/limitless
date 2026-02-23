<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.text.projects')</flux:heading>
            <flux:text>@lang('messages.text.projectSubTitle')</flux:text>
        </div>

        <flux:button variant="primary" :href="route('projects.create')" wire:navigate>
            @lang('messages.buttons.add')
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        <flux:input wire:model.live.debounce.300ms="search" label="Pretraga" placeholder="Kod, naziv ili opis" />

        <flux:select wire:model.live="statusFilter" label="Status">
            <option value="all">@lang('messages.text.all')</option>
            <option value="active">@lang('messages.text.active')</option>
            <option value="inactive">@lang('messages.text.inactive')</option>
        </flux:select>
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>@lang('messages.table.code')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.table.name')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.form.note')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.table.status')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.table.action')</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
                @forelse ($projects as $project)
                    <x-ui.table.row wire:key="project-{{ $project->id }}">
                        <x-ui.table.td class="font-medium">{{ $project->code }}</x-ui.table.td>
                        <x-ui.table.td>{{ $project->name }}</x-ui.table.td>
                        <x-ui.table.td>{{ $project->description ?: '-' }}</x-ui.table.td>
                        <x-ui.table.td>
                            @if ($project->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">Aktivan</span>
                            @else
                                <span class="inline-flex rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Neaktivan</span>
                            @endif
                        </x-ui.table.td>
                        <x-ui.table.td align="right">
                            <x-ui.table.actions>
                                <x-ui.buttons.icon-action
                                    :href="route('projects.edit', $project)"
                                    title="Izmeni projekat"
                                    color="primary"
                                    navigate
                                >
                                    <x-ui.icons.pen :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>

                                <x-ui.buttons.icon-action
                                    wire:click="toggleActive({{ $project->id }})"
                                    :title="$project->is_active ? 'Deaktiviraj projekat' : 'Aktiviraj projekat'"
                                    color="warning"
                                >
                                    <x-ui.icons.disable :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>

                                <x-ui.buttons.icon-action
                                    wire:click="deleteProject({{ $project->id }})"
                                    title="Obriši projekat"
                                    color="danger"
                                >
                                    <x-ui.icons.trash :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>
                            </x-ui.table.actions>
                        </x-ui.table.td>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="5">
                        @lang('messages.table.noResults')
                    </x-ui.table.empty>
                @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>
        {{ $projects->links() }}
    </div>
</div>
