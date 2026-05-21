<?php

use App\Http\Controllers\AnimController;
use App\Http\Controllers\ApiDocsController;
use App\Http\Controllers\LogExportController;
use App\Http\Controllers\OctaveController;
use App\Http\Middleware\LogAnimStats;
use App\Http\Middleware\LogOctaveApi;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('console', 'Console')->name('console');
Route::inertia('pendulum', 'Pendulum')->name('pendulum');
Route::inertia('ball-beam', 'BallBeam')->name('ball-beam');
Route::inertia('stats', 'Stats')->name('stats');
Route::inertia('api-docs', 'ApiDocs')->name('api-docs');

Route::prefix('api')->name('api.docs.')->group(function () {
    Route::get('docs.yaml', [ApiDocsController::class, 'yaml'])->name('yaml');
    Route::get('docs.pdf', [ApiDocsController::class, 'pdf'])->name('pdf');
});

// Octave console endpoints — kept under /api/octave URL prefix but defined in
// web routes so they get session middleware (httpOnly cookie unlock flag).
Route::prefix('api/octave')->name('octave.')->group(function () {
    Route::post('unlock', [OctaveController::class, 'unlock'])->name('unlock');
    Route::post('lock', [OctaveController::class, 'lock'])->name('lock');
    Route::get('status', [OctaveController::class, 'status'])->name('status');
    Route::post('eval', [OctaveController::class, 'evaluate'])
        ->middleware(LogOctaveApi::class)
        ->name('evaluate');

    Route::get('logs', [LogExportController::class, 'index'])->name('logs.index');
    Route::get('logs.csv', [LogExportController::class, 'export_logs'])->name('logs.csv');

    Route::get('ball', [AnimController::class, 'run_ball'])
        ->middleware(LogAnimStats::class)->name('ball');
    Route::get('pendulum', [AnimController::class, 'run_pendulum'])
        ->middleware(LogAnimStats::class)->name('pendulum');
});
