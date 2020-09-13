<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\ScorecardController;

Route::get('/', function () {
    return view('welcome');
});

// Socialite Google & Microsoft Login
Route::get('/login/{provider}', [LoginController::class, 'redirectToProvider']);
Route::get('/login/{provider}/callback', [LoginController::class, 'handleProviderCallback']);

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return Inertia\Inertia::render('Dashboard');
})->name('dashboard');

// Scorecard
Route::get('/scorecard/codes', [ScorecardController::class, 'codes']);
Route::get('/scorecard/code/{key}', [ScorecardController::class, 'code']);
Route::get('/scorecard/occupations', [ScorecardController::class, 'occupations']);
Route::get('/scorecard/occupation/{key}', [ScorecardController::class, 'occupation']);
Route::get('/scorecard/schools', [ScorecardController::class, 'schools']);
Route::get('/scorecard/school/{key}', [ScorecardController::class, 'school']);