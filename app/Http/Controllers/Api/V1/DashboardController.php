<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\User;
use App\Support\ApprovalWorkflow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trend_filter' => ['nullable', Rule::in(['7', '15', '30', 'all'])],
        ]);

        /** @var User $user */
        $user = $request->user();
        $role = $user->role;
        $trendFilter = $validated['trend_filter'] ?? '30';
        $trendData = $this->trendData($user, $trendFilter);

        return response()->json([
            'data' => [
                'role' => $role,
                'stats' => $this->stats($user),
                'charts' => [
                    'requisition_trends' => [
                        'labels' => $trendData['labels'],
                        'values' => $trendData['values'],
                    ],
                    'category_inventory' => [
                        'labels' => Category::query()->pluck('name'),
                        'values' => Category::query()->withCount('products')->pluck('products_count'),
                    ],
                ],
                'recent_requisitions' => $this->requisitionsForUserDepartment($user)
                    ->with(['user.department'])
                    ->latest()
                    ->take(5)
                    ->get(),
                'my_own_requisitions' => Requisition::query()
                    ->where('user_id', $user->id)
                    ->latest()
                    ->take(5)
                    ->get(),
            ],
        ]);
    }

    /**
     * @return array<string, int>
     */
    protected function stats(User $user): array
    {
        $role = $user->role;
        $stats = [];

        if (in_array($role, ['admin', 'super_admin', 'director'], true)) {
            $stats['total_users'] = User::query()->count();
            $stats['pending_users'] = User::query()->where('is_approved', false)->count();
            $stats['total_products'] = Product::query()->count();
            $stats['low_stock'] = Product::query()->where('stock', '<=', 10)->count();
        }

        if ($role === 'requisitioner') {
            $stats['total_submitted'] = Requisition::query()->where('user_id', $user->id)->count();
            $stats['pending'] = Requisition::query()->where('user_id', $user->id)->where('status', '!=', 'distributed')->count();
            $stats['distributed'] = Requisition::query()->where('user_id', $user->id)->where('status', 'distributed')->count();
            $stats['returned'] = Requisition::query()->where('user_id', $user->id)->where('status', 'returned')->count();
        }

        if (in_array($role, ['initiator', 'assistant_director', 'deputy_director', 'director'], true)) {
            $queueStatuses = $this->queueStatuses($role);
            $stats['pending_action'] = $this->requisitionsForUserDepartment($user)->whereIn('status', ['pending', 'returned'])->count();
            $stats['ready_to_print'] = $this->requisitionsForUserDepartment($user)->whereIn('status', ['director_approved', 'distributed'])->count();
            $stats['pending_approval'] = $this->requisitionsForUserDepartment($user)->whereIn('status', $queueStatuses)->count();
            $stats['total_requisitions'] = $this->requisitionsForUserDepartment($user)->count();

            if ($role === 'initiator') {
                $stats['stock_out_products'] = Product::query()->where('stock', '<=', 0)->count();
            }
        }

        return $stats;
    }

    /**
     * @return array<int, string>
     */
    protected function queueStatuses(string $role): array
    {
        if ($role === 'initiator') {
            return ['pending', 'returned', 'director_approved', 'distributed'];
        }

        $status = ApprovalWorkflow::statusForRole($role);

        return $status ? [$status] : [];
    }

    /**
     * @return Builder<Requisition>
     */
    protected function requisitionsForUserDepartment(User $user): Builder
    {
        $query = Requisition::query();

        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            return $query;
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

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    protected function trendData(User $user, string $trendFilter): array
    {
        if ($trendFilter === 'all') {
            $requisitions = $this->requisitionsForUserDepartment($user)
                ->select('id', 'created_at')
                ->orderBy('created_at')
                ->get();

            $trendData = $requisitions
                ->groupBy(fn (Requisition $requisition): string => $requisition->created_at->format('Y-m'))
                ->map(fn ($group): int => $group->count());

            $labels = [];
            $values = [];

            foreach ($trendData as $month => $total) {
                $labels[] = Carbon::parse($month.'-01')->format('M Y');
                $values[] = $total;
            }

            return ['labels' => $labels, 'values' => $values];
        }

        $days = (int) $trendFilter;
        $startDate = now()->subDays($days - 1)->startOfDay();
        $trendData = $this->requisitionsForUserDepartment($user)
            ->select('id', 'created_at')
            ->where('created_at', '>=', $startDate)
            ->get()
            ->groupBy(fn (Requisition $requisition): string => $requisition->created_at->format('Y-m-d'))
            ->map(fn ($group): int => $group->count());

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d M');
            $values[] = $trendData->get($date, 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
