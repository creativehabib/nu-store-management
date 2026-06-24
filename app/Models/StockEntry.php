<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockEntry extends Model
{
    protected $fillable = ['product_id', 'quantity', 'voucher_no', 'supplier'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
