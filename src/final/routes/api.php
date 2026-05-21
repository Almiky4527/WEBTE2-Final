<?php

use App\Http\Controllers\LogExportController;
use App\Http\Controllers\AnimController;
use App\Http\Middleware\LogAnimStats;
use App\Enums\AnimType;

Route::get('/octave/logs', [LogExportController::class, 'export_logs'])->name('octave.logs');

Route::get('/octave/ball', [AnimController::class, 'run_ball'])
    ->middleware(LogAnimStats::class)->name('octave.ball');

Route::get('/octave/pendulum', [AnimController::class, 'run_pendulum'])
    ->middleware(LogAnimStats::class)->name('octave.pendulum');