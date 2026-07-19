<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RequisitionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string'],
            'mine' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $query = $this->visibleRequisitions($user)
            ->with(['user.department', 'user.designation', 'items.product.category']);

        if (($validated['mine'] ?? false) === true || $user->role === 'requisitioner') {
            $query->where('user_id', $user->id);
        }

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function (Builder $query) use ($search): void {
                $query->where('requisition_no', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function (Builder $query) use ($search): void {
                        $query->where('name', 'like', '%'.$search.'%')
                            ->orWhere('pf_no', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('items.product', function (Builder $query) use ($search): void {
                        $query->where('name_bn', 'like', '%'.$search.'%')
                            ->orWhere('name_en', 'like', '%'.$search.'%');
                    });
            });
        }

        return response()->json([
            'data' => $query->latest()->paginate($validated['per_page'] ?? 25),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.demanded_qty' => ['required', 'integer', 'min:1'],
            'items.*.purpose' => ['required', Rule::exists('purposes', 'name')->where('is_active', true)],
        ]);

        /** @var User $user */
        $user = $request->user();

        $requisition = DB::transaction(function () use ($validated, $user): Requisition {
            $requisition = Requisition::query()->create([
                'requisition_no' => 'REQ-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
                'user_id' => $user->id,
                'status' => Requisition::initialStatus($user->department_id),
                'approval_history' => [],
            ]);

            foreach ($validated['items'] as $item) {
                $requisition->items()->create([
                    'product_id' => $item['product_id'],
                    'demanded_qty' => $item['demanded_qty'],
                    'supplied_qty' => 0,
                    'purpose' => $item['purpose'],
                ]);
            }

            return $requisition;
        });

        return response()->json([
            'message' => 'Requisition submitted successfully.',
            'data' => $requisition->load(['user.department', 'user.designation', 'items.product.category']),
        ], 201);
    }

    public function show(Request $request, Requisition $requisition): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($this->visibleRequisitions($user)->whereKey($requisition->id)->exists(), 404);

        return response()->json([
            'data' => $requisition->load(['user.department', 'user.designation', 'items.product.category']),
        ]);
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
}
