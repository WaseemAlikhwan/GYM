<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FitnessData;
use App\Models\User;
use Carbon\Carbon;

class FitnessDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = User::where('role', 'member')->get();

        foreach ($members as $member) {
            // Create initial fitness data
            $baseWeight = rand(60, 100);
            $baseHeight = rand(160, 190);
            $baseBMI = round(($baseWeight / (($baseHeight / 100) ** 2)), 1);
            
            // Create fitness data for the last 12 weeks (weekly records)
            for ($i = 0; $i < 12; $i++) {
                $date = Carbon::now()->subWeeks($i);
                
                // Simulate gradual weight loss/gain
                $weightChange = rand(-2, 1) * 0.5; // Small weight changes
                $currentWeight = $baseWeight + ($weightChange * $i);
                $currentBMI = round(($currentWeight / (($baseHeight / 100) ** 2)), 1);
                
                FitnessData::create([
                    'user_id' => $member->id,
                    'weight' => $currentWeight,
                    'height' => $baseHeight,
                    'bmi' => $currentBMI,
                    'fat_percent' => rand(10, 30),
                    'muscle_mass' => rand(30, 50),
                    'body_fat_percentage' => rand(10, 30),
                    'waist_circumference' => rand(70, 120),
                    'chest_circumference' => rand(90, 130),
                    'arm_circumference' => rand(25, 40),
                    'leg_circumference' => rand(50, 70),
                    'water_percentage' => rand(50, 70),
                    'bone_density' => rand(2, 4),
                    'metabolic_rate' => rand(1200, 2000),
                    'visceral_fat' => rand(1, 15),
                    'recorded_at' => $date,
                    'notes' => 'قياسات تلقائية',
                ]);
            }
        }
    }
}
