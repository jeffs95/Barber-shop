<?php

use App\Http\Controllers\LandingController;
use App\Livewire\ReservaPublica;
use Illuminate\Support\Facades\Route;

// Landing page pública de la barbería (incluye el asistente de reservas embebido).
Route::get('/', LandingController::class)->name('inicio');

// Acceso directo al asistente de reservas (origen "enlace").
Route::get('/reservar', ReservaPublica::class)
    ->middleware('throttle:60,1')
    ->name('reservar');
