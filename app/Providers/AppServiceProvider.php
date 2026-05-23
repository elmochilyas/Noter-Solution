<?php

namespace App\Providers;

use App\Channels\TwilioSmsChannel;
use App\Channels\TwilioWhatsAppChannel;
use App\Domain\Payment\PaymentGateway;
use App\Domain\Services\Chatbot\Contracts\LlmClient;
use App\Domain\Services\VirusScanner\Contracts\VirusScanner;
use App\Infrastructure\Chatbot\CerebrasClient;
use App\Models\Client;
use App\Observers\ClientObserver;
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

        $this->app->bind(LlmClient::class, CerebrasClient::class);

        $this->app->bind(VirusScanner::class, function () {
            $driver = config('virus-scanner.default', 'null');

            $class = config("virus-scanner.drivers.{$driver}");

            if (! $class || ! class_exists($class)) {
                throw new \RuntimeException("Virus scanner driver '{$driver}' is not configured.");
            }

            if ($driver === 'clamav') {
                return new $class(
                    host: config('virus-scanner.clamav.host', '127.0.0.1'),
                    port: (int) config('virus-scanner.clamav.port', 3310),
                    timeout: (float) config('virus-scanner.clamav.timeout', 30.0),
                );
            }

            return new $class;
        });
    }

    public function boot(): void
    {
        Client::observe(ClientObserver::class);

        $this->app->when(TwilioSmsChannel::class)
            ->needs('$from')
            ->give(config('services.twilio.from_sms'));

        $this->app->when(TwilioWhatsAppChannel::class)
            ->needs('$from')
            ->give(config('services.twilio.from_whatsapp'));
    }
}
