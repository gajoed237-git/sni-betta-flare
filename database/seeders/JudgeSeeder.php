<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class JudgeSeeder extends Seeder
{
    public function run(): void
    {
        $judges = [
            ['name' => 'Judge 1', 'email' => 'judge1@bettaflare.com'],
            ['name' => 'Judge 2', 'email' => 'judge2@bettaflare.com'],
            ['name' => 'Judge 3', 'email' => 'judge3@bettaflare.com'],
        ];

        foreach ($judges as $judge) {
            DB::table('users')->insert([
                'name' => $judge['name'],
                'email' => $judge['email'],
                'password' => Hash::make('password'),
                'role' => 'judge',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
