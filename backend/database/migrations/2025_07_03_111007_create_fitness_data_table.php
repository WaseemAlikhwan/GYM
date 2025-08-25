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
        Schema::create('fitness_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->float('weight');
            $table->float('height');
            $table->float('bmi')->nullable(); // يجب تلقائي
            $table->float('fat_percent')->nullable(); // نسبة الدهون إذا متوفرة
            $table->float('muscle_mass')->nullable(); // نسبة العضل اذا متوفرة
            $table->float('body_fat_percentage')->nullable(); // نسبة الدهون في الجسم
            $table->float('waist_circumference')->nullable(); // محيط الخصر
            $table->float('chest_circumference')->nullable(); // محيط الصدر
            $table->float('arm_circumference')->nullable(); // محيط الذراع
            $table->float('leg_circumference')->nullable(); // محيط الساق
            $table->float('water_percentage')->nullable(); // نسبة الماء
            $table->float('bone_density')->nullable(); // كثافة العظام
            $table->float('metabolic_rate')->nullable(); // معدل الأيض
            $table->float('visceral_fat')->nullable(); // الدهون الحشوية
            $table->timestamp('recorded_at')->nullable(); // وقت التسجيل
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fitness_data');
    }
};
