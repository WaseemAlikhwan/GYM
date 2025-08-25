<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = User::where('role', 'member')->get();

        foreach ($members as $member) {
            // Create attendance records for the last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::now()->subDays($i);
                
                // Random chance of attendance (70% chance)
                if (rand(1, 100) <= 70) {
                    $checkInTime = $date->copy()->setTime(rand(6, 10), rand(0, 59));
                    $checkOutTime = $checkInTime->copy()->addHours(rand(1, 3))->addMinutes(rand(0, 59));
                    
                    Attendance::create([
                        'user_id' => $member->id,
                        'check_in_time' => $checkInTime,
                        'check_out_time' => $checkOutTime,
                        'duration_minutes' => $checkInTime->diffInMinutes($checkOutTime),
                        'date' => $date->format('Y-m-d'),
                        'notes' => 'حضور تلقائي',
                    ]);
                }
            }
        }
    }
}
