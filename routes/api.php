<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompetitionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/profile/change-password', [AuthController::class, 'changePassword']);
    Route::post('/profile/settings', [AuthController::class, 'updateSettings']);
    Route::post('/profile/request-otp', [AuthController::class, 'requestOtpSettings']);
    Route::post('/push-token', [\App\Http\Controllers\Api\PushTokenController::class, 'update']);

    // Competition API for Judges
    Route::get('/assigned-events', [CompetitionController::class, 'getAssignedEvents']);
    Route::get('/judge-history', [CompetitionController::class, 'getJudgeHistory']);
    Route::get('/judge-stats', [CompetitionController::class, 'getJudgeStats']);
    Route::get('/unscored-fishes', [CompetitionController::class, 'getUnscoredFishes']);
    Route::get('/classes', [CompetitionController::class, 'getClasses']);
    Route::get('/classes/{classId}/fishes', [CompetitionController::class, 'getFishesByClass']);
    Route::get('/fishes/{id}', [CompetitionController::class, 'getFishDetails']);
    Route::post('/scores/submit', [CompetitionController::class, 'submitScore']);
    Route::post('/fishes/{fish}/move', [CompetitionController::class, 'moveFishClass']);
    Route::post('/fishes/{fish}/update-class', [CompetitionController::class, 'updateFishClass']);
    Route::post('/fishes/{fish}/disqualify', [CompetitionController::class, 'disqualifyFish']);
    Route::post('/nominate-fish/{fish}', [CompetitionController::class, 'toggleNomination']);
    Route::get('/assigned-nominations', [CompetitionController::class, 'getNominatedFishes']);
    Route::get('/divisions/{division}/winners', [CompetitionController::class, 'getDivisionWinners']);
    Route::post('/fishes/{fish}/winner-type', [CompetitionController::class, 'setWinnerType']);

    // Participant Registration
    Route::post('/register-event', [\App\Http\Controllers\Api\EventRegistrationController::class, 'register']);
    Route::post('/participants/{participant}/payment', [\App\Http\Controllers\Api\EventRegistrationController::class, 'uploadPayment']);
    Route::get('/my-participations', [\App\Http\Controllers\Api\EventRegistrationController::class, 'myParticipations']);
    Route::get('/participants/{participant}', [\App\Http\Controllers\Api\EventRegistrationController::class, 'showParticipant']);
    Route::get('/my-history', [\App\Http\Controllers\Api\EventRegistrationController::class, 'myHistory']);
    Route::get('/dashboard-stats', [\App\Http\Controllers\Api\EventRegistrationController::class, 'dashboardStats']);
    Route::get('/handlers', [\App\Http\Controllers\Api\EventRegistrationController::class, 'getHandlers']);
    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/delete-all', [\App\Http\Controllers\Api\NotificationController::class, 'deleteAll']);
    Route::delete('/notifications/{id}', [\App\Http\Controllers\Api\NotificationController::class, 'destroy']);

    // Event Interactions
    Route::post('/events/{event}/view', [\App\Http\Controllers\Api\EventInteractionController::class, 'incrementView']);
    Route::post('/events/{event}/like', [\App\Http\Controllers\Api\EventInteractionController::class, 'toggleLike']);
    Route::post('/events/{event}/share', [\App\Http\Controllers\Api\EventInteractionController::class, 'incrementShare']);
    Route::get('/events/{event}/comments', [\App\Http\Controllers\Api\EventInteractionController::class, 'getComments']);
    Route::post('/events/{event}/comments', [\App\Http\Controllers\Api\EventInteractionController::class, 'addComment']);
    Route::post('/comments/{comment}/react', [\App\Http\Controllers\Api\EventInteractionController::class, 'reactToComment']);
});

// Public Event Routes
Route::get('/events', [\App\Http\Controllers\Api\EventRegistrationController::class, 'index']);
Route::get('/events/{event}', [\App\Http\Controllers\Api\EventRegistrationController::class, 'show']);
