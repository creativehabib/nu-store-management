<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['category_id', 'name_bn', 'name_en', 'stock'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function requisitionItems(): HasMany
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequisitionItem::class);
    }
}
