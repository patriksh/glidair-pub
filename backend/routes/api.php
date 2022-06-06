<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CompetitionController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ClubController;
use App\Http\Controllers\API\LeaderboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::resource('competition', CompetitionController::class, ['only' => ['index', 'show']]);
Route::resource('user', UserController::class, ['only' => ['index']]);
Route::resource('club', ClubController::class, ['only' => ['index']]);

Route::get('leaderboard/participants', [LeaderboardController::class, 'participants']);
Route::get('leaderboard/clubs', [LeaderboardController::class, 'clubs']);

Route::post('login', [AuthController::class, 'login']);

Route::get('competition/report/{id}', [CompetitionController::class, 'downloadReport']);

Route::group(['middleware' => 'auth:api'], function() {
    Route::put('competition/judges/{id}', [CompetitionController::class, 'updateJudges']);
    Route::put('competition/participants/{id}', [CompetitionController::class, 'updateParticipants']);
    Route::put('competition/participants/xls/{id}', [CompetitionController::class, 'updateParticipantsFromXls']);
    Route::put('competition/rounds/{id}', [CompetitionController::class, 'updateRounds']);
    Route::resource('competition', CompetitionController::class, ['except' => ['index', 'show']]);

    Route::resource('user', UserController::class, ['except' => ['index']]);
    Route::resource('club', ClubController::class, ['except' => ['index']]);
});