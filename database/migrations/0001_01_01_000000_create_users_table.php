<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('pf_no')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('locale', 10)->default('en');
            $table->string('mobile_no')->unique();

            // নতুন ফরেন-কি কলামগুলো (স্ট্রিং কলামগুলোর পরিবর্তে)
            // nullable() রাখা হলো যাতে সেন্ট্রাল অ্যাডমিনের ক্ষেত্রে এগুলো খালি রাখা যায়
            $table->foreignId('department_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained()->restrictOnDelete();

            $table->string('password');
            $table->string('picture')->nullable();
            $table->string('digital_signature')->nullable();

            // Roles & Permissions (super_admin যোগ করা হলো)
            $table->enum('role', [
                'super_admin', 'admin', 'director', 'deputy_director',
                'assistant_director', 'initiator', 'requisitioner',
            ]);
            $table->boolean('is_approved')->default(false);

            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
