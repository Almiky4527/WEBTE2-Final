<?php

use App\Http\Controllers\StatsController;

Route::prefix('stats')->name('stats.')->group(function () {
    Route::get('summary', [StatsController::class, 'summary'])->name('summary');
    Route::get('detail/{anim}', [StatsController::class, 'detail'])->name('detail');
});
