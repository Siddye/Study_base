<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// Public / Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Library & Documents
    Route::get('/library', [DocumentController::class, 'index'])->name('library.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::post('/documents/{document}/reprocess', [DocumentController::class, 'reprocess'])->name('documents.reprocess');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Folders
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::put('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    // Semantic Search
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    // Chat / Ask AI (RAG)
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/session', [ChatController::class, 'storeSession'])->name('chat.session.store');
    Route::post('/chat/{session}/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::delete('/chat/{session}', [ChatController::class, 'destroySession'])->name('chat.session.destroy');
});
