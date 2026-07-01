<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_no')->unique(); // System Auto Fill
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // যিনি চাহিদা দিয়েছেন

            // রিকুইজিশন ফ্লো এর স্ট্যাটাস
            $table->enum('status', [
                'pending',             // Initiator-এর কিউতে
                'initiator_checked',   // AD-এর কিউতে
                'ad_approved',         // DD-এর কিউতে
                'dd_approved',         // Director-এর কিউতে
                'director_approved',   // অনুমোদিত (প্রিন্ট এবং স্টক মাইনাসের জন্য প্রস্তুত)
                'returned',            // যদি কেউ ব্যাক করে
                'distributed'          // স্টক মাইনাস করে দেওয়া হয়েছে
            ])->default('pending');

            // Server Table-এ কমেন্টস এবং অ্যাপ্রুভাল হিস্ট্রি রাখার জন্য JSON কলাম
            $table->json('approval_history')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
