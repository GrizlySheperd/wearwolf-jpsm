<?php

use App\Http\Controllers\MafiaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MafiaController::class, 'index'])->name('home');
Route::get('/mafia/new', [MafiaController::class, 'create'])->name('mafia.create');
Route::post('/mafia', [MafiaController::class, 'store'])->name('mafia.store');

Route::get('/mafia/{game}', [MafiaController::class, 'show'])->name('mafia.show');
Route::post('/mafia/{game}/vote', [MafiaController::class, 'vote'])->name('mafia.vote');
Route::post('/mafia/{game}/reset-votes', [MafiaController::class, 'resetVotes'])->name('mafia.resetVotes');
Route::post('/mafia/{game}/eliminate', [MafiaController::class, 'eliminate'])->name('mafia.eliminate');
Route::post('/mafia/{game}/undo', [MafiaController::class, 'undo'])->name('mafia.undo');
Route::post('/mafia/{game}/end', [MafiaController::class, 'end'])->name('mafia.end');
Route::post('/mafia/{game}/restart', [MafiaController::class, 'restart'])->name('mafia.restart');
