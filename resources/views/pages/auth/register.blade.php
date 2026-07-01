<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Create your account in the Store Requisition System')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="flex flex-col gap-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input name="name" :label="__('Name')" :value="old('name')" type="text" required autofocus autocomplete="name" placeholder="Full name" />

                <flux:input name="pf_no" :label="__('PF No (ID)')" :value="old('pf_no')" type="text" required placeholder="Ex: 2115" />

                <flux:select name="designation_id" :label="__('Designation')" required>
                    <flux:select.option value="">{{ __('Select Designation') }}</flux:select.option>
                    @foreach(\App\Models\Designation::all() as $desig)
                        <flux:select.option value="{{ $desig->id }}">{{ $desig->title }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select name="department_id" :label="__('Department')" required>
                    <flux:select.option value="">{{ __('Select Department') }}</flux:select.option>
                    @foreach(\App\Models\Department::all() as $dept)
                        <flux:select.option value="{{ $dept->id }}">{{ $dept->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input name="mobile_no" :label="__('Mobile No')" :value="old('mobile_no')" type="text" required placeholder="017XXXXXXXX" />

                <flux:input name="email" :label="__('Email address')" :value="old('email')" type="email" required autocomplete="email" placeholder="email@example.com" />
            </div>

            <flux:select name="role" :label="__('User Role')" required>
                <flux:select.option value="requisitioner" selected>{{ __('Requisitioner') }}</flux:select.option>
                <flux:select.option value="initiator">{{ __('Initiator') }}</flux:select.option>
                <flux:select.option value="assistant_director">{{ __('Assistant Director') }}</flux:select.option>
                <flux:select.option value="deputy_director">{{ __('Deputy Director') }}</flux:select.option>
                <flux:select.option value="director">{{ __('Director') }}</flux:select.option>
            </flux:select>

            <flux:input name="digital_signature" :label="__('Digital Signature (Image)')" type="file" accept="image/*" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input name="password" :label="__('Password')" type="password" required autocomplete="new-password" placeholder="Password" viewable />
                <flux:input name="password_confirmation" :label="__('Confirm password')" type="password" required autocomplete="new-password" placeholder="Confirm password" viewable />
            </div>

            <div class="flex items-center justify-end mt-2">
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
