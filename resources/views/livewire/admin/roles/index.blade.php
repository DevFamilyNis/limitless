<div class="flex h-full w-full flex-1 flex-col gap-6">

    <div>
        <flux:heading size="xl">Role i permisije</flux:heading>
        <flux:subheading>Upravljajte rolama i dodeljenim permisijama.</flux:subheading>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
    @endif

    {{-- Create new role --}}
    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800/50" x-data="{ open: false }">
        <button
            type="button"
            @click="open = !open"
            class="flex w-full items-center justify-between text-left"
        >
            <flux:heading size="lg">Nova rola</flux:heading>
            <svg class="size-5 text-zinc-400 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        <div x-show="open" x-collapse class="mt-4">
            <div class="mb-4">
                <flux:field>
                    <flux:label>Naziv role</flux:label>
                    <flux:input wire:model="newRoleName" placeholder="npr. manager, editor..." class="max-w-xs" />
                    <flux:error name="newRoleName" />
                </flux:field>
            </div>

            <div class="mb-4">
                <p class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Permisije</p>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($permissionGroups as $groupName => $permissions)
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ $groupName }}</p>
                            <div class="space-y-1">
                                @foreach ($permissions as $permKey => $permLabel)
                                    <label class="flex cursor-pointer items-center gap-2 rounded p-1 hover:bg-zinc-100 dark:hover:bg-zinc-700/50">
                                        <input
                                            type="checkbox"
                                            value="{{ $permKey }}"
                                            wire:model="newRolePermissions"
                                            class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700"
                                        />
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $permLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <flux:button wire:click="createRole" variant="primary">Kreiraj rolu</flux:button>
        </div>
    </div>

    {{-- Existing roles --}}
    <div class="space-y-3">
        @foreach ($roles as $role)
            <div
                class="rounded-lg border bg-white dark:bg-zinc-900 {{ $role->name === $superAdminRole ? 'border-red-200 dark:border-red-900' : 'border-zinc-200 dark:border-zinc-700' }}"
                x-data="{ open: {{ $role->name !== $superAdminRole ? 'false' : 'false' }} }"
            >
                {{-- Role header --}}
                <div class="flex items-center justify-between p-4">
                    <button type="button" @click="open = !open" class="flex flex-1 items-center gap-3 text-left">
                        <flux:badge
                            size="sm"
                            variant="solid"
                            color="{{ $role->name === $superAdminRole ? 'red' : 'zinc' }}"
                        >
                            {{ $role->name }}
                        </flux:badge>

                        @if ($role->name === $superAdminRole)
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">Sve permisije (Gate::before bypass)</span>
                        @else
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $role->permissions->count() }} / {{ collect($permissionGroups)->flatten()->count() }} permisija
                                @if ($role->users_count > 0)
                                    · {{ $role->users_count }} {{ $role->users_count === 1 ? 'korisnik' : 'korisnika' }}
                                @endif
                            </span>
                        @endif

                        <svg class="ml-auto size-4 text-zinc-400 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    @php($isSystemRole = in_array($role->name, array_column(\App\Enums\RoleKey::cases(), 'value')))
                    @if (!$isSystemRole)
                        <flux:button
                            size="sm"
                            variant="danger"
                            class="ml-3"
                            x-on:click="if(confirm('Obrisati rolu?')) $wire.deleteRole({{ $role->id }})"
                        >
                            Obriši
                        </flux:button>
                    @endif
                </div>

                {{-- Permissions grid --}}
                <div x-show="open" x-collapse>
                    <div class="border-t border-zinc-100 p-4 dark:border-zinc-700/50">
                        @if ($role->name === $superAdminRole)
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Super-admin zaobilazi sve permisije putem <code class="rounded bg-zinc-100 px-1 py-0.5 text-xs dark:bg-zinc-800">Gate::before</code>.
                                Individualne permisije nisu relevantne za ovu rolu.
                            </p>
                        @else
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                @foreach ($permissionGroups as $groupName => $permissions)
                                    <div>
                                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ $groupName }}</p>
                                        <div class="space-y-1">
                                            @foreach ($permissions as $permKey => $permLabel)
                                                <label class="flex cursor-pointer items-center gap-2 rounded p-1 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                                    <input
                                                        type="checkbox"
                                                        {{ $role->hasPermissionTo($permKey) ? 'checked' : '' }}
                                                        wire:change="togglePermission({{ $role->id }}, '{{ $permKey }}')"
                                                        class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800"
                                                    />
                                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $permLabel }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
