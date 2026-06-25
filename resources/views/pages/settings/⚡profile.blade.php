<?php

use App\Concerns\ProfileValidationRules;
/* @chisel-email-verification */
use Illuminate\Contracts\Auth\MustVerifyEmail;
/* @end-chisel-email-verification */
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads; // ফাইল আপলোডের জন্য যুক্ত করা হলো

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;
    use WithFileUploads; // ফাইল আপলোডের ট্রেইট

    public string $name = '';
    public string $email = '';

    // নতুন প্রোপার্টিসমূহ
    public ?string $mobile_no = '';
    public $digital_signature; // ফাইল আপলোডের জন্য
    public ?string $current_signature = null; // বর্তমান সিগনেচার দেখানোর জন্য

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->mobile_no = Auth::user()->mobile_no;
        $this->current_signature = Auth::user()->digital_signature;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        // ডিফল্ট ভ্যালিডেশন (নাম ও ইমেইল)
        $validated = $this->validate($this->profileRules($user->id));

        // কাস্টম ফিল্ড ভ্যালিডেশন
        $customValidated = $this->validate([
            'mobile_no' => ['required', 'string', 'max:20'],
            'digital_signature' => ['nullable', 'image', 'max:2048'], // সর্বোচ্চ ২ মেগাবাইট
        ]);

        $user->fill($validated);
        $user->mobile_no = $customValidated['mobile_no'];

        // সিগনেচার আপলোড লজিক
        if ($this->digital_signature) {
            // যদি আগে থেকে কোনো সিগনেচার থাকে, তবে স্টোরেজ বাঁচানোর জন্য সেটি ডিলিট করা
            if ($user->digital_signature && Storage::disk('public')->exists($user->digital_signature)) {
                Storage::disk('public')->delete($user->digital_signature);
            }

            // নতুন সিগনেচার সেভ করা
            $path = $this->digital_signature->store('signatures', 'public');
            $user->digital_signature = $path;
            $this->current_signature = $path;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile and signature updated successfully.'));

        // ফাইল ইনপুট ক্লিয়ার করা
        $this->reset('digital_signature');
    }

    /* @chisel-email-verification */
    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
    /* @end-chisel-email-verification */
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile and Signature')" :subheading="__('Update your name, email, mobile number, and digital signature')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">

            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                {{-- @chisel-email-verification --}}
                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
                {{-- @end-chisel-email-verification --}}
            </div>

            <flux:input wire:model="mobile_no" :label="__('Mobile Number')" type="text" required />

            <div class="space-y-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('Digital Signature') }}</flux:heading>
                <p class="text-sm text-zinc-500">{{ __('This signature will be used on printed copies for approval (try using an image with a white background).') }}</p>

                @if($current_signature)
                    <div class="mb-4">
                        <p class="text-sm font-medium mb-2 text-zinc-700 dark:text-zinc-300">{{ __('Current Signature:') }}</p>
                        <div class="p-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg inline-block">
                            <img src="{{ asset('storage/' . $current_signature) }}" alt="Signature" class="h-16 object-contain mix-blend-multiply dark:mix-blend-normal dark:bg-white dark:p-1">
                        </div>
                    </div>
                @endif

                <flux:input type="file" wire:model="digital_signature" :label="__('Upload New Signature (Optional)')" accept="image/*" />

                <div wire:loading wire:target="digital_signature" class="text-sm text-indigo-600 mt-1">{{ __('Uploading image, please wait...') }}</div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Update (Save)') }}
                    </flux:button>
                </div>
            </div>
        </form>

        {{-- @chisel-email-verification --}}
        @if ($this->showDeleteUser)
            {{-- @end-chisel-email-verification --}}
            <livewire:pages::settings.delete-user-form />
            {{-- @chisel-email-verification --}}
        @endif
        {{-- @end-chisel-email-verification --}}
    </x-pages::settings.layout>
</section>
