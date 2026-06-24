<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requisition extends Model
{
    protected $fillable = [
        'requisition_no',
        'user_id',
        'status',
        'approval_history'
    ];

    // JSON ডাটাকে Array হিসেবে ব্যবহারের জন্য কাস্টিং
    protected function casts(): array
    {
        return [
            'approval_history' => 'array',
        ];
    }

    // রিকুইজিশনটি কোন ইউজার তৈরি করেছেন
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // একটি রিকুইজিশনে একাধিক আইটেম থাকতে পারে
    public function items(): HasMany
    {
        return $this->hasMany(RequisitionItem::class);
    }
}
