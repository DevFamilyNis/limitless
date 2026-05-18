<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('messages.admin.users_title') }}</flux:heading>
            <flux:subheading>{{ __('messages.admin.users_subtitle') }}</flux:subheading>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" class="mb-4">{{ session('status') }}</flux:callout>
    @endif

    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('messages.admin.search_placeholder') }}" />
    </div>

    <flux:table>
        <flux:columns>
            <flux:column>{{ __('messages.admin.col_name') }}</flux:column>
            <flux:column>{{ __('messages.admin.col_email') }}</flux:column>
            <flux:column>{{ __('messages.admin.col_roles') }}</flux:column>
            <flux:column>{{ __('messages.admin.col_actions') }}</flux:column>
        </flux:columns>
        <flux:rows>
            @foreach ($users as $user)
                <flux:row>
                    <flux:cell>{{ $user->name }}</flux:cell>
                    <flux:cell>{{ $user->email }}</flux:cell>
                    <flux:cell>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($user->roles as $role)
                                <flux:badge size="sm" variant="solid" color="{{ $role->name === $superAdminRole ? 'red' : 'zinc' }}">
                                    {{ $role->name }}
                                </flux:badge>
                            @endforeach
                        </div>
                    </flux:cell>
                    <flux:cell>
                        <flux:select wire:change="assignRole({{ $user->id }}, $event.target.value)" size="sm">
                            <flux:select.option value="">{{ __('messages.admin.select_role') }}</flux:select.option>
                            @foreach ($roles as $role)
                                <flux:select.option value="{{ $role->name }}" @selected($user->hasRole($role->name))>
                                    {{ $role->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:cell>
                </flux:row>
            @endforeach
        </flux:rows>
    </flux:table>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
