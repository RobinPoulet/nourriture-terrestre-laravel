<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RatingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::redirect('/index', '/');

// ── Commandes ─────────────────────────────────────────────────────
Route::get('/commande', [OrderController::class, 'create'])->name('orders.create');
Route::post('/create-order', [OrderController::class, 'store'])->name('orders.store');
Route::get('/display-orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/edit-order/{order}', [OrderController::class, 'update'])->name('orders.update');
Route::post('/delete-order/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

// ── Classement ────────────────────────────────────────────────────
Route::get('/ranking', [RatingController::class, 'index'])->name('ranking');
Route::get('/classement', [RatingController::class, 'classement'])->name('classement');
Route::post('/vote', [RatingController::class, 'vote'])->name('vote');

// ── Admin ─────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
    Route::post('/authenticate', [AdminLoginController::class, 'store'])->name('authenticate');
    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

    Route::middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');

        Route::post('/create-user', [AdminController::class, 'createUser'])->name('users.store');
        Route::post('/edit-user/{user}', [AdminController::class, 'editUser'])->name('users.update');
        Route::post('/delete-user/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy');
        Route::post('/set-role/{user}', [AdminController::class, 'toggleUserRole'])->name('users.role');
        Route::post('/reset-device/{user}', [AdminController::class, 'resetUserDevice'])->name('users.reset-device');

        Route::post('/create-announcement', [AdminController::class, 'createAnnouncement'])->name('announcements.store');
        Route::post('/edit-announcement/{announcement}', [AdminController::class, 'editAnnouncement'])->name('announcements.update');
        Route::post('/delete-announcement/{announcement}', [AdminController::class, 'deleteAnnouncement'])->name('announcements.destroy');
        Route::post('/toggle-announcement/{announcement}', [AdminController::class, 'toggleAnnouncement'])->name('announcements.toggle');

        Route::post('/update-settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    });
});
