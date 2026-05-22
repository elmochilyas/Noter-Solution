<?php

namespace App\Providers;

use App\Channels\TwilioSmsChannel;
use App\Channels\TwilioWhatsAppChannel;
use App\Domain\Payment\PaymentGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function () {
            $driver = config('payments.default', 'stripe');

            $class = config("payments.gateways.{$driver}");

            if (! $class || ! class_exists($class)) {
                throw new \RuntimeException("Payment gateway driver '{$driver}' is not configured.");
            }

            return new $class;
        });
    }

    public function boot(): void
    {
        $this->app->when(TwilioSmsChannel::class)
            ->needs('$from')
            ->give(config('services.twilio.from_sms'));

        $this->app->when(TwilioWhatsAppChannel::class)
            ->needs('$from')
            ->give(config('services.twilio.from_whatsapp'));
    }
}
