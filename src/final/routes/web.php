<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('console', 'Console')->name('console');
Route::inertia('pendulum', 'Pendulum')->name('pendulum');
Route::inertia('ball-beam', 'BallBeam')->name('ball-beam');
Route::inertia('stats', 'Stats')->name('stats');
Route::inertia('api-docs', 'ApiDocs')->name('api-docs');
