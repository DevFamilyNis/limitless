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

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="'Početna'" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        @lang('messages.menu.dashboard')
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="'Klijenti i projekti'" class="grid">
                    <flux:sidebar.item icon="users" :href="route('clients.index')" :current="request()->routeIs('clients.*')" wire:navigate>
                        @lang('messages.menu.clients')
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="layout-grid" :href="route('projects.index')" :current="request()->routeIs('projects.*')" wire:navigate>
                        @lang('messages.menu.projects')
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="receipt-percent" :href="route('client-project-rates.index')" :current="request()->routeIs('client-project-rates.*')" wire:navigate>
                        @lang('messages.menu.projectsPrice')
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="'Finansije'" class="grid">
                    <flux:sidebar.item icon="document-text" :href="route('invoices.index')" :current="request()->routeIs('invoices.*')" wire:navigate>
                       @lang('messages.menu.invoices')
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrows-right-left" :href="route('transactions.index')" :current="request()->routeIs('transactions.*')" wire:navigate>
                       Prihodi
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('paid-expenses.index')" :current="request()->routeIs('paid-expenses.*')" wire:navigate>
                       Rashodi
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="route('monthly-expenses.index')" :current="request()->routeIs('monthly-expenses.*')" wire:navigate>
                       Spisak mesečnih obaveza
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="tag" :href="route('categories.index')" :current="request()->routeIs('categories.*')" wire:navigate>
                       @lang('messages.menu.categories')
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar-days" :href="route('tax-years.index')" :current="request()->routeIs('tax-years.*')" wire:navigate>
                       @lang('messages.menu.tax')
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="'Izveštaji'" class="grid">
                    <flux:sidebar.item icon="document-text" :href="route('kpo-reports.index')" :current="request()->routeIs('kpo-reports.*')" wire:navigate>
                      @lang('messages.menu.kpo')
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="'Operativa'" class="grid">
                    <flux:sidebar.item icon="layout-grid" :href="route('issues.index')" :current="request()->routeIs('issues.*')" wire:navigate>
                        @lang('messages.menu.tasks')
                    </flux:sidebar.item>
                </flux:sidebar.group>
                <flux:sidebar.group :heading="'Podešavanja'" class="grid">
                    <flux:sidebar.item icon="bars-2" :href="route('settings.issue-statuses.index')" :current="request()->routeIs('settings.issue-statuses.*')" wire:navigate>
                        @lang('messages.menu.statuses')
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="receipt-percent" :href="route('settings.issue-priorities.index')" :current="request()->routeIs('settings.issue-priorities.*')" wire:navigate>
                        @lang('messages.menu.priorities')
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="tag" :href="route('settings.issue-categories.index')" :current="request()->routeIs('settings.issue-categories.*')" wire:navigate>
                        @lang('messages.menu.catIssues')
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

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
                    </flux:menu>
                </flux:dropdown>
            @endauth
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
