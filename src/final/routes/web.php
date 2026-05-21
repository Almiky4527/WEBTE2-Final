<?php

use App\Http\Controllers\OctaveController;
use App\Http\Middleware\LogOctaveApi;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('console', 'Console')->name('console');
Route::inertia('pendulum', 'Pendulum')->name('pendulum');
Route::inertia('ball-beam', 'BallBeam')->name('ball-beam');
Route::inertia('stats', 'Stats')->name('stats');
Route::inertia('api-docs', 'ApiDocs')->name('api-docs');

// Octave console endpoints — kept under /api/octave URL prefix but defined in
// web routes so they get session middleware (httpOnly cookie unlock flag).
Route::prefix('api/octave')->name('octave.')->group(function () {
    Route::post('unlock', [OctaveController::class, 'unlock'])->name('unlock');
    Route::post('eval', [OctaveController::class, 'evaluate'])
        ->middleware(LogOctaveApi::class)
        ->name('evaluate');
});
