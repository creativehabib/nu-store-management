<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
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

        // 2. Categories & Products Seeding
        $categories = [
            'Stationery & Office Supplies' => [
                ['অফসেট কাগজ A4', 'Offset Paper A4'],
                ['অফসেট কাগজ A7', 'Offset Paper A7'],
                ['বলপেন (কালো)', 'Ball Pen (Black)'],
                ['বলপেন (লাল)', 'Ball Pen (Red)'],
                ['রেজিস্টার খাতা নম্বর ৪', 'Register Book No. 4'],
                ['স্ট্যাপলার (ছোট)', 'Stapler (Small)'],
                ['পাঞ্চ মেশিন (সিঙ্গেল)', 'Punch Machine (Single)'],
                ['ক্যালকুলেটর', 'Calculator'],
                ['খাকি ইনভেলাপ খাম NU লোগোসহ', 'Khaki Envelope with NU Logo'],
                ['সাদা সুতার গুটি', 'White Thread Roll'],
            ],
            'Electronics & IT Items' => [
                ['Desktop কম্পিউটার', 'Desktop Computer'],
                ['Laptop কম্পিউটার', 'Laptop Computer'],
                ['লেজার প্রিন্টার', 'Laser Printer'],
                ['ফটোকপিয়ার মেশিন Sharp-NU', 'Sharp Photocopier Machine (NU)'],
                ['লেজার প্রিন্টার টোনার 76A (Black)', 'Laser Printer Toner 76A (Black)'],
                ['ওয়াইফাই রাউটার', 'WiFi Router'],
            ],
            'Cleaning & Toiletries Items' => [
                ['ফেসিয়াল টিস্যু', 'Facial Tissue'],
                ['হ্যান্ডওয়াশ ২৫০ গ্রাম', 'Hand Wash 250 gm'],
                ['স্যাভলন লিকুইড', 'Savlon Liquid'],
                ['এয়ার ফ্রেশনার', 'Air Freshener'],
            ],
            'Crockery & Kitchen Items' => [
                ['সিরামিক প্লেট', 'Ceramic Plate'],
                ['সিরামিক কাপ-পিরিচ (VIP)', 'Ceramic Cup & Saucer (VIP)'],
                ['চা চামচ', 'Tea Spoon'],
                ['কাঁচের গ্লাস', 'Glass Tumbler'],
            ]
        ];

        foreach ($categories as $catName => $products) {
            $category = Category::create(['name' => $catName]);

            foreach ($products as $product) {
                Product::create([
                    'category_id' => $category->id,
                    'name_bn' => $product[0],
                    'name_en' => $product[1],
                    'stock' => 100 // Default Initial Stock
                ]);
            }
        }
    }
}
