<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class ApprovalWorkflow
{
    /**
     * @return array<string, string>
     */
    public static function availableApprovers(): array
    {
        return [
            'assistant_director' => 'Assistant Director',
            'deputy_director' => 'Deputy Director',
            'director' => 'Director',
        ];
    }

    /**
     * @return list<string>
     */
    public static function roles(): array
    {
        $roles = ['assistant_director', 'deputy_director', 'director'];

        if (is_installed() && Schema::hasTable('settings')) {
            $savedRoles = Setting::where('key', 'approval_flow_roles')->value('value');

            if ($savedRoles !== null) {
                $decodedRoles = json_decode($savedRoles, true);
                $roles = json_last_error() === JSON_ERROR_NONE ? $decodedRoles : [$savedRoles];
            }
        }

        return is_array($roles) ? self::normalizeRoles($roles) : ['director'];
    }

    /**
     * @param  array<int, string>  $selectedRoles
     * @return list<string>
     */
    public static function rolesFromSelection(array $selectedRoles): array
    {
        return self::normalizeRoles($selectedRoles);
    }

    /**
     * @param  array<int, mixed>  $roles
     * @return list<string>
     */
    public static function normalizeRoles(array $roles): array
    {
        $selectedRoles = array_filter($roles, fn ($role): bool => is_string($role));
        $normalizedRoles = [];

        foreach (array_keys(self::availableApprovers()) as $role) {
            if ($role !== 'director' && in_array($role, $selectedRoles, true)) {
                $normalizedRoles[] = $role;
            }
        }

        $normalizedRoles[] = 'director';

        return $normalizedRoles;
    }

    public static function firstStatus(): string
    {
        return self::statusWaitingFor(self::roles()[0]);
    }

    public static function statusWaitingFor(string $role): string
    {
        return match ($role) {
            'assistant_director' => 'initiator_checked',
            'deputy_director' => 'ad_approved',
            'director' => 'dd_approved',
            default => 'dd_approved',
        };
    }

    public static function statusForRole(string $role, bool $isDepartmentDirectorReview = false): ?string
    {
        if ($isDepartmentDirectorReview && $role === 'director') {
            return 'department_director_review';
        }

        return in_array($role, self::roles(), true) ? self::statusWaitingFor($role) : null;
    }

    public static function nextStatusAfter(string $role): string
    {
        $roles = self::roles();
        $index = array_search($role, $roles, true);

        if ($index === false) {
            return 'director_approved';
        }

        $nextRole = $roles[$index + 1] ?? null;

        return $nextRole ? self::statusWaitingFor($nextRole) : 'director_approved';
    }

    public static function roleWaitingForStatus(string $status): ?string
    {
        foreach (self::roles() as $role) {
            if (self::statusWaitingFor($role) === $status) {
                return $role;
            }
        }

        return null;
    }
}
