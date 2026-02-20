<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('messages.form.title')" :description="__('messages.form.subTitle')
        " />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('messages.form.email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('messages.form.password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('messages.form.forgotPassword') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('messages.form.rememberMe')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('messages.form.submit') }}
                </flux:button>
            </div>
        </form>

        @if (Route::has('magic-login.send'))
            <div class="pt-2 border-t border-zinc-200 dark:border-zinc-700">
                <p class="mb-3 text-sm text-center text-zinc-600 dark:text-zinc-400">
                    {{ __('messages.form.magicLoginTitle') }}
                </p>

                <form method="POST" action="{{ route('magic-login.send') }}" class="flex flex-col gap-4">
                    @csrf

                    <flux:input
                        name="magic_email"
                        :label="__('messages.form.magicLoginEmail')"
                        :value="old('magic_email')"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"
                    />

                    <div class="flex items-center justify-end">
                        <flux:button variant="primary" type="submit" class="w-full">
                            {{ __('messages.form.magicLoginSubmit') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-layouts::auth>
