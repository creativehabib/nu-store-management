<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // ১. অ্যাডমিনের জন্য একটি ডিফল্ট ডিপার্টমেন্ট তৈরি করা
        $department = Department::firstOrCreate(
            ['code' => 'CS-ADMIN'],
            ['name' => 'Central Store Administration']
        );

        // ২. অ্যাডমিনের জন্য একটি সর্বোচ্চ পদবী তৈরি করা
        $designation = Designation::firstOrCreate(
            ['title' => 'Super Admin'],
            ['rank' => 100] // র‍্যাংক ১০০ দিলাম যাতে সবার উপরে থাকে
        );

        // ৩. সুপার অ্যাডমিন ইউজার তৈরি করা
        User::updateOrCreate(
            ['email' => 'admin@nu.ac.bd'], // লগইন ইমেইল
            [
                'name' => 'System Administrator',
                'pf_no' => 'NU-ADMIN-01',
                'mobile_no' => '01700000000',
                'password' => Hash::make('Admin@12345'),
                'role' => 'super_admin',
                'department_id' => $department->id,
                'designation_id' => $designation->id,
                'is_approved' => true, // অ্যাডমিন অটোমেটিক এপ্রুভড থাকবে
            ]
        );

        $this->command->info('Super Admin created successfully!');
    }
}
