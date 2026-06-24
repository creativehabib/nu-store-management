<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name'];

    // একটি ক্যাটাগরির অধীনে অনেক প্রোডাক্ট থাকতে পারে
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
