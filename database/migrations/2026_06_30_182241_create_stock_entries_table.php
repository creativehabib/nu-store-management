<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity'); // কতটুকু স্টক ইন হলো
            $table->string('voucher_no')->nullable(); // মেমো বা ভাউচার নম্বর
            $table->string('supplier')->nullable(); // সরবরাহকারী প্রতিষ্ঠান/ব্যক্তি
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_entries');
    }
};
