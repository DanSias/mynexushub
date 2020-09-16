<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\DeadlineController;
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

// Programs
Route::get('/programs/{program?}', [ProgramController::class, 'index']);
// Partners
Route::get('/partners/{partner?}', [PartnerController::class, 'index']);
// Deadlines
Route::get('/deadlines/program/{program}', [DeadlineController::class, 'program']);

// Scorecard
Route::get('/scorecard', [ScorecardController::class, 'index']);
Route::get('/scorecard/saves', [ScorecardController::class, 'saves']);
Route::get('/scorecard/codes', [ScorecardController::class, 'codes']);
Route::get('/scorecard/code/{key}', [ScorecardController::class, 'code']);
Route::get('/scorecard/occupations', [ScorecardController::class, 'occupations']);
Route::get('/scorecard/occupation/{key}', [ScorecardController::class, 'occupation']);
Route::get('/scorecard/schools', [ScorecardController::class, 'schools']);
Route::get('/scorecard/school/{key}', [ScorecardController::class, 'school']);
// Scorecard Data (JSON)
Route::get('/scorecard/data/keywords', [ScorecardController::class, 'keywords']);
Route::get('/scorecard/data/codes-conferrals/{level?}', [ScorecardController::class, 'conferralCodes']);
Route::get('/scorecard/data/codes', [ScorecardController::class, 'allCodes']);
Route::get('/scorecard/data/occupations/proxy/{degree?}', [ScorecardController::class, 'degreeOccupations']);
Route::get('/scorecard/data/occupations', [ScorecardController::class, 'allOccupations']);
Route::get('/scorecard/data/schools', [ScorecardController::class, 'allSchools']);
Route::get('/scorecard/data/accreditation/{code}', [ScorecardController::class, 'programAccreditation']);
