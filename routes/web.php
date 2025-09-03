<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


Route::get('/chatbot/stream', [App\Http\Controllers\ChatbotStreamController::class, 'stream'])->name('chatbot.stream');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', \App\Livewire\Home::class)->name('dashboard');
    Route::get('/aspek', \App\Livewire\AspekCrud::class)->name('aspek');
    Route::get('/bidang', \App\Livewire\BidangCrud::class)->name('bidang');
    Route::get('/indikator', \App\Livewire\IndikatorCrud::class)->name('indikator');
    Route::get('/publikasi', \App\Livewire\PublikasiCrud::class)->name('publikasi.index');
    Route::get('/dataset', \App\Livewire\DatasetCrud::class)->name('dataset.index');
    Route::get('/walidata', \App\Livewire\IndikatorWalidata::class)->name('walidata.index');
    Route::get('/datasets/{dataset}', \App\Livewire\DetailData::class)->name('dataset.show');
    Route::get('/walidata/{walidata}', \App\Livewire\DetailIndikator::class)->name('walidata.show');
    Route::post('/logout', \App\Http\Controllers\LogoutController::class)->name('logout');
    Route::get('/skpd', \App\Livewire\SkpdCrud::class)->name('skpd.index');
});

Route::middleware('auth', 'role:admin')->group(function () {
    Route::get('users', \App\Livewire\Users\Index::class)->name('users.index');
});

Route::middleware('guest')->group(function () {
Route::get('/', \App\Livewire\HomeSlider::class)->name('home')->middleware('guest');
Route::get('/login', [\App\Http\Controllers\SsoController::class, 'login'])->name('login');        
Route::get('/auth/callback', [\App\Http\Controllers\SsoController::class, 'callback'])->name('auth.callback');  

});

Route::get('/json', [\App\Http\Controllers\JsonController::class, 'index']);

Route::fallback(function () {
    // Kalau request API / expects JSON, tetap 404 JSON
    if (request()->is('api/*') || request()->expectsJson()) {
        return response()->json(['message' => 'Not Found.'], 404);
    }
    // Selain itu, redirect ke beranda
    return redirect()->route('home'); // atau: return redirect('/');
});

require __DIR__.'/auth.php';
