<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProgramCodeController;
use App\Http\Controllers\ProgramTrackController;
use App\Http\Controllers\ProgramConcentrationController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DeadlineController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ScorecardController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\TrelloController;
use App\Http\Controllers\DataController;

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
Route::get('/programs', [ProgramController::class, 'index']);
Route::get('/programs/{program?}/{action?}', [ProgramController::class, 'index']);
Route::post('/programs', [ProgramController::class, 'store']);
Route::patch('/programs/{program}', [ProgramController::class, 'update']);
// Partners
Route::get('/partners/{partner?}', [PartnerController::class, 'index']);
// Deadlines
Route::get('/deadlines/program/{program}', [DeadlineController::class, 'program']);
Route::get('/deadlines', [DeadlineController::class, 'index']);
Route::get('/deadlines/next', [DeadlineController::class, 'next']);

// Feedback
Route::get('/feedback', [FeedbackController::class, 'index']);

// Data returned as JSON
Route::get('/data/programs/list/bu', [ProgramController::class, 'buList']);
Route::get('/data/programs/list/type', [ProgramController::class, 'typeList']);
Route::get('/data/programs/list/partner', [ProgramController::class, 'partnerList']);
Route::get('/data/programs/list/vertical', [ProgramController::class, 'verticalList']);
Route::get('/data/programs/list/subvertical/{vertical?}', [ProgramController::class, 'subverticalList']);
Route::get('/data/partners/list/all', [PartnerController::class, 'data']);

// Program Codes, Tracks, and Concentrations
Route::post('/codes', [ProgramCodeController::class, 'store']);
Route::delete('/codes/{programCode}', [ProgramCodeController::class, 'destroy']);
Route::get('/data/programs/codes/{program?}', [ProgramCodeController::class, 'program']);

Route::post('/tracks', [ProgramTrackController::class, 'store']);
Route::delete('/tracks/{programTrack}', [ProgramTrackController::class, 'destroy']);
Route::get('/data/programs/tracks/{program?}', [ProgramTrackController::class, 'program']);

Route::post('/concentrations', [ProgramConcentrationController::class, 'store']);
Route::delete('/concentrations/{programConcentration}', [ProgramConcentrationController::class, 'destroy']);
Route::get('/data/programs/concentrations/{program}', [ProgramConcentrationController::class, 'program']);

Route::get('/forecast/{program?}/{channel?}', [ForecastController::class, 'index']);

Route::get('/data/forecast/settings', [ForecastController::class, 'settings']);
Route::get('/data/forecast/expected', [ForecastController::class, 'expected']);
Route::get('/data/forecast/has-forecast', [ForecastController::class, 'hasForecasts']);

// Budget
Route::get('/data/budget/settings', [BudgetController::class, 'settings']);
Route::get('/data/budget/expected', [BudgetController::class, 'expected']);
Route::get('/data/budget/has-budget', [BudgetController::class, 'hasForecasts']);

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

// Excel Exports
Route::get('/excel/attributes', [ExcelController::class, 'attributes']);

Route::get('/test', function () {
    return Inertia\Inertia::render('Test');
});

// Trello
Route::middleware(['auth:sanctum', 'verified'])->get('/trello', [TrelloController::class, 'index']);
Route::get('/trello/boards', [TrelloController::class, 'boards']);
Route::get('/trello/lists/{boardId}', [TrelloController::class, 'lists']);
Route::get('/trello/cards/{boardId}', [TrelloController::class, 'cards']);
Route::get('/trello/card/{cardId}', [TrelloController::class, 'card']);
Route::post('/trello/comment/{cardId}', [TrelloController::class, 'storeComment']);
Route::post('/trello/card/{listId}', [TrelloController::class, 'storeCard']);

// Metrics Data
Route::get('/metrics/{key?}', [DataController::class, 'metrics']);
Route::get('/data/metrics/totals', [DataController::class, 'metricTotals']);
Route::get('/data/pipeline/totals', [DataController::class, 'pipelineTotals']);

// Chart full year
Route::get('/data/chart/year', [DataController::class, 'chartMetrics']);
Route::get('/data/chart/year-pipeline', [DataController::class, 'chartPipeline']);

// Overview Dashboard
Route::get('/overview/{key?}', [DataController::class, 'overview']);


Route::get('/data/metrics/overview', [DataController::class, 'overviewData']);
Route::get('/data/metrics/overview/pipeline', [DataController::class, 'overviewPipeline']);
Route::get('/data/metrics/conversion', [DataController::class, 'conversion']);

Route::get('/data/revenue', [DataController::class, 'revenue']);
Route::get('/data/revenue/program-semester', [DataController::class, 'revenueSemester']);

Route::get('/data/term-conversion', [DataController::class, 'termConversion']);

Route::get('/data/dates', [DataController::class, 'dates']);

// Admin Settings
Route::get('/admin', [DataController::class, 'index']);
