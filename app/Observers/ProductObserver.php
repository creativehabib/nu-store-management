<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Notification;

class ProductObserver
{
    public function updated(Product $product): void
    {
        if ($product->wasChanged('stock')) {
            AuditLogger::record(
                'inventory.stock_changed',
                __('Product :name stock changed from :old to :new.', [
                    'name' => $product->name_bn,
                    'old' => $product->getOriginal('stock'),
                    'new' => $product->stock,
                ]),
                $product,
                ['stock' => $product->getOriginal('stock')],
                ['stock' => $product->stock],
            );
        }

        // যদি স্টক ৫ বা তার কম হয়
        if ($product->stock <= 5 && $product->stock > $product->getOriginal('stock') - 1) {
            $admins = User::whereIn('role', ['admin', 'initiator'])->get();
            Notification::send($admins, new LowStockNotification($product));
        }
    }
}
