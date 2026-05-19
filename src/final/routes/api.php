<?php

use App\Http\Controllers\OctaveController;
use App\Http\Controllers\LogExportController;
use App\Http\Middleware\LogOctaveApi;

Route::post('/octave/eval', [OctaveController::class, 'evaluate'])->middleware(LogOctaveApi::class)->name('octave.eval');

Route::get('/octave/logs', [LogExportController::class, 'export_logs'])->name('octave.logs');