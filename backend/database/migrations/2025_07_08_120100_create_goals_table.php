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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['weight_loss', 'weight_gain', 'muscle_gain', 'endurance', 'flexibility', 'general_fitness']);
            $table->decimal('target_value', 8, 2)->nullable(); // القيمة المستهدفة
            $table->string('unit')->nullable(); // الوحدة (kg, cm, minutes, etc.)
            $table->date('target_date');
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active');
            $table->decimal('current_value', 8, 2)->nullable(); // القيمة الحالية
            $table->integer('progress_percentage')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('target_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};

