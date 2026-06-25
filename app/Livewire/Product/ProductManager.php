<?php

namespace App\Livewire\Product;

use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
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
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->category_id = $product->category_id;
        $this->name_bn = $product->name_bn;
        $this->name_en = $product->name_en;
        $this->stock = $product->stock;
        $this->isEditMode = true;
    }

    public function delete($id)
    {
        Product::findOrFail($id)->delete();
        Flux::toast('প্রোডাক্ট মুছে ফেলা হয়েছে!');
    }

    public function resetFields()
    {
        $this->reset(['productId', 'category_id', 'name_bn', 'name_en', 'stock', 'isEditMode']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.product.product-manager', [
            // প্রোডাক্টের সাথে ক্যাটাগরির নাম দেখানোর জন্য with('category') ব্যবহার করা হয়েছে
            'products' => Product::with('category')->latest()->paginate(10),
            'categories' => Category::orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'Product Manager']);
    }
}
