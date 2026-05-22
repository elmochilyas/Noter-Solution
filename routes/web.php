<?php

use App\Http\Controllers\Admin\DownloadController as AdminDownloadController;
use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\Portal\AccountDeletionController;
use App\Http\Controllers\Portal\BookingController as PortalBookingController;
use App\Http\Controllers\Portal\CancelController as PortalCancelController;
use App\Http\Controllers\Portal\DownloadController;
use App\Http\Controllers\Portal\PreferenceController;
use App\Http\Controllers\Portal\ReceiptController as PortalReceiptController;
use App\Http\Controllers\Portal\RescheduleController as PortalRescheduleController;
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

Route::middleware(['web', 'auth'])->prefix('admin/downloads')->name('admin.downloads.')->group(function () {
    Route::get('receipts/{receipt}', [AdminDownloadController::class, 'receipt'])->name('receipt');
    Route::get('documents/{document}', [AdminDownloadController::class, 'document'])->name('document');
});

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

        Route::middleware(['auth:client', 'client.session'])->group(function () {
            Route::get('dashboard', fn () => view('portal.dashboard'))->name('dashboard');

            Route::get('bookings', [PortalBookingController::class, 'index'])->name('bookings.index');
            Route::get('bookings/{reference}', [PortalBookingController::class, 'show'])->name('bookings.show');

            Route::get('bookings/{reference}/cancel', [PortalCancelController::class, 'confirm'])->name('bookings.cancel.confirm');
            Route::post('bookings/{reference}/cancel', [PortalCancelController::class, 'destroy'])->name('bookings.cancel.destroy');

            Route::get('bookings/{reference}/reschedule', [PortalRescheduleController::class, 'edit'])->name('bookings.reschedule.edit');
            Route::post('bookings/{reference}/reschedule', [PortalRescheduleController::class, 'update'])->name('bookings.reschedule.update');

            Route::get('bookings/{reference}/documents/{document}', [DownloadController::class, 'document'])->name('bookings.documents.download');
            Route::get('bookings/{reference}/receipt/{receipt}', [DownloadController::class, 'receipt'])->name('bookings.receipt.download');

            Route::get('receipts', [PortalReceiptController::class, 'index'])->name('receipts.index');

            Route::get('preferences', [PreferenceController::class, 'edit'])->name('preferences.edit');
            Route::post('preferences', [PreferenceController::class, 'update'])->name('preferences.update');

            Route::get('account/delete', [AccountDeletionController::class, 'confirm'])->name('account.delete.confirm');
            Route::post('account/delete', [AccountDeletionController::class, 'destroy'])->name('account.delete.destroy');
        });
    });
});
