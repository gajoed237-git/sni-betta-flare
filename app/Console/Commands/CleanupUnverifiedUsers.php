<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-unverified-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus otomatis akun yang belum terverifikasi emailnya selama lebih dari 7 hari.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = \App\Models\User::whereNull('email_verified_at')
            ->where('email', '!=', 'superadmin@gajoed237.com')
            ->whereNotIn('role', ['admin', 'event_admin', 'judge'])
            ->where('created_at', '<', now()->subDays(7))
            ->delete();

        $this->info("Berhasil menghapus {$count} akun yang tidak terverifikasi.");
    }
}
