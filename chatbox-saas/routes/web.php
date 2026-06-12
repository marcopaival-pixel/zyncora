<?php

use App\Http\Controllers\DemoChatController;
use App\Http\Controllers\LgpdController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy/{companySlug}', [LgpdController::class, 'showPrivacyPolicy'])->name('lgpd.privacy');
Route::post('/lgpd/consent', [LgpdController::class, 'submitConsent'])->name('lgpd.consent');

use App\Http\Controllers\PlatformLegalController;

Route::get('/termos-de-uso', [PlatformLegalController::class, 'terms'])->name('legal.terms');
Route::get('/politica-de-privacidade', [PlatformLegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/politica-de-cookies', [PlatformLegalController::class, 'cookies'])->name('legal.cookies');
Route::get('/central-lgpd', [PlatformLegalController::class, 'lgpdCentral'])->name('legal.lgpd-central');
Route::post('/central-lgpd/submit', [PlatformLegalController::class, 'submitLgpdRequest'])->name('legal.lgpd-request.submit');

Route::middleware(['auth'])->group(function () {
    Route::get('/aceite-termos', [PlatformLegalController::class, 'pendingAcceptance'])->name('legal.pending-acceptance');
    Route::post('/aceite-termos', [PlatformLegalController::class, 'acceptPending'])->name('legal.accept-pending');
});
Route::get('/chat/{company:slug}', function (\App\Models\Company $company) {
    $chatbot = $company->chatbots()->where('status', 'active')->latest()->first();
    return view('chat.widget', compact('company', 'chatbot'));
})->name('chat.widget');

Route::middleware('demo.enabled')->group(function () {
    Route::get('/demo', [DemoChatController::class, 'index'])->name('demo');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/admin/impersonation/leave', [\App\Http\Controllers\Admin\ImpersonationController::class, 'leave'])->name('admin.impersonation.leave');
});
