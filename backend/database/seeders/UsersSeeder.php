<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@gym.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '01234567890',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => 'القاهرة، مصر',
            'emergency_contact' => '01987654321',
            'profile_picture' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Sample Coaches
        $coaches = [
            [
                'name' => 'أحمد محمود',
                'email' => 'coach1@gym.com',
                'password' => Hash::make('password123'),
                'role' => 'coach',
                'phone' => '01111111111',
                'date_of_birth' => '1985-05-15',
                'gender' => 'male',
                'address' => 'الجيزة، مصر',
                'emergency_contact' => '01222222222',
                'specialization' => 'تدريب القوة',
                'experience_level' => 'خبير',
                'certification' => 'مدرب معتمد - ACSM',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'فاطمة أحمد',
                'email' => 'coach2@gym.com',
                'password' => Hash::make('password123'),
                'role' => 'coach',
                'phone' => '01333333333',
                'date_of_birth' => '1988-08-20',
                'gender' => 'female',
                'address' => 'الإسكندرية، مصر',
                'emergency_contact' => '01444444444',
                'specialization' => 'اليوغا والبيلاتس',
                'experience_level' => 'متوسط',
                'certification' => 'مدربة يوغا معتمدة',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'محمد علي',
                'email' => 'coach3@gym.com',
                'password' => Hash::make('password123'),
                'role' => 'coach',
                'phone' => '01555555555',
                'date_of_birth' => '1992-03-10',
                'gender' => 'male',
                'address' => 'الأقصر، مصر',
                'emergency_contact' => '01666666666',
                'specialization' => 'كمال الأجسام',
                'experience_level' => 'مبتدئ',
                'certification' => 'مدرب كمال أجسام',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($coaches as $coach) {
            User::create($coach);
        }

        // Create Sample Members
        $members = [
            [
                'name' => 'سارة محمد',
                'email' => 'member1@gym.com',
                'password' => Hash::make('password123'),
                'role' => 'member',
                'phone' => '01777777777',
                'date_of_birth' => '1995-07-25',
                'gender' => 'female',
                'address' => 'المنصورة، مصر',
                'emergency_contact' => '01888888888',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'عمر حسن',
                'email' => 'member2@gym.com',
                'password' => Hash::make('password123'),
                'role' => 'member',
                'phone' => '01999999999',
                'date_of_birth' => '1993-12-05',
                'gender' => 'male',
                'address' => 'طنطا، مصر',
                'emergency_contact' => '01000000000',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'نور الدين',
                'email' => 'member3@gym.com',
                'password' => Hash::make('password123'),
                'role' => 'member',
                'phone' => '01123456789',
                'date_of_birth' => '1997-04-18',
                'gender' => 'female',
                'address' => 'أسوان، مصر',
                'emergency_contact' => '01987654321',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'كريم صلاح',
                'email' => 'member4@gym.com',
                'password' => Hash::make('password123'),
                'role' => 'member',
                'phone' => '01234567891',
                'date_of_birth' => '1991-09-30',
                'gender' => 'male',
                'address' => 'الزقازيق، مصر',
                'emergency_contact' => '01876543210',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'مريم أحمد',
                'email' => 'member5@gym.com',
                'password' => Hash::make('password123'),
                'role' => 'member',
                'phone' => '01345678912',
                'date_of_birth' => '1996-11-12',
                'gender' => 'female',
                'address' => 'بورسعيد، مصر',
                'emergency_contact' => '01765432109',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($members as $member) {
            User::create($member);
        }
    }
}
