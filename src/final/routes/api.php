<?php

use App\Http\Controllers\LogExportController;

Route::get('/octave/logs', [LogExportController::class, 'export_logs'])->name('octave.logs');
