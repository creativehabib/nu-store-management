<?php

use App\Livewire\Product\ProductManager;
use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

it('shows only low stock products when opened from the dashboard low stock card', function () {
    $category = Category::create(['name' => 'Stationery']);

    Product::create([
        'category_id' => $category->id,
        'name_bn' => 'Low Stock Paper',
        'name_en' => 'Low Stock Paper',
        'stock' => 10,
    ]);

    Product::create([
        'category_id' => $category->id,
        'name_bn' => 'Healthy Stock Pen',
        'name_en' => 'Healthy Stock Pen',
        'stock' => 11,
    ]);

    Livewire::withQueryParams(['low_stock' => 1])
        ->test(ProductManager::class)
        ->assertSet('lowStockOnly', true)
        ->assertSee('Low Stock Paper')
        ->assertDontSee('Healthy Stock Pen');
});
