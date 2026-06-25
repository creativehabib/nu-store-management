<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;

class ProductObserver
{
    public function updated(Product $product)
    {
        // যদি স্টক ৫ বা তার কম হয়
        if ($product->stock <= 5 && $product->stock > $product->getOriginal('stock') - 1) {
            $admins = User::whereIn('role', ['admin', 'initiator'])->get();
            Notification::send($admins, new LowStockNotification($product));
        }
    }
}
