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
        'approval_history',
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

    public static function initialStatus(?int $requestingDepartmentId = null): string
    {
        if (setting('store_mode', 'departmental') === 'centralized') {
            if ((int) $requestingDepartmentId === (int) setting('central_store_dept_id', 1)) {
                return 'pending';
            }

            return 'department_director_review';
        }

        return 'pending';
    }

    public function scopeForUserDepartment($query)
    {
        $user = auth()->user();

        // ১. অ্যাডমিন বা সুপার অ্যাডমিন হলে সব দপ্তরের রিকুইজিশন দেখতে পারবে
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return $query;
        }

        $storeMode = setting('store_mode', 'departmental');
        $centralStoreId = setting('central_store_dept_id', 1);

        // নির্দিষ্ট স্টোর রোলগুলো সংজ্ঞায়িত করা হলো
        $storeRoles = ['initiator', 'assistant_director', 'deputy_director', 'director'];

        // ২. সেন্ট্রাল মোড হলে এবং ইউজার সেন্ট্রাল স্টোরের নির্দিষ্ট কর্মকর্তা হলে সব দেখতে পারবে
        if ($storeMode === 'centralized' &&
            $user->department_id == $centralStoreId &&
            in_array($user->role, $storeRoles)) {

            return $query; // কোনো ফিল্টার ছাড়া সব দপ্তরের ডাটা রিটার্ন করবে
        }

        // ৩. সাধারণ ইউজার বা departmental মোডের ক্ষেত্রে শুধুমাত্র নিজ দপ্তরের ডাটা
        return $query->whereHas('user', function ($q) use ($user) {
            $q->where('department_id', $user->department_id);
        });
    }
}
