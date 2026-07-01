<?php

use App\Http\Controllers\ImagenProductoController;
use App\Http\Controllers\LandingController;
use App\Livewire\ReservaPublica;
use Illuminate\Support\Facades\Route;

// Landing page pública de la barbería (incluye el asistente de reservas embebido).
Route::get('/', LandingController::class)->name('inicio');

// Acceso directo al asistente de reservas (origen "enlace").
// Proxy para imágenes de productos almacenadas en FTP
Route::get('/img/producto/{filename}', ImagenProductoController::class)
    ->where('filename', '.+')
    ->name('img.producto');

Route::get('/reservar', ReservaPublica::class)
    ->middleware('throttle:60,1')
    ->name('reservar');
