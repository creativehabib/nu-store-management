<?php

use App\Models\Product;
use App\Models\Requisition;
use App\Notifications\LowStockNotification;
use App\Notifications\RequisitionStatusChanged;

it('builds the low stock mail notification with product details', function () {
    $product = new Product([
        'name_bn' => 'টেস্ট পণ্য',
        'name_en' => 'Test Product',
        'stock' => 3,
    ]);

    $mail = (new LowStockNotification($product))->toMail((object) []);

    expect($mail->subject)->toBe(__('Low Stock Alert!'))
        ->and($mail->introLines)->toContain(__('Product :name is running low on stock. Current stock: :qty', [
            'name' => 'টেস্ট পণ্য',
            'qty' => 3,
        ]));
});

it('builds the requisition status changed mail notification with requisition details', function () {
    $requisition = new Requisition([
        'requisition_no' => 'REQ-1001',
    ]);

    $mail = (new RequisitionStatusChanged($requisition))->toMail((object) []);

    expect($mail->subject)->toBe(__('Requisition Status Updated'))
        ->and($mail->introLines)->toContain(__('Your requisition (:no) has been updated.', [
            'no' => 'REQ-1001',
        ]));
});
