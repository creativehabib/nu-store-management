<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        // ১. ভ্যালিডেশন রুলস আপডেট করা হলো
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'pf_no' => ['required', 'string', 'max:255', 'unique:users,pf_no'],
            'mobile_no' => ['required', 'string', 'max:20'],
            'post' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:director,deputy_director,assistant_director,initiator,requisitioner'],
            'digital_signature' => ['image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
        ])->validate();

        // ২. ডিজিটাল সিগনেচার ফাইল সেভ করার লজিক
        $signaturePath = null;
        if (isset($input['digital_signature']) && request()->hasFile('digital_signature')) {
            $signaturePath = request()->file('digital_signature')->store('signatures', 'public');
        }

        // ৩. ডাটাবেসে ইউজার তৈরি করা
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'pf_no' => $input['pf_no'],
            'mobile_no' => $input['mobile_no'],
            'post' => $input['post'],
            'department' => $input['department'],
            'role' => $input['role'],
            'digital_signature' => $signaturePath,
            'is_approved' => false,
        ]);
    }
}
