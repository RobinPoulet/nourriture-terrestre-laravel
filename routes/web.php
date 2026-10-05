<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RatingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::redirect('/index', '/');

// ── Authentification ──────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');
Route::redirect('/admin/login', '/login');

// ── Commandes ─────────────────────────────────────────────────────
Route::get('/display-orders', [OrderController::class, 'index'])->name('orders.index');
Route::middleware('auth')->group(function () {
    Route::get('/commande', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/create-order', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/edit-order/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::post('/delete-order/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
});

// ── Classement ────────────────────────────────────────────────────
Route::get('/ranking', [RatingController::class, 'index'])->name('ranking');
Route::get('/classement', [RatingController::class, 'classement'])->name('classement');
Route::post('/vote', [RatingController::class, 'vote'])->name('vote');

// ── Admin ─────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');

    Route::post('/create-user', [AdminController::class, 'createUser'])->name('users.store');
    Route::post('/edit-user/{user}', [AdminController::class, 'editUser'])->name('users.update');
    Route::post('/delete-user/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy');
    Route::post('/set-role/{user}', [AdminController::class, 'toggleUserRole'])->name('users.role');
    Route::post('/invite-user/{user}', [AdminController::class, 'inviteUser'])->name('users.invite');

    Route::post('/create-announcement', [AdminController::class, 'createAnnouncement'])->name('announcements.store');
    Route::post('/edit-announcement/{announcement}', [AdminController::class, 'editAnnouncement'])->name('announcements.update');
    Route::post('/delete-announcement/{announcement}', [AdminController::class, 'deleteAnnouncement'])->name('announcements.destroy');
    Route::post('/toggle-announcement/{announcement}', [AdminController::class, 'toggleAnnouncement'])->name('announcements.toggle');

    Route::post('/update-settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});
