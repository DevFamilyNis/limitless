<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? 'Izmena cene klijenta' : 'Nova cena klijenta' }}</flux:heading>
            <flux:text>Poveži klijenta i projekat sa cenom i periodom naplate.</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('client-project-rates.index')" wire:navigate>
            Nazad
        </flux:button>
    </div>

    @unless ($hasRequiredData)
        <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
            Za unos cene potrebno je da imaš bar jednog aktivnog klijenta i jedan aktivan projekat.
        </flux:callout>
    @endunless

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:select wire:model="clientId" label="Klijent" required>
                <option value="">Izaberi klijenta</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">
                        @if ($client->type?->key === 'person' && $client->person)
                            {{ trim($client->person->first_name.' '.$client->person->last_name) }}
                        @else
                            {{ $client->display_name }}
                        @endif
                    </option>
                @endforeach
            </flux:select>

            <flux:select wire:model="projectId" label="Projekat" required>
                <option value="">Izaberi projekat</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->code }} - {{ $project->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <flux:select wire:model="billingPeriodId" label="Period naplate" required>
                <option value="">Izaberi period</option>
                @foreach ($billingPeriods as $billingPeriod)
                    <option value="{{ $billingPeriod->id }}">{{ $billingPeriod->name }}</option>
                @endforeach
            </flux:select>

            <flux:input wire:model="priceAmount" label="Cena" type="number" step="0.01" min="0.01" required />
            <flux:input wire:model="currency" label="Valuta" required />
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit" :disabled="! $hasRequiredData">
                Sačuvaj
            </flux:button>
        </div>
    </form>
</div>
