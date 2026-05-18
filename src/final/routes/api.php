<?php

use App\Http\Controllers\OctaveController;
use App\Http\Middleware\LogOctaveApi;

Route::post('/octave/eval', [OctaveController::class, 'evaluate'])->middleware(LogOctaveApi::class)->name('octave.eval');