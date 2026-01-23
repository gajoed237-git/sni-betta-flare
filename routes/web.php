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
    
    // Route untuk membuka print di tab baru
    Route::get('/open-print-new-tab/{encodedUrl?}', function (\Illuminate\Http\Request $request, $encodedUrl = null) {
        $url = null;
        
        // Coba ambil dari route parameter dulu
        if ($encodedUrl) {
            $url = base64_decode(urldecode($encodedUrl), true);
        }
        
        // Fallback: coba dari query string
        if (!$url && $request->has('url')) {
            $url = base64_decode(urldecode($request->query('url')), true);
        }
        
        return view('print-new-tab', ['url' => $url]);
    })->name('open.print.new.tab');
});
