<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? __('messages.client_project_rates.edit_title') : __('messages.client_project_rates.new_title') }}</flux:heading>
            <flux:text>@lang('messages.client_project_rates.form_subtitle')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('client-project-rates.index')" wire:navigate>
            @lang('messages.actions.back')
        </flux:button>
    </div>

    @unless ($hasRequiredData)
        <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
            @lang('messages.client_project_rates.requirements')
        </flux:callout>
    @endunless

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:select wire:model="clientId" :label="__('messages.client_project_rates.client')" required>
                <option value="">@lang('messages.client_project_rates.select_client')</option>
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

            <flux:select wire:model="projectId" :label="__('messages.client_project_rates.project')" required>
                <option value="">@lang('messages.client_project_rates.select_project')</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->code }} - {{ $project->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <flux:select wire:model="billingPeriodId" :label="__('messages.client_project_rates.billing_period')" required>
                <option value="">@lang('messages.client_project_rates.select_period')</option>
                @foreach ($billingPeriods as $billingPeriod)
                    <option value="{{ $billingPeriod->id }}">{{ $billingPeriod->name }}</option>
                @endforeach
            </flux:select>

            <flux:input wire:model="priceAmount" :label="__('messages.client_project_rates.price')" type="number" step="0.01" min="0.01" required />
            <flux:input wire:model="currency" :label="__('messages.common.currency')" required />
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit" :disabled="! $hasRequiredData">
                @lang('messages.actions.save')
            </flux:button>
        </div>
    </form>
</div>
