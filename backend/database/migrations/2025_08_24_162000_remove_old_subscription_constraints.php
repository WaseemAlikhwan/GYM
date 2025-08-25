<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // إزالة constraint القديم إذا كان موجوداً
        try {
            DB::statement('ALTER TABLE subscriptions DROP INDEX unique_user_month_subscription');
        } catch (Exception $e) {
            // تجاهل الخطأ إذا لم يكن constraint موجوداً
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا يمكن إعادة constraint القديم
    }
};
