<?php

use App\Http\Controllers\DemoChatController;
use App\Http\Controllers\LgpdController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy/{companySlug}', [LgpdController::class, 'showPrivacyPolicy'])->name('lgpd.privacy');
Route::post('/lgpd/consent', [LgpdController::class, 'submitConsent'])->name('lgpd.consent');

Route::get('/privacy-policy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/terms-of-use', function () {
    return view('terms');
})->name('terms');
Route::get('/chat/{company:slug}', function (\App\Models\Company $company) {
    return view('chat.widget', compact('company'));
})->name('chat.widget');

Route::middleware('demo.enabled')->group(function () {
    Route::get('/demo', [DemoChatController::class, 'index'])->name('demo');
});
