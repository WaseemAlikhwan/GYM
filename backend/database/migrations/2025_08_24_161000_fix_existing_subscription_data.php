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
        // إصلاح البيانات الموجودة قبل تطبيق القيود
        
        // 1. إلغاء تفعيل الاشتراكات المتداخلة لنفس العضو
        $duplicateSubscriptions = DB::table('subscriptions')
            ->select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicateSubscriptions as $duplicate) {
            $subscriptions = DB::table('subscriptions')
                ->where('user_id', $duplicate->user_id)
                ->orderBy('created_at', 'desc')
                ->get();

            // الاحتفاظ بالاشتراك الأحدث وإلغاء تفعيل الباقي
            $keepActive = $subscriptions->first();
            
            DB::table('subscriptions')
                ->where('user_id', $duplicate->user_id)
                ->where('id', '!=', $keepActive->id)
                ->update(['is_active' => false, 'status' => 'cancelled']);
        }

        // 2. إلغاء تفعيل الاشتراكات المنتهية الصلاحية
        DB::table('subscriptions')
            ->where('end_date', '<', now())
            ->update(['is_active' => false, 'status' => 'expired']);

        // 3. إلغاء تفعيل الاشتراكات المتداخلة في التواريخ
        $overlappingSubscriptions = DB::table('subscriptions as s1')
            ->join('subscriptions as s2', function($join) {
                $join->on('s1.user_id', '=', 's2.user_id')
                     ->where('s1.id', '!=', 's2.id')
                     ->where(function($q) {
                         $q->where(function($subQ) {
                             $subQ->where('s1.start_date', '<=', DB::raw('s2.start_date'))
                                   ->where('s1.end_date', '>=', DB::raw('s2.start_date'));
                         })->orWhere(function($subQ) {
                             $subQ->where('s1.start_date', '<=', DB::raw('s2.end_date'))
                                   ->where('s1.end_date', '>=', DB::raw('s2.end_date'));
                         })->orWhere(function($subQ) {
                             $subQ->where('s1.start_date', '>=', DB::raw('s2.start_date'))
                                   ->where('s1.end_date', '<=', DB::raw('s2.end_date'));
                         });
                     });
            })
            ->select('s1.id')
            ->get();

        foreach ($overlappingSubscriptions as $overlapping) {
            DB::table('subscriptions')
                ->where('id', $overlapping->id)
                ->update(['is_active' => false, 'status' => 'cancelled']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا يمكن التراجع عن إصلاح البيانات
    }
};
