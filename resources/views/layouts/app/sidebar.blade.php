<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            @php
                $clientProjectOpen = request()->routeIs('leads.*') || request()->routeIs('clients.*') || request()->routeIs('projects.*') || request()->routeIs('client-project-rates.*') || request()->routeIs('contracts.*');
                $financeOpen = request()->routeIs('invoices.*') || request()->routeIs('transactions.*') || request()->routeIs('paid-expenses.*') || request()->routeIs('monthly-expenses.*') || request()->routeIs('categories.*') || request()->routeIs('tax-years.*');
                $reportsOpen = request()->routeIs('kpo-reports.*');
                $settingsOpen = request()->routeIs('settings.issue-statuses.*') || request()->routeIs('settings.issue-priorities.*') || request()->routeIs('settings.issue-categories.*');
            @endphp

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="'Početna'" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        @lang('messages.menu.dashboard')
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="'Brzi linkovi'" class="grid">
                    <flux:sidebar.item icon="layout-grid" :href="route('issues.index')" :current="request()->routeIs('issues.*')" wire:navigate>
                        @lang('messages.menu.tasks')
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('clients.index')" :current="request()->routeIs('clients.*')" wire:navigate>
                        @lang('messages.menu.clients')
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="briefcase" :href="route('leads.index')" :current="request()->routeIs('leads.*')" wire:navigate>
                        @lang('messages.menu.leads')
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="route('invoices.index')" :current="request()->routeIs('invoices.*')" wire:navigate>
                        @lang('messages.menu.invoices')
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <div x-data="{ open: @js($clientProjectOpen) }">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm font-medium text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        <span class="flex items-center gap-2">
{{--                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">--}}
{{--                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />--}}
{{--                            </svg>--}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1
                            .5" stroke="currentColor" class="size-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                            </svg>

                            @lang('messages.menu.clients_projects')
                        </span>
                        <svg class="size-4 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="mt-1 space-y-1">
                        <flux:sidebar.item class="ps-9" icon="briefcase" :href="route('leads.index')" :current="request()->routeIs('leads.*')" wire:navigate>
                            @lang('messages.menu.leads')
                        </flux:sidebar.item>
                        <flux:sidebar.item class="ps-9" icon="users" :href="route('clients.index')" :current="request()->routeIs('clients.*')" wire:navigate>
                            @lang('messages.menu.clients')
                        </flux:sidebar.item>
                        <flux:sidebar.item class="ps-9" icon="layout-grid" :href="route('projects.index')" :current="request()->routeIs('projects.*')" wire:navigate>
                            @lang('messages.menu.projects')
                        </flux:sidebar.item>
                        <flux:sidebar.item class="ps-9" icon="receipt-percent" :href="route('client-project-rates.index')" :current="request()->routeIs('client-project-rates.*')" wire:navigate>
                            @lang('messages.menu.projectsPrice')
                        </flux:sidebar.item>
                        <flux:sidebar.item class="ps-9" icon="document-check" :href="route('contracts.index')" :current="request()->routeIs('contracts.*')" wire:navigate>
                            @lang('messages.menu.contracts')
                        </flux:sidebar.item>
                    </div>
                </div>

                <div x-data="{ open: @js($financeOpen) }">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm font-medium text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        <span class="flex items-center gap-2">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                            </svg>
                            @lang('messages.menu.finance')
                        </span>
                        <svg class="size-4 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="mt-1 space-y-1">
                        <flux:sidebar.item class="ps-9" icon="document-text" :href="route('invoices.index')" :current="request()->routeIs('invoices.*')" wire:navigate>
                            @lang('messages.menu.invoices')
                        </flux:sidebar.item>
                        <flux:sidebar.item class="ps-9" icon="arrows-right-left" :href="route('transactions.index')" :current="request()->routeIs('transactions.*')" wire:navigate>
                            Prihodi
                        </flux:sidebar.item>
                        <flux:sidebar.item class="ps-9" icon="banknotes" :href="route('paid-expenses.index')" :current="request()->routeIs('paid-expenses.*')" wire:navigate>
                            Rashodi
                        </flux:sidebar.item>
                        <flux:sidebar.item class="ps-9" icon="document-text" :href="route('monthly-expenses.index')" :current="request()->routeIs('monthly-expenses.*')" wire:navigate>
                            Spisak mesečnih obaveza
                        </flux:sidebar.item>
                        <flux:sidebar.item class="ps-9" icon="arrow-trending-up" :href="route('monthly-incomes.index')" :current="request()->routeIs('monthly-incomes.*')" wire:navigate>
                            @lang('messages.menu.monthly_incomes')
                        </flux:sidebar.item>
                        <flux:sidebar.item class="ps-9" icon="tag" :href="route('categories.index')" :current="request()->routeIs('categories.*')" wire:navigate>
                            @lang('messages.menu.categories')
                        </flux:sidebar.item>
                        <flux:sidebar.item class="ps-9" icon="calendar-days" :href="route('tax-years.index')" :current="request()->routeIs('tax-years.*')" wire:navigate>
                            @lang('messages.menu.tax')
                        </flux:sidebar.item>
                    </div>
                </div>

                <div x-data="{ open: @js($reportsOpen) }">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm font-medium text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        <span class="flex items-center gap-2">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m7.875 14.25 1.214 1.942a2.25 2.25 0 0 0 1.908 1.058h2.006c.776 0 1.497-.4 1.908-1.058l1.214-1.942M2.41 9h4.636a2.25 2.25 0 0 1 1.872 1.002l.164.246a2.25 2.25 0 0 0 1.872 1.002h2.092a2.25 2.25 0 0 0 1.872-1.002l.164-.246A2.25 2.25 0 0 1 16.954 9h4.636M2.41 9a2.25 2.25 0 0 0-.16.832V12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 12V9.832c0-.287-.055-.57-.16-.832M2.41 9a2.25 2.25 0 0 1 .382-.632l3.285-3.832a2.25 2.25 0 0 1 1.708-.786h8.43c.657 0 1.281.287 1.709.786l3.284 3.832c.163.19.291.404.382.632M4.5 20.25h15A2.25 2.25 0 0 0 21.75 18v-2.625c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125V18a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            @lang('messages.menu.reports')
                        </span>
                        <svg class="size-4 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="mt-1 space-y-1">
                        <flux:sidebar.item class="ps-9" icon="document-text" :href="route('kpo-reports.index')" :current="request()->routeIs('kpo-reports.*')" wire:navigate>
                            @lang('messages.menu.kpo')
                        </flux:sidebar.item>
                    </div>
                </div>

                <div x-data="{ open: @js($settingsOpen) }">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm font-medium text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        <span class="flex items-center gap-2">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            @lang('messages.menu.settings')
                        </span>
                        <svg class="size-4 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="mt-1 space-y-1">
                        <flux:sidebar.item class="ps-9" icon="bars-2" :href="route('settings.issue-statuses.index')" :current="request()->routeIs('settings.issue-statuses.*')" wire:navigate>
                            @lang('messages.menu.statuses')
                        </flux:sidebar.item>
                        <flux:sidebar.item class="ps-9" icon="receipt-percent" :href="route('settings.issue-priorities.index')" :current="request()->routeIs('settings.issue-priorities.*')" wire:navigate>
                            @lang('messages.menu.priorities')
                        </flux:sidebar.item>
                        <flux:sidebar.item class="ps-9" icon="tag" :href="route('settings.issue-categories.index')" :current="request()->routeIs('settings.issue-categories.*')" wire:navigate>
                            @lang('messages.menu.catIssues')
                        </flux:sidebar.item>
                    </div>
                </div>
            </flux:sidebar.nav>

            @can('manage-users')
                @php
                    $adminUsersIcon = new \Illuminate\Support\HtmlString('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" /></svg>');
                    $adminRolesIcon = new \Illuminate\Support\HtmlString('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>');
                @endphp
                <flux:sidebar.group :heading="'Admin'" class="grid">
                    <flux:sidebar.item :icon="$adminUsersIcon" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>
                        Korisnici
                    </flux:sidebar.item>
                    @can('manage-roles')
                        <flux:sidebar.item :icon="$adminRolesIcon" :href="route('admin.roles.index')" :current="request()->routeIs('admin.roles.*')" wire:navigate>
                            Role i permisije
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
            @endcan

            <flux:spacer />


{{--            <flux:sidebar.nav>--}}
{{--                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">--}}
{{--                    {{ __('Repository') }}--}}
{{--                </flux:sidebar.item>--}}

{{--                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">--}}
{{--                    {{ __('Documentation') }}--}}
{{--                </flux:sidebar.item>--}}
{{--            </flux:sidebar.nav>--}}

            @auth
                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            @endauth
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            @auth
                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <flux:avatar
                                        :name="auth()->user()->name"
                                        :initials="auth()->user()->initials()"
                                    />

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                        <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                                {{ __('messages.menu.settings') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item
                                as="button"
                                type="submit"
                                icon="arrow-right-start-on-rectangle"
                                class="w-full cursor-pointer"
                                data-test="logout-button"
                            >
                                {{ __('messages.menu.logOut') }}
                            </flux:menu.item>
                        </form>

                        <livewire:work-sessions.finish-work-session-button />
                    </flux:menu>
                </flux:dropdown>
            @endauth
        </flux:header>

        {{ $slot }}

        @auth
            <livewire:work-sessions.start-work-session-modal />
        @endauth

        @fluxScripts
    </body>
</html>
