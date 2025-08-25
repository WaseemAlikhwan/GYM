<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء مستخدم admin تجريبي
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+1234567890',
            'gender' => 'male',
            'birth_date' => '1990-01-01',
        ]);

        // إنشاء مستخدم coach تجريبي
        User::create([
            'name' => 'Coach Mike',
            'email' => 'coach@example.com',
            'password' => Hash::make('password'),
            'role' => 'coach',
            'phone' => '+1234567891',
            'gender' => 'male',
            'birth_date' => '1985-05-15',
        ]);

        // إنشاء مستخدم member تجريبي
        User::create([
            'name' => 'John Doe',
            'email' => 'member@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'phone' => '+1234567892',
            'gender' => 'male',
            'birth_date' => '1995-08-20',
        ]);

        $this->command->info('Test users created successfully!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Coach: coach@example.com / password');
        $this->command->info('Member: member@example.com / password');
    }
}
