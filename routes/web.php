<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Response;

// Página inicial redireciona para login
Route::get('/', fn () => redirect('/login'));

// Rotas de autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Área protegida por autenticação
Route::middleware(['auth'])->group(function () {
    // Dashboard usando controller dedicado
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Rota silenciosa para evitar erro 404 com /sw.js
Route::get('/sw.js', fn () => response('', 204));
