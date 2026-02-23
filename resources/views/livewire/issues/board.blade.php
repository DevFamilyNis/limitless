<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">Issue board</flux:heading>
            <flux:text>Kanban light pregled bez drag-and-drop.</flux:text>
        </div>

        <div class="flex gap-2">
            <flux:button variant="ghost" :href="route('issues.index')" wire:navigate>Issues</flux:button>
            <flux:button variant="primary" :href="route('issues.create')" wire:navigate>Novi issue</flux:button>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-6">
        <flux:select wire:model.live="projectId" label="Projekat" required>
            <option value="">Izaberi projekat</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </flux:select>
        <flux:input wire:model.live.debounce.300ms="search" label="Pretraga" placeholder="Naslov/opis" />
        <flux:select wire:model.live="categoryId" label="Kategorija">
            <option value="">Sve</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="priorityId" label="Prioritet">
            <option value="">Svi</option>
            @foreach ($priorities as $priority)
                <option value="{{ $priority->id }}">{{ $priority->name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="clientId" label="Klijent">
            <option value="">Svi</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}">{{ $client->display_name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="assigneeId" label="Dodeljeno">
            <option value="">Svi</option>
            @foreach ($assignees as $assignee)
                <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
            @endforeach
        </flux:select>
    </div>

    <div class="grid gap-4 xl:grid-cols-4">
        @foreach ($statuses as $status)
            <div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="sm">{{ $status->name }}</flux:heading>
                    <flux:text class="text-xs">{{ ($issuesByStatus[$status->id] ?? collect())->count() }}</flux:text>
                </div>

                <div class="space-y-3">
                    @forelse ($issuesByStatus[$status->id] ?? [] as $issue)
                        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                            <a href="{{ route('issues.show', $issue) }}" wire:navigate class="block text-sm font-semibold hover:underline">
                                {{ $issue->title }}
                            </a>
                            <div class="mt-2 flex flex-wrap gap-1 text-xs">
                                <span class="rounded bg-zinc-100 px-2 py-1 dark:bg-zinc-800">{{ $issue->priority?->name }}</span>
                                <span class="rounded bg-zinc-100 px-2 py-1 dark:bg-zinc-800">{{ $issue->category?->name }}</span>
                                @if ($issue->assignee)
                                    <span class="rounded bg-zinc-100 px-2 py-1 dark:bg-zinc-800">{{ $issue->assignee->name }}</span>
                                @endif
                            </div>
                            @if ($issue->due_date)
                                <div class="mt-2 text-xs text-zinc-500">Rok: {{ $issue->due_date->format('d.m.Y') }}</div>
                            @endif

                            <flux:dropdown class="mt-3">
                                <flux:button variant="ghost" size="sm" icon-trailing="chevron-down">Move to…</flux:button>
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
                            Nema issue-a.
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
