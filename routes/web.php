<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'role:Super Admin|Wakasek Sarpras'])->group(function () {
    Route::resource('locations', \App\Http\Controllers\Web\LocationController::class);
});
