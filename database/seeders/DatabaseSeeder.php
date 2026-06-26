<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Auto Generated Account
        User::create([
            'pf_no' => '2115',
            'password' => Hash::make('123456'),
            'name' => 'Md. Meherajul Islam',
            'post' => 'Section Officer',
            'department' => 'Office of the Teachers Training',
            'mobile_no' => '01716150100',
            'email' => 'meherajulislam@nu.ac.bd',
            'role' => 'admin',
            'is_approved' => true,
        ]);
    }
}
