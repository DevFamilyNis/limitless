<div class="mx-auto w-full max-w-6xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? __('messages.issues.edit_title') : __('messages.issues.new_title') }}</flux:heading>
            <flux:text>@lang('messages.issues.author_auto')</flux:text>
        </div>
        <flux:button variant="ghost" :href="route('issues.index')" wire:navigate>@lang('messages.actions.back')</flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:select wire:model="projectId" :label="__('messages.issues.project')" required>
                <option value="">@lang('messages.issues.select_project')</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="clientId" :label="__('messages.issues.client')">
                <option value="">@lang('messages.issues.without_client')</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->display_name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="clientContactId" :label="__('messages.issues.contact_person')">
                <option value="">@lang('messages.issues.without_contact')</option>
                @foreach ($clientContacts as $contact)
                    <option value="{{ $contact->id }}">{{ $contact->full_name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <flux:select wire:model="statusId" :label="__('messages.issues.status')" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="priorityId" :label="__('messages.issues.priority')" required>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="categoryId" :label="__('messages.issues.category')" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="assigneeId" :label="__('messages.issues.assignee')">
                <option value="">@lang('messages.issues.not_assigned')</option>
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <flux:input wire:model="title" :label="__('messages.issues.title_label')" required />
            </div>
            <flux:input wire:model="dueDate" :label="__('messages.issues.due_date')" type="date" />
        </div>

        <flux:textarea wire:model="description" :label="__('messages.issues.description')" rows="6" />

        <div class="flex gap-2">
            <flux:button variant="primary" type="submit">@lang('messages.actions.save')</flux:button>
        </div>
    </form>
</div>
