<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\FishScore;
use App\Observers\FishScoreObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in Production to fix 405 Method Not Allowed / Mixed Content issues
        if ($this->app->environment('production') || $this->app->environment('local')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \App\Models\FishScore::observe(\App\Observers\FishScoreObserver::class);
        \App\Models\Fish::observe(\App\Observers\FishObserver::class);
        \App\Models\Participant::observe(\App\Observers\ParticipantObserver::class);

        // Register custom argon2id hasher to support old bcrypt passwords without throwing exceptions
        \Illuminate\Support\Facades\Hash::extend('argon2id', function ($app) {
            return new \App\Hashing\CustomArgon2IdHasher($app['config']['hashing.argon']);
        });
    }
}
