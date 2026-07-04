<?php

namespace App\Livewire\Product;

use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProductManager extends Component
{
    use WithPagination;

    public $productId;

    public $category_id;

    public $name_bn;

    public $name_en;

    public $stock = 0;

    public $isEditMode = false;
    public $productToDelete = null;

    public bool $lowStockOnly = false;

    public bool $stockOutOnly = false;

    public function mount(): void
    {
        $this->lowStockOnly = request()->boolean('low_stock');
        $this->stockOutOnly = request()->boolean('stock_out') || ! $this->canManageProducts();
    }

    public function rules()
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name_bn' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
        ];
    }

    public function save()
    {
        abort_unless($this->canManageProducts(), 403);

        $this->validate();

        Product::updateOrCreate(
            ['id' => $this->productId],
            [
                'category_id' => $this->category_id,
                'name_bn' => $this->name_bn,
                'name_en' => $this->name_en,
                'stock' => $this->stock,
            ]
        );

        Flux::toast($this->isEditMode ? 'প্রোডাক্ট সফলভাবে আপডেট হয়েছে!' : 'নতুন প্রোডাক্ট যুক্ত হয়েছে!');
        $this->resetFields();
    }

    public function edit($id)
    {
        abort_unless($this->canManageProducts(), 403);

        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->category_id = $product->category_id;
        $this->name_bn = $product->name_bn;
        $this->name_en = $product->name_en;
        $this->stock = $product->stock;
        $this->isEditMode = true;
    }

    // ডিলিট বাটনের পরিবর্তে এটি ব্যবহার করুন
    public function confirmDelete($id): void
    {
        abort_unless($this->canManageProducts(), 403);

        $this->productToDelete = $id;
        Flux::modal('delete-produt-modal')->show();
    }

    public function executeDelete(): void
    {
        abort_unless($this->canManageProducts(), 403);

        if ($this->productToDelete) {
            Product::findOrFail($this->productToDelete)->delete();
            Flux::toast('প্রোডাক্ট মুছে ফেলা হয়েছে!');
            $this->productToDelete = null;
            Flux::modal('delete-produt-modal')->close();
        }
    }

    public function resetFields()
    {
        $this->reset(['productId', 'category_id', 'name_bn', 'name_en', 'stock', 'isEditMode']);
        $this->resetValidation();
    }

    public function clearLowStockFilter(): void
    {
        $this->lowStockOnly = false;
        $this->resetPage();
    }

    public function clearStockOutFilter(): void
    {
        abort_unless($this->canManageProducts(), 403);

        $this->stockOutOnly = false;
        $this->resetPage();
    }

    public function canManageProducts(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public function render()
    {
        $products = Product::with('category')
            ->when($this->lowStockOnly, function ($query) {
                $query->where('stock', '<=', 10);
            })
            ->when($this->stockOutOnly, function ($query) {
                $query->where('stock', '<=', 0);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.product.product-manager', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'canManageProducts' => $this->canManageProducts(),
        ])->layout('layouts.app', ['title' => 'Product Manager']);
    }
}
