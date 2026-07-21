<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\User;
use App\Support\ApprovalWorkflow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkflowController extends Controller
{
    public function counts(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => [
                'initiator' => $this->initiatorQueueCount($user),
                'approval' => $this->approvalQueueCount($user),
            ],
        ]);
    }

    public function initiatorQueue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'returned', 'director_approved', 'distributed'])],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        /** @var User $user */
        $user = $request->user();

        abort_unless($user->role === 'initiator', 403);

        $query = $this->visibleRequisitions($user)
            ->with(['user.department', 'user.designation', 'items.product.category'])
            ->whereIn('status', ['pending', 'returned', 'director_approved', 'distributed']);

        if (setting('store_mode', 'departmental') === 'centralized'
            && (int) $user->department_id !== (int) setting('central_store_dept_id', 1)) {
            $query->whereRaw('1 = 0');
        }

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $this->applySearch($query, $validated['search'] ?? null, true);

        return response()->json([
            'data' => $query->latest()->paginate($validated['per_page'] ?? 25),
        ]);
    }

    public function approvalQueue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $status = $this->approvalStatusFor($user);

        abort_unless($status !== null, 403);

        $query = $this->visibleRequisitions($user)
            ->with(['user.department', 'user.designation', 'items.product.category'])
            ->where('status', $status);

        $this->applySearch($query, $validated['search'] ?? null);

        if (! empty($validated['start_date']) && ! empty($validated['end_date'])) {
            $query->whereDate('created_at', '>=', $validated['start_date'])
                ->whereDate('created_at', '<=', $validated['end_date']);
        }

        if (! empty($validated['department_id'])) {
            $query->whereHas('user', function (Builder $query) use ($validated): void {
                $query->where('department_id', $validated['department_id']);
            });
        }

        return response()->json([
            'data' => $query->latest()->paginate($validated['per_page'] ?? 25),
            'meta' => [
                'waiting_status' => $status,
            ],
        ]);
    }

    public function forward(Request $request, Requisition $requisition): JsonResponse
    {
        $validated = $this->validateWorkflowPayload($request);

        /** @var User $user */
        $user = $request->user();

        abort_unless($user->role === 'initiator', 403);
        abort_unless($this->visibleRequisitions($user)->whereKey($requisition->id)->exists(), 404);
        abort_unless(in_array($requisition->status, ['pending', 'returned'], true), 422, 'Only pending or returned requisitions can be forwarded.');

        $requisition = $this->loadWorkflowRequisition($requisition);

        DB::transaction(function () use ($requisition, $validated, $user): void {
            $this->updateSuppliedQuantities($requisition, $validated['supplied_quantities'] ?? []);
            $this->appendHistory($requisition, $user, 'forwarded', $validated['comment'] ?? null);

            $requisition->update([
                'status' => ApprovalWorkflow::firstStatus(),
            ]);
        });

        return response()->json([
            'message' => 'Requisition forwarded successfully.',
            'data' => $requisition->fresh(['user.department', 'user.designation', 'items.product.category']),
        ]);
    }

    public function approve(Request $request, Requisition $requisition): JsonResponse
    {
        $validated = $this->validateWorkflowPayload($request);

        /** @var User $user */
        $user = $request->user();
        $status = $this->approvalStatusFor($user);

        abort_unless($status !== null, 403);
        abort_unless($this->visibleRequisitions($user)->whereKey($requisition->id)->exists(), 404);
        abort_unless($requisition->status === $status, 422, 'This requisition is not waiting for your approval.');

        $requisition = $this->loadWorkflowRequisition($requisition);
        $nextStatus = $user->role === 'director' && $requisition->status === 'department_director_review'
            ? 'pending'
            : ApprovalWorkflow::nextStatusAfter($user->role);

        DB::transaction(function () use ($requisition, $validated, $user, $nextStatus): void {
            $this->updateSuppliedQuantities($requisition, $validated['supplied_quantities'] ?? []);
            $this->appendHistory($requisition, $user, 'approved', $validated['comment'] ?? null);

            $requisition->update(['status' => $nextStatus]);
        });

        return response()->json([
            'message' => 'Requisition approved successfully.',
            'data' => $requisition->fresh(['user.department', 'user.designation', 'items.product.category']),
        ]);
    }

    public function return(Request $request, Requisition $requisition): JsonResponse
    {
        $validated = $this->validateWorkflowPayload($request);

        /** @var User $user */
        $user = $request->user();
        $status = $this->approvalStatusFor($user);

        abort_unless($status !== null, 403);
        abort_unless($this->visibleRequisitions($user)->whereKey($requisition->id)->exists(), 404);
        abort_unless($requisition->status === $status, 422, 'This requisition is not waiting for your review.');

        $requisition = $this->loadWorkflowRequisition($requisition);

        DB::transaction(function () use ($requisition, $validated, $user): void {
            $this->updateSuppliedQuantities($requisition, $validated['supplied_quantities'] ?? []);
            $this->appendHistory($requisition, $user, 'return', $validated['comment'] ?? null);

            $requisition->update(['status' => 'returned']);
        });

        return response()->json([
            'message' => 'Requisition returned successfully.',
            'data' => $requisition->fresh(['user.department', 'user.designation', 'items.product.category']),
        ]);
    }

    public function distribute(Request $request, Requisition $requisition): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless(in_array($user->role, ['admin', 'super_admin', 'initiator'], true), 403);
        abort_unless($this->visibleRequisitions($user)->whereKey($requisition->id)->exists(), 404);
        abort_unless($requisition->status === 'director_approved', 422, 'Only director approved requisitions can be distributed.');

        $requisition = $this->loadWorkflowRequisition($requisition);

        DB::transaction(function () use ($requisition): void {
            foreach ($requisition->items as $item) {
                Product::query()
                    ->whereKey($item->product_id)
                    ->where('stock', '>=', $item->supplied_qty)
                    ->decrement('stock', $item->supplied_qty);
            }

            $requisition->update(['status' => 'distributed']);
        });

        return response()->json([
            'message' => 'Requisition distributed successfully.',
            'data' => $requisition->fresh(['user.department', 'user.designation', 'items.product.category']),
        ]);
    }

    protected function initiatorQueueCount(User $user): int
    {
        if ($user->role !== 'initiator') {
            return 0;
        }

        if (setting('store_mode', 'departmental') === 'centralized'
            && (int) $user->department_id !== (int) setting('central_store_dept_id', 1)) {
            return 0;
        }

        return $this->visibleRequisitions($user)
            ->whereIn('status', ['pending', 'returned', 'director_approved'])
            ->count();
    }

    protected function approvalQueueCount(User $user): int
    {
        $status = $this->approvalStatusFor($user);

        if ($status === null) {
            return 0;
        }

        return $this->visibleRequisitions($user)
            ->where('status', $status)
            ->count();
    }

    /**
     * @return array{comment?: string|null, supplied_quantities?: array<int|string, int>}
     */
    protected function validateWorkflowPayload(Request $request): array
    {
        return $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
            'supplied_quantities' => ['nullable', 'array'],
            'supplied_quantities.*' => ['integer', 'min:0'],
        ]);
    }

    protected function approvalStatusFor(User $user): ?string
    {
        $isDepartmentDirectorReview = setting('store_mode', 'departmental') === 'centralized'
            && (int) $user->department_id !== (int) setting('central_store_dept_id', 1);

        return ApprovalWorkflow::statusForRole($user->role, $isDepartmentDirectorReview);
    }

    /**
     * @return Builder<Requisition>
     */
    protected function visibleRequisitions(User $user): Builder
    {
        $query = Requisition::query();

        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            return $query;
        }

        if ($user->role === 'requisitioner') {
            return $query->where('user_id', $user->id);
        }

        $storeMode = setting('store_mode', 'departmental');
        $centralStoreId = setting('central_store_dept_id', 1);
        $storeRoles = ['initiator', 'assistant_director', 'deputy_director', 'director'];

        if ($storeMode === 'centralized'
            && (int) $user->department_id === (int) $centralStoreId
            && in_array($user->role, $storeRoles, true)) {
            return $query;
        }

        return $query->whereHas('user', function (Builder $query) use ($user): void {
            $query->where('department_id', $user->department_id);
        });
    }

    protected function applySearch(Builder $query, ?string $search, bool $includeDepartment = false): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $query->where(function (Builder $query) use ($search, $includeDepartment): void {
            $query->where('requisition_no', 'like', '%'.$search.'%')
                ->orWhereHas('user', function (Builder $query) use ($search, $includeDepartment): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('pf_no', 'like', '%'.$search.'%');

                    if ($includeDepartment) {
                        $query->orWhereHas('department', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', '%'.$search.'%');
                        });
                    }
                })
                ->orWhereHas('items.product', function (Builder $query) use ($search): void {
                    $query->where('name_bn', 'like', '%'.$search.'%')
                        ->orWhere('name_en', 'like', '%'.$search.'%');
                });
        });
    }

    /**
     * @param  array<int|string, int>  $suppliedQuantities
     */
    protected function updateSuppliedQuantities(Requisition $requisition, array $suppliedQuantities): void
    {
        foreach ($requisition->items as $item) {
            $quantity = null;

            if (array_key_exists($item->id, $suppliedQuantities)) {
                $quantity = $suppliedQuantities[$item->id];
            } elseif (array_key_exists((string) $item->id, $suppliedQuantities)) {
                $quantity = $suppliedQuantities[(string) $item->id];
            } elseif (array_key_exists($item->product_id, $suppliedQuantities)) {
                $quantity = $suppliedQuantities[$item->product_id];
            } elseif (array_key_exists((string) $item->product_id, $suppliedQuantities)) {
                $quantity = $suppliedQuantities[(string) $item->product_id];
            }

            if ($quantity !== null) {
                $item->update([
                    'supplied_qty' => (int) $quantity,
                ]);
            }
        }
    }

    protected function appendHistory(Requisition $requisition, User $user, string $action, ?string $comment): void
    {
        $history = $requisition->approval_history ?? [];
        $history[] = [
            'role' => $user->role,
            'name' => $user->name,
            'action' => $action,
            'comment' => $comment,
            'date' => now()->toDateTimeString(),
            'designation' => $user->designation?->title,
            'signature' => $user->digital_signature,
        ];

        $requisition->approval_history = $history;
        $requisition->save();
    }

    protected function loadWorkflowRequisition(Requisition $requisition): Requisition
    {
        return $requisition->load(['user.department', 'user.designation', 'items.product.category']);
    }
}
