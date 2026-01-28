<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\LanguageController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify-app', [AuthController::class, 'verifyAppBridge'])
    ->name('verify.bridge');

Route::get('lang/{locale}', [LanguageController::class, 'switch'])
    ->name('lang.switch');

Route::get('/template/import-classes', [PrintController::class, 'downloadImportTemplate'])
    ->name('template.import-classes');
Route::get('/certificate/{fishId}', [PrintController::class, 'printCertificate'])
    ->name('certificate.download');

/*
|--------------------------------------------------------------------------
| Protected Routes (Filament Auth)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/print/labels', [PrintController::class, 'printLabels'])
        ->name('print.labels');

    Route::get('/print/class-results/{classId}', [PrintController::class, 'printClassResults'])
        ->name('print.class-results');

    Route::get('/print/champion-standings', [PrintController::class, 'printChampionStandings'])
        ->name('print.champion-standings');

    Route::get('/print/registration-form/{eventId}', [PrintController::class, 'printRegistrationForm'])
        ->name('print.registration-form');

    Route::get('/print/fish-out/{participantId}', [PrintController::class, 'printFishOut'])
        ->name('print.fish-out');

    Route::get('/print/empty-registration/{participantId}', [PrintController::class, 'printEmptyRegistration'])
        ->name('print.empty-registration');

    Route::get('/print/moved-dq-report', [PrintController::class, 'printMovedDqFishes'])
        ->name('print.moved-dq');

    // Route untuk membuka print di tab baru
    Route::post('/open-print-new-tab', function (\Illuminate\Http\Request $request) {
        $url = $request->input('url');

        // Security validation for URL
        if ($url && !str_starts_with($url, config('app.url'))) {
            abort(403, 'Unauthorized target URL');
        }

        return view('print-new-tab', ['url' => $url]);
    })->name('open.print.new.tab');

    // GET fallback jika ada query string
    Route::get('/open-print-new-tab', function (\Illuminate\Http\Request $request) {
        $url = $request->query('url');
        if ($url) {
            // Jika base64 encoded
            $decoded = base64_decode($url, true);
            if ($decoded) {
                $url = $decoded;
            }
        }

        // Security validation for URL
        if ($url && !str_starts_with($url, config('app.url'))) {
            abort(403, 'Unauthorized target URL');
        }

        return view('print-new-tab', ['url' => $url]);
    });
});
