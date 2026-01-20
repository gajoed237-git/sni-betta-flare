<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\Division;
use App\Models\BettaClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Superadmin
        User::create([
            'name' => 'Superadmin',
            'email' => 'admin@cupang.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Create Event Admin
        $eventAdmin = User::create([
            'name' => 'Event Admin Yusuf',
            'email' => 'eventadmin@cupang.com',
            'password' => Hash::make('password'),
            'role' => 'event_admin',
        ]);

        // 3. Create Judge
        User::create([
            'name' => 'Judge Junaedi',
            'email' => 'juri@cupang.com',
            'password' => Hash::make('password'),
            'role' => 'judge',
        ]);

        // 4. Create Sample Event
        $event = Event::create([
            'name' => 'SNI Betta Contest 2026',
            'event_date' => '2026-03-15',
            'location' => 'Jakarta, Indonesia',
            'is_active' => true,
        ]);

        // 5. Assign Event Admin to Event
        $eventAdmin->managed_events()->attach($event->id, ['role' => 'event_admin']);

        // 6. Create Sample Divisions for this Event
        $divA = Division::create([
            'event_id' => $event->id,
            'name' => 'Division A - Halfmoon Special',
            'code' => 'DIV-A',
        ]);

        $divB = Division::create([
            'event_id' => $event->id,
            'name' => 'Division B - Plakat Solid',
            'code' => 'DIV-B',
        ]);

        // 7. Create Sample Betta Classes for these Divisions
        BettaClass::create([
            'event_id' => $event->id,
            'division_id' => $divA->id,
            'name' => 'Halfmoon Red',
            'code' => 'HM01',
        ]);

        BettaClass::create([
            'event_id' => $event->id,
            'division_id' => $divA->id,
            'name' => 'Halfmoon Blue',
            'code' => 'HM02',
        ]);

        BettaClass::create([
            'event_id' => $event->id,
            'division_id' => $divB->id,
            'name' => 'Plakat Super Red',
            'code' => 'PK01',
        ]);

        BettaClass::create([
            'event_id' => $event->id,
            'division_id' => $divB->id,
            'name' => 'Plakat Black Samurai',
            'code' => 'PK02',
        ]);
    }
}
