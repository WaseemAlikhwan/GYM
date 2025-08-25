<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\coach_member;
use App\Models\User;

class CoachMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get coaches and members
        $coaches = User::where('role', 'coach')->get();
        $members = User::where('role', 'member')->get();

        if ($coaches->count() > 0 && $members->count() > 0) {
            // Assign members to coaches
            $coachMemberPairs = [
                ['coach_email' => 'coach1@gym.com', 'member_email' => 'member1@gym.com'],
                ['coach_email' => 'coach1@gym.com', 'member_email' => 'member2@gym.com'],
                ['coach_email' => 'coach2@gym.com', 'member_email' => 'member3@gym.com'],
                ['coach_email' => 'coach2@gym.com', 'member_email' => 'member4@gym.com'],
                ['coach_email' => 'coach3@gym.com', 'member_email' => 'member5@gym.com'],
            ];

            foreach ($coachMemberPairs as $pair) {
                $coach = User::where('email', $pair['coach_email'])->first();
                $member = User::where('email', $pair['member_email'])->first();

                if ($coach && $member) {
                    coach_member::create([
                        'coach_id' => $coach->id,
                        'member_id' => $member->id,
                        'assigned_at' => now(),
                        'is_active' => true,
                        'notes' => 'تم تعيين المدرب بشكل تلقائي',
                    ]);
                }
            }
        }
    }
}
