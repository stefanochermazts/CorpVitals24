<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Welcome page
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

// Authentication routes
Route::middleware(['guest', 'throttle:auth'])->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['ensure.team'])
        ->name('dashboard');
    
    // KPIs API
    Route::get('/api/dashboard/kpis', [DashboardController::class, 'kpis'])
        ->middleware(['ensure.team'])
        ->name('dashboard.kpis');
});
