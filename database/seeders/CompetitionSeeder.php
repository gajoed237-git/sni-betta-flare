<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['code' => 'A', 'name' => 'HALFMOON'],
            ['code' => 'B', 'name' => 'CROWNTAIL'],
            ['code' => 'C', 'name' => 'PLAKAT'],
            ['code' => 'D', 'name' => 'DOUBLE TAIL'],
            ['code' => 'E', 'name' => 'GIANT'],
            ['code' => 'F', 'name' => 'BABY HALFMOON'],
            ['code' => 'G', 'name' => 'BABY CROWNTAIL'],
            ['code' => 'H', 'name' => 'BABY PLAKAT'],
            ['code' => 'I', 'name' => 'BABY GIANT'],
        ];

        foreach ($divisions as $div) {
            $divisionId = DB::table('divisions')->insertGetId([
                'name' => $div['name'],
                'code' => $div['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $classes = [
                ['code' => $div['code'] . '1', 'name' => $div['name'] . ' BEBAS TERANG'],
                ['code' => $div['code'] . '2', 'name' => $div['name'] . ' BEBAS GELAP'],
                ['code' => $div['code'] . '3', 'name' => $div['name'] . ' KOMBINASI'],
                ['code' => $div['code'] . '4', 'name' => $div['name'] . ' DASAR'],
                ['code' => $div['code'] . '5', 'name' => $div['name'] . ' MARBLE/FANCY'],
            ];

            foreach ($classes as $cls) {
                DB::table('classes')->insert([
                    'division_id' => $divisionId,
                    'name' => $cls['name'],
                    'code' => $cls['code'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('events')->insert([
            'name' => 'SNI BETTA FLARE CHAMPIONSHIP 2026',
            'event_date' => '2026-06-01',
            'location' => 'Jakarta, Indonesia',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('users')->insert([
            'name' => 'Admin Utama',
            'email' => 'admin@bettaflare.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
