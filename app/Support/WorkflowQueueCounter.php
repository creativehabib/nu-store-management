<?php

namespace App\Support;

use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class WorkflowQueueCounter
{
    /**
     * @return array{initiator: int, approval: int}
     */
    public function countsFor(?User $user): array
    {
        if (! $user || ! Schema::hasTable('requisitions')) {
            return ['initiator' => 0, 'approval' => 0];
        }

        return [
            'initiator' => $this->initiatorCount($user),
            'approval' => $this->approvalCount($user),
        ];
    }

    private function initiatorCount(User $user): int
    {
        if ($user->role !== 'initiator') {
            return 0;
        }

        if (setting('store_mode', 'departmental') === 'centralized'
            && (int) $user->department_id !== (int) setting('central_store_dept_id', 1)) {
            return 0;
        }

        return Requisition::forUserDepartment()
            ->whereIn('status', ['pending', 'returned', 'director_approved'])
            ->count();
    }

    private function approvalCount(User $user): int
    {
        $status = $this->approvalStatusFor($user);

        if (! $status) {
            return 0;
        }

        return Requisition::forUserDepartment()
            ->where('status', $status)
            ->count();
    }

    private function approvalStatusFor(User $user): ?string
    {
        $isDepartmentDirectorReview = setting('store_mode', 'departmental') === 'centralized'
            && (int) $user->department_id !== (int) setting('central_store_dept_id', 1);

        return ApprovalWorkflow::statusForRole($user->role, $isDepartmentDirectorReview);
    }
}
