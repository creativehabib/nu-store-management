<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisitionItem extends Model
{
    protected $fillable = [
        'requisition_id',
        'product_id',
        'demanded_qty',
        'supplied_qty',
        'purpose',
    ];

    // এটি কোন রিকুইজিশন মাস্টার ডাটার আন্ডারে
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    // আইটেমটি কোন প্রোডাক্ট
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
