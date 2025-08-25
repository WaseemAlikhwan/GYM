<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Membership;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء أنواع العضويات إذا لم تكن موجودة
        $membershipTypes = [
            ['name' => 'Basic', 'description' => 'خطة أساسية مع إمكانية الوصول للمعدات الأساسية', 'price' => 50, 'duration_days' => 30, 'has_coach' => false, 'has_workout_plan' => false, 'has_nutrition_plan' => false],
            ['name' => 'Premium', 'description' => 'خطة متقدمة مع مدرب شخصي وخطة تمارين', 'price' => 100, 'duration_days' => 30, 'has_coach' => true, 'has_workout_plan' => true, 'has_nutrition_plan' => false],
            ['name' => 'VIP', 'description' => 'خطة VIP شاملة مع مدرب شخصي وخطة تمارين وتغذية', 'price' => 200, 'duration_days' => 30, 'has_coach' => true, 'has_workout_plan' => true, 'has_nutrition_plan' => true],
        ];

        foreach ($membershipTypes as $membershipData) {
            Membership::firstOrCreate(
                ['name' => $membershipData['name']],
                $membershipData
            );
        }

        // الحصول على المستخدمين
        $members = User::where('role', 'member')->get();
        $memberships = Membership::all();

        if ($members->isEmpty()) {
            $this->command->info('No members found. Please run AdminUserSeeder first.');
            return;
        }

        // إنشاء اشتراكات منتهية اليوم
        $today = Carbon::today();
        foreach ($members->take(2) as $index => $member) {
            $membership = $memberships->random();
            Subscription::create([
                'user_id' => $member->id,
                'membership_id' => $membership->id,
                'start_date' => $today->copy()->subDays(30),
                'end_date' => $today,
                'is_active' => true,
                'status' => 'active',
                'notes' => "اشتراك {$membership->name} للمستخدم {$member->name} - ينتهي اليوم",
                'created_at' => $today->copy()->subDays(30),
                'updated_at' => $today->copy()->subDays(30),
            ]);
        }

        // إنشاء اشتراكات تنتهي قريباً (خلال الأسبوع)
        foreach ($members->take(2) as $index => $member) {
            $membership = $memberships->random();
            $daysUntilExpiry = rand(1, 7);
            Subscription::create([
                'user_id' => $member->id,
                'membership_id' => $membership->id,
                'start_date' => $today->copy()->subDays(30),
                'end_date' => $today->copy()->addDays($daysUntilExpiry),
                'is_active' => true,
                'status' => 'active',
                'notes' => "اشتراك {$membership->name} للمستخدم {$member->name} - ينتهي بعد {$daysUntilExpiry} يوم",
                'created_at' => $today->copy()->subDays(30),
                'updated_at' => $today->copy()->subDays(30),
            ]);
        }

        // إنشاء اشتراكات نشطة طويلة الأمد
        foreach ($members->take(2) as $index => $member) {
            $membership = $memberships->random();
            Subscription::create([
                'user_id' => $member->id,
                'membership_id' => $membership->id,
                'start_date' => $today->copy()->subDays(15),
                'end_date' => $today->copy()->addDays(rand(30, 90)),
                'is_active' => true,
                'status' => 'active',
                'notes' => "اشتراك {$membership->name} للمستخدم {$member->name} - نشط",
                'created_at' => $today->copy()->subDays(15),
                'updated_at' => $today->copy()->subDays(15),
            ]);
        }

        // إنشاء اشتراكات منتهية
        foreach ($members->take(2) as $index => $member) {
            $membership = $memberships->random();
            Subscription::create([
                'user_id' => $member->id,
                'membership_id' => $membership->id,
                'start_date' => $today->copy()->subDays(60),
                'end_date' => $today->copy()->subDays(rand(1, 29)),
                'is_active' => false,
                'status' => 'expired',
                'notes' => "اشتراك {$membership->name} للمستخدم {$member->name} - منتهي",
                'created_at' => $today->copy()->subDays(60),
                'updated_at' => $today->copy()->subDays(60),
            ]);
        }

        $this->command->info('Subscriptions created successfully!');
        $this->command->info('Created subscriptions with various expiry dates for testing.');
    }
}
