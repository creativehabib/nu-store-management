<?php

use App\Livewire\Dashboard\DashboardStats;
use App\Livewire\Product\ProductManager;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

function productVisibilityUser(string $role, string $pfNo, string $mobileNo): User
{
    return User::factory()->create([
        'role' => $role,
        'pf_no' => $pfNo,
        'mobile_no' => $mobileNo,
        'is_approved' => true,
    ]);
}

it('shows stock out products on the initiator dashboard', function () {
    $category = Category::create(['name' => 'Stationery']);
    Product::create([
        'category_id' => $category->id,
        'name_bn' => 'শেষ পণ্য',
        'name_en' => 'Out Product',
        'stock' => 0,
    ]);

    $this->actingAs(productVisibilityUser('initiator', 'INIT-STOCK-001', '01700002001'));

    Livewire::test(DashboardStats::class)
        ->assertSee('Stock Out Products')
        ->assertSee('1');
});

it('allows initiators to view only stock out products without management actions', function () {
    $category = Category::create(['name' => 'Stationery']);
    Product::create([
        'category_id' => $category->id,
        'name_bn' => 'শেষ পণ্য',
        'name_en' => 'Out Product',
        'stock' => 0,
    ]);
    Product::create([
        'category_id' => $category->id,
        'name_bn' => 'মজুদ পণ্য',
        'name_en' => 'Available Product',
        'stock' => 15,
    ]);

    $this->actingAs(productVisibilityUser('initiator', 'INIT-STOCK-002', '01700002002'));

    Livewire::test(ProductManager::class)
        ->assertSee('Out Product')
        ->assertDontSee('Available Product')
        ->assertDontSee('Initial Stock')
        ->assertDontSee('Action')
        ->assertSee('only admins can add, edit, or delete products');
});

it('prevents initiators from calling product management actions directly', function () {
    $category = Category::create(['name' => 'Stationery']);
    $product = Product::create([
        'category_id' => $category->id,
        'name_bn' => 'শেষ পণ্য',
        'name_en' => 'Out Product',
        'stock' => 0,
    ]);

    $this->actingAs(productVisibilityUser('initiator', 'INIT-STOCK-003', '01700002003'));

    expect(fn () => Livewire::test(ProductManager::class)->call('edit', $product->id))
        ->toThrow(HttpException::class);
});

it('keeps full product management available for admins', function () {
    $category = Category::create(['name' => 'Stationery']);
    Product::create([
        'category_id' => $category->id,
        'name_bn' => 'শেষ পণ্য',
        'name_en' => 'Out Product',
        'stock' => 0,
    ]);

    $this->actingAs(productVisibilityUser('admin', 'ADMIN-STOCK-001', '01700002004'));

    Livewire::withQueryParams(['stock_out' => 1])
        ->test(ProductManager::class)
        ->assertSee('Out Product')
        ->assertSee('Initial Stock')
        ->assertSee('Action');
});
