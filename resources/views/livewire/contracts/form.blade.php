<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">
                {{ $isEditing ? __('messages.contracts.form_edit_title') : __('messages.contracts.form_new_title') }}
            </flux:heading>
            <flux:text>@lang('messages.contracts.form_subtitle')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('contracts.index')" wire:navigate>
            @lang('messages.buttons.back')
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">

        @if (! $isEditing)
            <flux:select wire:model.live="clientId" :label="__('messages.contracts.customer')" required>
                <option value="">@lang('messages.contracts.select_customer')</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->display_name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="type" :label="__('messages.contracts.type')" required>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}">{{ $type->value }}</option>
                @endforeach
            </flux:select>

            @if ($type === 'Aneks')
                <flux:select wire:model.live="parentId" :label="__('messages.contracts.parent_contract')" required>
                    <option value="">@lang('messages.contracts.select_parent')</option>
                    @foreach ($parentContracts as $parent)
                        <option value="{{ $parent->id }}">
                            {{ $parent->start_date->format('d.m.Y') }}
                            @if ($parent->end_date) – {{ $parent->end_date->format('d.m.Y') }} @endif
                            ({{ $parent->status->value }})
                        </option>
                    @endforeach
                </flux:select>
            @endif
        @else
            <div class="flex gap-3">
                <x-ui.badge color="blue" class="text-sm">{{ $type }}</x-ui.badge>
                @if ($type === 'Aneks')
                    <flux:text class="text-sm text-zinc-500">@lang('messages.contracts.type_cannot_change')</flux:text>
                @endif
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="startDate" :label="__('messages.contracts.start_date')" type="date" required />
            <flux:input wire:model="endDate" :label="__('messages.contracts.end_date')" type="date" />
        </div>

        <flux:textarea wire:model="note" :label="__('messages.contracts.note')" rows="3" />

        <div>
            <flux:label>@lang('messages.contracts.pdf_file')</flux:label>
            @if ($hasPdf && ! $pdfFile)
                <flux:text class="mb-2 text-sm text-zinc-500">@lang('messages.contracts.pdf_exists')</flux:text>
            @endif
            <input
                type="file"
                wire:model="pdfFile"
                accept="application/pdf"
                class="block w-full text-sm text-zinc-500 file:me-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-zinc-300 dark:hover:file:bg-zinc-600"
            />
            @error('pdfFile')
                <flux:text class="mt-1 text-sm text-red-600">{{ $message }}</flux:text>
            @enderror
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                @lang('messages.buttons.save')
            </flux:button>
        </div>
    </form>
</div>
