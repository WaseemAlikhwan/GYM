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
        Schema::create('system_achievements', function (Blueprint $table) {
            $table->id();
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
            $table->boolean('is_active')->default(true); // هل الإنجاز نشط
            $table->integer('required_value')->nullable(); // القيمة المطلوبة (مثل عدد مرات الحضور)
            $table->string('unit')->nullable(); // الوحدة (مرات، كيلوغرام، إلخ)
            $table->timestamps();
            
            // Indexes
            $table->index('type');
            $table->index('level');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_achievements');
    }
};

