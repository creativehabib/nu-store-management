<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Product;
use App\Models\Purpose;
use App\Models\Requisition;
use App\Models\StockEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryDataController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'departments' => Department::query()->orderBy('name')->get(),
                'designations' => Designation::query()->orderBy('rank')->orderBy('title')->get(),
                'categories' => Category::query()->orderBy('name')->get(),
                'products' => Product::query()->with('category')->orderBy('name_en')->get(),
                'purposes' => Purpose::query()->orderBy('name')->get(),
                'stock_entries' => StockEntry::query()->with('product')->latest()->limit(100)->get(),
                'requisitions' => Requisition::query()->with(['user', 'items.product'])->latest()->limit(100)->get(),
            ],
        ]);
    }

    public function categories(): JsonResponse
    {
        return response()->json(['data' => Category::query()->orderBy('name')->get()]);
    }

    public function departments(): JsonResponse
    {
        return response()->json(['data' => Department::query()->orderBy('name')->get()]);
    }

    public function designations(): JsonResponse
    {
        return response()->json(['data' => Designation::query()->orderBy('rank')->orderBy('title')->get()]);
    }

    public function products(): JsonResponse
    {
        return response()->json(['data' => Product::query()->with('category')->orderBy('name_en')->get()]);
    }

    public function purposes(): JsonResponse
    {
        return response()->json(['data' => Purpose::query()->orderBy('name')->get()]);
    }

    public function requisitions(): JsonResponse
    {
        return response()->json(['data' => Requisition::query()->with(['user', 'items.product'])->latest()->paginate(25)]);
    }

    public function myRequisitions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
                'pending',
                'returned',
                'department_director_review',
                'initiator_checked',
                'ad_approved',
                'dd_approved',
                'director_approved',
                'distributed',
            ])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = $validated['search'] ?? '';
        $status = $validated['status'] ?? '';
        $perPage = $validated['per_page'] ?? 10;

        $requisitions = Requisition::query()
            ->where('user_id', $request->user()?->id)
            ->with(['items.product'])
            ->when($status !== '', function ($query) use ($status): void {
                $query->where('status', $status);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($requisitionQuery) use ($search): void {
                    $requisitionQuery->where('requisition_no', 'like', '%'.$search.'%')
                        ->orWhereHas('items.product', function ($productQuery) use ($search): void {
                            $productQuery->where('name_bn', 'like', '%'.$search.'%')
                                ->orWhere('name_en', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate($perPage);

        return response()->json(['data' => $requisitions]);
    }

    public function stockEntries(): JsonResponse
    {
        return response()->json(['data' => StockEntry::query()->with('product')->latest()->paginate(25)]);
    }
}
