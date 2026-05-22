<?php

use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ConsultationController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LegalController;
use App\Http\Controllers\Public\OfficeController;
use App\Http\Controllers\Public\ServiceDetailController;
use App\Http\Controllers\Public\ServicesIndexController;
use App\Http\Controllers\Public\ShortLinkController;
use App\Http\Controllers\Webhook\ResendWebhookController;
use App\Http\Controllers\Webhook\StripeWebhookController;
use App\Http\Controllers\Webhook\TwilioWebhookController;
use App\Livewire\Booking\CreateBooking;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe')->middleware('throttle.webhooks');
Route::post('/webhooks/resend', ResendWebhookController::class)->name('webhooks.resend');
Route::post('/webhooks/twilio', TwilioWebhookController::class)->name('webhooks.twilio');

Route::get('/s/{hash}', ShortLinkController::class)->name('short-link');

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

// SEO: robots.txt
Route::get('/robots.txt', function () {
    $content = "User-agent: *\nAllow: /\n\nSitemap: ".url('/sitemap.xml');

    return Response::make($content, 200, ['Content-Type' => 'text/plain']);
});

Route::prefix('{locale}')->where(['locale' => 'ar|fr'])->middleware('locale')->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/maitre-bouhamidi', AboutController::class)->name('about');
    Route::get('/services', ServicesIndexController::class)->name('services.index');
    Route::get('/services/{slug}', ServiceDetailController::class)->name('services.show');
    Route::get('/consultation', ConsultationController::class)->name('consultation');
    Route::get('/faq', FaqController::class)->name('faq');
    Route::get('/contact', ContactController::class)->name('contact');
    Route::get('/cabinet', OfficeController::class)->name('office');
    Route::get('/{page}', LegalController::class)->whereIn('page', ['mentions-legales', 'politique-confidentialite', 'conditions-utilisation'])->name('legal.show');

    Route::get('/book', CreateBooking::class)->name('book');

    // SEO: Sitemap per locale
    Route::get('/sitemap.xml', function () {
        $locale = request()->segment(1);
        $pages = [
            '',
            'maitre-bouhamidi',
            'services',
            'services/actes-familiaux',
            'services/immobilier',
            'services/entreprise',
            'services/contentieux',
            'consultation',
            'faq',
            'contact',
            'cabinet',
            'mentions-legales',
            'politique-confidentialite',
            'conditions-utilisation',
            'book',
        ];

        $urls = collect($pages)->map(fn ($p) => url("/{$locale}/{$p}"))->push(url('/'));

        return Response::view('seo.sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    })->name('sitemap');

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
