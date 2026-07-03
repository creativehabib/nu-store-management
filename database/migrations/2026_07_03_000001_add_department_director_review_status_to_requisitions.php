<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE requisitions MODIFY status ENUM('pending', 'department_director_review', 'initiator_checked', 'ad_approved', 'dd_approved', 'director_approved', 'returned', 'distributed') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('requisitions')
                ->where('status', 'department_director_review')
                ->update(['status' => 'pending']);

            DB::statement("ALTER TABLE requisitions MODIFY status ENUM('pending', 'initiator_checked', 'ad_approved', 'dd_approved', 'director_approved', 'returned', 'distributed') DEFAULT 'pending'");
        }
    }
};
