<?php

use App\Http\Controllers\OctaveController;

Route::post('/octave/eval', [OctaveController::class, 'evaluate']);