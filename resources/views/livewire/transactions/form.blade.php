<div class="mx-auto w-full max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? 'Izmena transakcije' : 'Nova transakcija' }}</flux:heading>
            <flux:text>Unesi podatke o transakciji. Valuta je uvek RSD.</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('transactions.index')" wire:navigate>
            Nazad
        </flux:button>
    </div>

    @unless ($hasRequiredData)
        <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
            Za unos transakcije potrebno je da imaš bar jednu kategoriju.
        </flux:callout>
    @endunless

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:select wire:model="categoryId" label="Kategorija" required>
                <option value="">Izaberi kategoriju</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->type?->key === 'expense' ? 'Rashod' : $category->type?->name }})</option>
                @endforeach
            </flux:select>

            <flux:select wire:model="clientId" label="Klijent (opciono)">
                <option value="">Bez klijenta</option>
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
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <flux:select wire:model.live="documentType" label="Dokument" required>
                <option value="invoice">Faktura</option>
                <option value="fiscal">Fiskalni račun</option>
            </flux:select>

            @if ($documentType === 'invoice')
                <flux:select wire:model.live="invoiceId" label="Faktura" required>
                    <option value="">Izaberi fakturu</option>
                    @foreach ($invoices as $invoice)
                        <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }}</option>
                    @endforeach
                </flux:select>
            @else
                <flux:input label="Faktura" value="Fiskalni račun (bez povezane fakture)" readonly />
            @endif

            <flux:input label="Valuta" value="RSD" readonly />
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <flux:input wire:model="date" label="Datum" type="date" required />
            <flux:input wire:model="amount" label="Iznos" type="number" min="0.01" step="0.01" required />
            <flux:input wire:model="title" label="Naslov" required />
        </div>

        <flux:textarea wire:model="note" label="Napomena" rows="3" />

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit" :disabled="! $hasRequiredData">
                Sačuvaj
            </flux:button>
        </div>
    </form>
</div>
