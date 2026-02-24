<div class="mx-auto w-full max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? __('messages.transactions.edit_title') : __('messages.transactions.new_title') }}</flux:heading>
            <flux:text>@lang('messages.transactions.form_subtitle')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('transactions.index')" wire:navigate>
            @lang('messages.actions.back')
        </flux:button>
    </div>

    @unless ($hasRequiredData)
        <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
            @lang('messages.transactions.requirements')
        </flux:callout>
    @endunless

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:select wire:model="categoryId" :label="__('messages.transactions.category')" required>
                <option value="">@lang('messages.transactions.select_category')</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->type?->key === 'expense' ? __('messages.categories.expense') : __('messages.categories.income') }})</option>
                @endforeach
            </flux:select>

            <flux:select wire:model="clientId" :label="__('messages.transactions.client_optional')">
                <option value="">@lang('messages.transactions.without_client')</option>
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
            <flux:select wire:model.live="documentType" :label="__('messages.transactions.document')" required>
                <option value="invoice">@lang('messages.transactions.invoice')</option>
                <option value="fiscal">@lang('messages.transactions.fiscal_receipt')</option>
            </flux:select>

            @if ($documentType === 'invoice')
                <flux:select wire:model.live="invoiceId" :label="__('messages.transactions.invoice')" required>
                    <option value="">@lang('messages.transactions.select_invoice')</option>
                    @foreach ($invoices as $invoice)
                        <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }}</option>
                    @endforeach
                </flux:select>
            @else
                <flux:input :label="__('messages.transactions.invoice')" :value="__('messages.transactions.fiscal_no_invoice')" readonly />
            @endif

            <flux:input :label="__('messages.common.currency')" value="RSD" readonly />
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <flux:input wire:model="date" :label="__('messages.transactions.date')" type="date" required />
            <flux:input wire:model="amount" :label="__('messages.common.amount')" type="number" min="0.01" step="0.01" required />
            <flux:input wire:model="title" :label="__('messages.transactions.title_label')" required />
        </div>

        <flux:textarea wire:model="note" :label="__('messages.invoices.note')" rows="3" />

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit" :disabled="! $hasRequiredData">
                @lang('messages.actions.save')
            </flux:button>
        </div>
    </form>
</div>
