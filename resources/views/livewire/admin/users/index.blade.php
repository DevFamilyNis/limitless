<div class="flex h-full w-full flex-1 flex-col gap-6">

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">{{ __('messages.admin.users_title') }}</flux:heading>
            <flux:subheading>{{ __('messages.admin.users_subtitle') }}</flux:subheading>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
    @endif

    {{-- Invite form --}}
    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800/50">
        <flux:heading size="lg" class="mb-4">{{ __('messages.admin.invite_heading') }}</flux:heading>
        <form wire:submit.prevent="inviteUser" class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                <flux:field>
                    <flux:label>{{ __('messages.admin.invite_name') }}</flux:label>
                    <flux:input wire:model="inviteName" placeholder="Ime i prezime" />
                    <flux:error name="inviteName" />
                </flux:field>
            </div>
            <div class="flex-1">
                <flux:field>
                    <flux:label>{{ __('messages.admin.invite_email') }}</flux:label>
                    <flux:input wire:model="inviteEmail" type="email" placeholder="email@primer.com" />
                    <flux:error name="inviteEmail" />
                </flux:field>
            </div>
            <div class="w-44">
                <flux:field>
                    <flux:label>{{ __('messages.admin.invite_role') }}</flux:label>
                    <flux:select wire:model="inviteRole">
                        @foreach ($roles as $role)
                            <flux:select.option value="{{ $role->name }}">{{ $role->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="inviteRole" />
                </flux:field>
            </div>
            <div class="pb-0.5">
                <flux:button type="submit" variant="primary">{{ __('messages.admin.invite_button') }}</flux:button>
            </div>
        </form>
    </div>

    {{-- Search --}}
    <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('messages.admin.search_placeholder') }}" />

    {{-- User table --}}
    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>{{ __('messages.admin.col_name') }}</x-ui.table.th>
                <x-ui.table.th>{{ __('messages.admin.col_email') }}</x-ui.table.th>
                <x-ui.table.th>{{ __('messages.admin.col_roles') }}</x-ui.table.th>
                <x-ui.table.th align="right">{{ __('messages.admin.col_actions') }}</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @forelse ($users as $user)
                <x-ui.table.row wire:key="user-{{ $user->id }}">
                    <x-ui.table.td>
                        <div class="font-medium">{{ $user->name }}</div>
                    </x-ui.table.td>
                    <x-ui.table.td>
                        <span class="text-zinc-500 dark:text-zinc-400">{{ $user->email }}</span>
                    </x-ui.table.td>
                    <x-ui.table.td>
                        <div class="flex flex-wrap gap-1">
                            @forelse ($user->roles as $role)
                                <flux:badge size="sm" variant="solid" color="{{ $role->name === $superAdminRole ? 'red' : 'zinc' }}">
                                    {{ $role->name }}
                                </flux:badge>
                            @empty
                                <flux:badge size="sm" color="amber">bez role</flux:badge>
                            @endforelse
                        </div>
                    </x-ui.table.td>
                    <x-ui.table.td align="right">
                        <div class="flex items-center justify-end gap-2">
                            @if ($user->id !== auth()->id())
                                <select
                                    wire:change="assignRole({{ $user->id }}, $event.target.value)"
                                    class="rounded-md border border-zinc-300 bg-white px-2 py-1 text-sm text-zinc-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200"
                                >
                                    <option value="">{{ __('messages.admin.select_role') }}</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    x-on:click="if(confirm('{{ __('messages.admin.confirm_delete_user', ['name' => addslashes($user->name)]) }}')) $wire.deleteUser({{ $user->id }})"
                                >
                                    {{ __('messages.admin.delete_user') }}
                                </flux:button>
                            @else
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">sopstveni nalog</span>
                            @endif
                        </div>
                    </x-ui.table.td>
                </x-ui.table.row>
            @empty
                <x-ui.table.empty colspan="4">Nema korisnika.</x-ui.table.empty>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>
        {{ $users->links() }}
    </div>

</div>
