<?php

use App\Http\Controllers\LeaderNetworkController;
use Illuminate\Support\Facades\Route;

Route::get('/leaders/{leaderId}/network', [LeaderNetworkController::class, 'show'])
    ->name('leaders.network');
