<?php

use App\Http\Controllers\CalendarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalendarioController::class, 'index'])->name('calendario.index');
