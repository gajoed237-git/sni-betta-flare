<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EventAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Event Admin Betta',
            'email' => 'event_admin@siknusa.local',
            'password' => Hash::make('password123'),
            'phone' => '081234567891',
            'address' => 'Surabaya, Indonesia',
            'role' => 'event_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
