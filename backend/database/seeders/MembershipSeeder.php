<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Membership;

class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Membership::create([
            'name' => 'Trial',
            'description' => 'تجربة مجانية ليوم واحد',
            'price' => 0,
            'duration_days' => 1,
            'has_coach' => false,
            'has_workout_plan' => false,
            'has_nutrition_plan' => false,
         ]);
        Membership::create([
            'name' => 'Regular',
            'description' => 'اشتراك شهري عادي',
            'price' => 100000,
            'duration_days' => 30,
            'has_coach' => false,
            'has_workout_plan' => true,
            'has_nutrition_plan' => false,
        ]);
         Membership::create([
            'name' => 'VIP',
            'description' => 'اشتراك مع كوتش خاص وخطة شاملة',
            'price' => 200000,
            'duration_days' => 30,
            'has_coach' => true,
            'has_workout_plan' => true,
            'has_nutrition_plan' => true,
        ]);

         Membership::create([
            'name' => 'Couple Discount',
            'description' => 'اشتراك مزدوج لشخصين مع خصم',
            'price' => 150000,
            'duration_days' => 30,
            'has_coach' => false,
            'has_workout_plan' => true,
            'has_nutrition_plan' => true,
        ]);


    }
}
