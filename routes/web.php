<?php

use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\LeaderController;
use Illuminate\Support\Facades\Route;

/* Public portal */
Route::get('/', [HomeController::class, 'index'])->name('public.home');
Route::get('/leaders', [LeaderController::class, 'index'])->name('public.leaders.index');
Route::get('/leaders/{leader}', [LeaderController::class, 'show'])
    ->where('leader', '[A-Za-z0-9_-]+')
    ->name('public.leaders.show');

/* Admin portal */
require base_path('routes/admin.php');
require base_path('routes/network.php');
