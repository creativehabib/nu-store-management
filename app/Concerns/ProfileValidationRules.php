<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'pf_no' => $this->pfNoRules($userId),
            'mobile_no' => $this->mobileNoRules($userId),
            'designation_id' => ['required', 'integer', Rule::exists('designations', 'id')],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'role' => ['required', 'string', Rule::in([
                'super_admin',
                'admin',
                'director',
                'deputy_director',
                'assistant_director',
                'initiator',
                'requisitioner',
            ])],
            'digital_signature' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user PF numbers.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function pfNoRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'max:255',
            $userId === null
                ? Rule::unique(User::class, 'pf_no')
                : Rule::unique(User::class, 'pf_no')->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user mobile numbers.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function mobileNoRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'max:20',
            $userId === null
                ? Rule::unique(User::class, 'mobile_no')
                : Rule::unique(User::class, 'mobile_no')->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
