<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\Publications\DownloadController;
use App\Http\Controllers\Public\Publications\ShowController;
use App\Http\Controllers\ViewTrackerController;
use App\Http\Controllers\Public\DownloadSurveyController;
use App\Http\Controllers\Public\DataDownloadController;
use App\Http\Controllers\Public\DataPdfDownloadController;
use App\Http\Controllers\Public\WalidataDownloadController;
use App\Http\Controllers\Public\WalidataPdfDownloadController;

// =======================
// PUBLIC (tanpa 'guest')
// =======================
Route::name('public.')->group(function () {
    Route::get('/', \App\Livewire\Admin\Home::class)->name('home');

    Route::get('/data', \App\Livewire\Public\Data\Index::class)->name('data.index');
    Route::get('/data/{dataset}', \App\Livewire\Public\Data\Show::class)->name('data.show');
    Route::get('/data/{dataset}/unduh', [DataDownloadController::class, 'download'])->name('data.download');
    Route::get('/data/{dataset}/pdf', [DataPdfDownloadController::class, 'download'])->name('data.pdf.download');

    Route::get('/walidata', \App\Livewire\Public\Walidata\Index::class)->name('walidata.index');
    Route::get('/walidata/{walidata}', \App\Livewire\Public\Walidata\Show::class)->name('walidata.show');
    Route::get('/walidata/{walidata}/unduh', [WalidataDownloadController::class, 'download'])->name('walidata.download');
    Route::get('/walidata/{walidata}/pdf', [WalidataPdfDownloadController::class, 'download'])->name('walidata.pdf.download');

    Route::view('/aspek', 'public.aspects.index')->name('aspects.index');
    Route::view('/aspek/{slug}', 'public.aspects.show')->name('aspects.show');

    Route::view('/publikasi', 'public.publications.index')->name('publications.index');
    Route::get('/publikasi/{publication}/unduh', DownloadController::class)->name('publications.download');

    Route::view('/instansi', 'public.agencies.index')->name('agencies.index');
    Route::view('/instansi/{slug}', 'public.agencies.show')->name('agencies.show');
    
    // View tracking routes
    Route::post('/track-view', [ViewTrackerController::class, 'trackView'])->name('track-view');
    Route::get('/view-count', [ViewTrackerController::class, 'getViewCount'])->name('view-count');
    
    // Survey routes
    Route::get('/survey/check', [DownloadSurveyController::class, 'checkSurveyRequired'])->name('survey.check');
    Route::post('/survey/submit', [DownloadSurveyController::class, 'submitSurvey'])->name('survey.submit');
});

Route::middleware('guest')->group(function () { 
    Route::get('/login', [\App\Http\Controllers\SsoController::class, 'login'])->name('login'); 
    Route::get('/auth/callback', [\App\Http\Controllers\SsoController::class, 'callback'])->name('auth.callback'); });

Route::get('/chatbot/stream', [\App\Http\Controllers\ChatbotStreamController::class, 'stream'])
    ->name('chatbot.stream');

Route::prefix('admin')->middleware('role:admin|verifikator|user')->name('admin.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Admin\Home::class)->name('dashboard');
    Route::get('/aspek', \App\Livewire\Admin\AspekCrud::class)->name('aspek');
    Route::get('/bidang', \App\Livewire\Admin\BidangCrud::class)->name('bidang');
    Route::get('/indikator', \App\Livewire\Admin\IndikatorCrud::class)->name('indikator');
    Route::get('/publikasi', \App\Livewire\Admin\PublikasiCrud::class)->name('publikasi.index');
    Route::get('/dataset', \App\Livewire\Admin\DatasetCrud::class)->name('dataset.index');
    Route::get('/walidata', \App\Livewire\Admin\IndikatorWalidata::class)->name('walidata.index');
    Route::get('/datasets/{dataset}', \App\Livewire\Admin\DetailData::class)->name('dataset.show');
    Route::get('/walidata/{walidata}', \App\Livewire\Admin\DetailIndikator::class)->name('walidata.show');
    Route::post('/logout', \App\Http\Controllers\LogoutController::class)->name('logout');
    Route::get('/skpd', \App\Livewire\Admin\SkpdCrud::class)->name('skpd.index');

    // Role admin khusus
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', \App\Livewire\Admin\Users\Index::class)->name('users.index');
        Route::get('/survey', \App\Livewire\Admin\SurveyCrud::class)->name('survey');
    });
});

Route::post('/logout', \App\Http\Controllers\LogoutController::class)->name('logout');

Route::get('/json', [\App\Http\Controllers\JsonController::class, 'index']);


Route::fallback(function () {
    if (request()->is('api/*') || request()->expectsJson()) {
        return response()->json(['message' => 'Not Found.'], 404);
    }
    return redirect()->route('public.home');
});

require __DIR__.'/auth.php';
