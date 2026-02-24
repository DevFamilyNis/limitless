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

    @if ($projects->isEmpty())
        <flux:card>
            <flux:text>@lang('messages.table.noResults')</flux:text>
        </flux:card>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($projects as $project)
                <flux:card class="flex h-full flex-col gap-4" wire:key="project-card-{{ $project->id }}">
                    <a href="{{ route('projects.show', $project) }}" wire:navigate class="space-y-2">
                        <div class="flex items-center justify-between">
                            <flux:badge color="zinc">{{ $project->code }}</flux:badge>
                            @if ($project->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">Aktivan</span>
                            @else
                                <span class="inline-flex rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Neaktivan</span>
                            @endif
                        </div>

                        <flux:heading size="lg">{{ $project->name }}</flux:heading>
                        <flux:text>{{ $project->description ?: '-' }}</flux:text>
                    </a>

                    <div class="mt-auto grid gap-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Korisnici</span>
                            <span class="font-medium">{{ (int) ($project->clients_count ?? 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Fakture mesec</span>
                            <span class="font-medium">{{ number_format((float) ($project->current_month_total ?? 0), 2, ',', '.') }} RSD</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Vlasnik</span>
                            <span class="font-medium">{{ $project->user?->name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="pt-2">
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
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif

    <div>
        {{ $projects->links() }}
    </div>
</div>
