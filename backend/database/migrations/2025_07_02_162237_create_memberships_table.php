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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Regular - VIP - Trial...
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->integer('duration_days'); // عدد أيام الاشتراك
            $table->boolean('has_coach')->default(false);
            $table->boolean('has_workout_plan')->default(false);
            $table->boolean('has_nutrition_plan')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
