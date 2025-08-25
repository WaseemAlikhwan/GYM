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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title'); // عنوان الإنجاز
            $table->text('description')->nullable(); // وصف الإنجاز
            $table->enum('type', [
                'attendance',      // إنجازات الحضور
                'weight_loss',     // فقدان الوزن
                'weight_gain',     // زيادة الوزن
                'muscle_gain',     // بناء العضلات
                'endurance',       // التحمل
                'strength',        // القوة
                'flexibility',     // المرونة
                'consistency',     // الانتظام
                'milestone',       // معالم مهمة
                'special'          // إنجازات خاصة
            ]);
            $table->enum('level', ['bronze', 'silver', 'gold', 'platinum', 'diamond'])->default('bronze');
            $table->integer('points')->default(0); // نقاط الإنجاز
            $table->string('icon')->nullable(); // أيقونة الإنجاز
            $table->string('badge_image')->nullable(); // صورة الشارة
            $table->json('criteria')->nullable(); // معايير الحصول على الإنجاز
            $table->boolean('is_unlocked')->default(false); // هل تم فتح الإنجاز
            $table->timestamp('unlocked_at')->nullable(); // تاريخ فتح الإنجاز
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'is_unlocked']);
            $table->index('level');
            $table->index('points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};

