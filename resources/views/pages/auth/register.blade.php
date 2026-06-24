<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('স্টোর রিকুইজিশন সিস্টেমে আপনার অ্যাকাউন্ট তৈরি করুন')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="flex flex-col gap-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input name="name" :label="__('Name')" :value="old('name')" type="text" required autofocus autocomplete="name" :placeholder="__('Full name')" />

                <flux:input name="pf_no" label="PF No (ID)" :value="old('pf_no')" type="text" required placeholder="Ex: 2115" />

                <flux:input name="post" label="Post" :value="old('post')" type="text" required placeholder="Ex: Administrative Officer" />

                <flux:input name="department" label="Department" :value="old('department')" type="text" required placeholder="Ex: National University" />

                <flux:input name="mobile_no" label="Mobile No" :value="old('mobile_no')" type="text" required placeholder="017XXXXXXXX" />

                <flux:input name="email" :label="__('Email address')" :value="old('email')" type="email" required autocomplete="email" placeholder="email@example.com" />
            </div>

            <flux:select name="role" label="User Role" required>
                <flux:select.option value="requisitioner" selected>Requisitioner</flux:select.option>
                <flux:select.option value="initiator">Initiator</flux:select.option>
                <flux:select.option value="assistant_director">Assistant Director</flux:select.option>
                <flux:select.option value="deputy_director">Deputy Director</flux:select.option>
                <flux:select.option value="director">Director</flux:select.option>
                <flux:select.option value="adin">Initiator</flux:select.option>
            </flux:select>

            <flux:input name="digital_signature" label="Digital Signature (Image)" type="file" required accept="image/*" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input name="password" :label="__('Password')" type="password" required autocomplete="new-password" :placeholder="__('Password')" passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}" viewable />

                <flux:input name="password_confirmation" :label="__('Confirm password')" type="password" required autocomplete="new-password" :placeholder="__('Confirm password')" passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}" viewable />
            </div>

            <div class="flex items-center justify-end mt-2">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
