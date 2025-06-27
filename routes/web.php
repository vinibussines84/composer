<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// Página inicial redireciona para login
Route::get('/', function () {
    return redirect('/login');
});

// Rotas de autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rota protegida (exemplo de dashboard)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard'); // Crie resources/views/dashboard.blade.php
    })->name('dashboard');
});
