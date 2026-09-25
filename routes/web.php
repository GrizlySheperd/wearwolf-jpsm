<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'index'])->name('home');
Route::get('/join/{code}', [GameController::class, 'joinForm'])->name('games.joinForm');

Route::post('/games', [GameController::class, 'store'])->name('games.store');
Route::post('/games/join', [GameController::class, 'join'])->name('games.join');

Route::get('/g/{code}', [GameController::class, 'show'])->name('games.show');
Route::get('/g/{code}/status', [GameController::class, 'status'])->name('games.status');
Route::post('/g/{code}/start', [GameController::class, 'start'])->name('games.start');
Route::post('/g/{code}/night-action', [GameController::class, 'nightAction'])->name('games.nightAction');
Route::post('/g/{code}/vote', [GameController::class, 'vote'])->name('games.vote');
Route::post('/g/{code}/resolve-night', [GameController::class, 'resolveNight'])->name('games.resolveNight');
Route::post('/g/{code}/resolve-day', [GameController::class, 'resolveDay'])->name('games.resolveDay');
Route::post('/g/{code}/reset', [GameController::class, 'reset'])->name('games.reset');
Route::post('/g/{code}/leave', [GameController::class, 'leave'])->name('games.leave');
