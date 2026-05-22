<?php

use App\Http\Controllers\Auth\MagicLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $locale = 'ar';

    if ($cookie = request()->cookie('locale')) {
        $locale = $cookie;
    } elseif (request()->server('HTTP_ACCEPT_LANGUAGE')) {
        $preferred = substr(request()->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
        if (in_array($preferred, ['ar', 'fr'])) {
            $locale = $preferred;
        }
    }

    return redirect("/{$locale}");
});

Route::prefix('{locale}')->where(['locale' => 'ar|fr'])->middleware('locale')->group(function () {
    Route::get('/', fn () => view('hello'))->name('home');

    // Portal (client) auth — magic link
    Route::prefix('portal')->name('portal.')->group(function () {
        Route::get('login', [MagicLinkController::class, 'create'])->name('login');
        Route::post('login', [MagicLinkController::class, 'store'])->name('login.send');
        Route::get('login/sent', [MagicLinkController::class, 'sent'])->name('login.sent');
        Route::get('login/verify', [MagicLinkController::class, 'verify'])->name('login.verify');
        Route::post('logout', [MagicLinkController::class, 'logout'])->name('logout');

        Route::middleware('auth:client')->group(function () {
            Route::get('dashboard', fn () => view('portal.dashboard'))->name('dashboard');
        });
    });
});
