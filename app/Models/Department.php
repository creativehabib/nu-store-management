<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $guarded = [];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function getApprovingDepartmentId($applicantDeptId): int
    {
        // আপনার SettingManager ব্যবহার করে ডাটা আনা হচ্ছে
        $storeMode = setting('store_mode', 'departmental');

        if ($storeMode === 'centralized') {
            // সেন্ট্রাল মোড হলে সেন্ট্রাল স্টোরের আইডি রিটার্ন করবে
            return (int) setting('central_store_dept_id', 1);
        }

        // সাধারণ মোড হলে আবেদনকারীর নিজ দপ্তরের আইডি রিটার্ন করবে
        return $applicantDeptId;
    }
}
