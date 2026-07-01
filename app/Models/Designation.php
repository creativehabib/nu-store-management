<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Designation extends Model
{
    protected $guarded = [];

    // একটি পদবীতে অনেক ইউজার থাকতে পারে
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
