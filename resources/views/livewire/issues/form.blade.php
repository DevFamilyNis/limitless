<div class="mx-auto w-full max-w-6xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? 'Izmena issue-a' : 'Novi issue' }}</flux:heading>
            <flux:text>Autor je automatski prijavljeni korisnik.</flux:text>
        </div>
        <flux:button variant="ghost" :href="route('issues.index')" wire:navigate>Nazad</flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:select wire:model="projectId" label="Projekat" required>
                <option value="">Izaberi projekat</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="clientId" label="Klijent">
                <option value="">Bez klijenta</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->display_name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="clientContactId" label="Kontakt osoba">
                <option value="">Bez kontakta</option>
                @foreach ($clientContacts as $contact)
                    <option value="{{ $contact->id }}">{{ $contact->full_name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <flux:select wire:model="statusId" label="Status" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="priorityId" label="Prioritet" required>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="categoryId" label="Kategorija" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="assigneeId" label="Dodeljeno">
                <option value="">Nije dodeljeno</option>
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <flux:input wire:model="title" label="Naslov" required />
            </div>
            <flux:input wire:model="dueDate" label="Rok" type="date" />
        </div>

        <flux:textarea wire:model="description" label="Opis" rows="6" />

        <div class="flex gap-2">
            <flux:button variant="primary" type="submit">Sačuvaj</flux:button>
        </div>
    </form>
</div>
