<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purposes', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        DB::table('purposes')->insert([
            ['name' => 'Official Use', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Training Purpose', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        if (Schema::hasTable('requisition_items') && DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE requisition_items MODIFY purpose VARCHAR(255) NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('requisition_items') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE requisition_items MODIFY purpose ENUM('Training Purpose', 'Official Use') NOT NULL");
        }

        Schema::dropIfExists('purposes');
    }
};
