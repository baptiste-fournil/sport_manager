<?php

use App\Http\Controllers\Api\SessionSetApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    // Session Set JSON API endpoints
    Route::post('session-exercises/{sessionExercise}/sets', [SessionSetApiController::class, 'store']);
    Route::patch('session-sets/{sessionSet}', [SessionSetApiController::class, 'update']);
    Route::delete('session-sets/{sessionSet}', [SessionSetApiController::class, 'destroy']);

    // Get session data (for refreshing without Inertia)
    Route::get('sessions/{session}', [SessionSetApiController::class, 'getSession']);
});
