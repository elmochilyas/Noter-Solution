<?php

namespace App\Providers;

use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingRescheduled;
use App\Events\ContactMessageReceived;
use App\Events\MagicLinkRequested;
use App\Events\PaymentFailed;
use App\Events\PaymentSucceeded;
use App\Events\ReceiptGenerated;
use App\Events\RefundIssued;
use App\Listeners\ConfirmBooking;
use App\Listeners\GenerateCreditNote;
use App\Listeners\GenerateReceipt;
use App\Listeners\IssueRefundIfApplicable;
use App\Listeners\LogAuthActivity;
use App\Listeners\NotifyPaymentFailed;
use App\Listeners\RescheduleReminders;
use App\Listeners\ScheduleReminders;
use App\Listeners\SendBookingCancelledNotifications;
use App\Listeners\SendBookingConfirmationNotifications;
use App\Listeners\SendBookingRescheduledNotifications;
use App\Listeners\SendContactMessageNotification;
use App\Listeners\SendMagicLinkNotification;
use App\Listeners\SendReceiptNotification;
use App\Listeners\SendRefundIssuedNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogAuthActivity::class.'@handleLogin',
        ],
        Logout::class => [
            LogAuthActivity::class.'@handleLogout',
        ],
        ContactMessageReceived::class => [
            SendContactMessageNotification::class,
        ],

        BookingConfirmed::class => [
            SendBookingConfirmationNotifications::class,
            ScheduleReminders::class,
        ],

        BookingCancelled::class => [
            SendBookingCancelledNotifications::class,
            IssueRefundIfApplicable::class,
        ],

        BookingRescheduled::class => [
            SendBookingRescheduledNotifications::class,
            RescheduleReminders::class,
        ],

        PaymentSucceeded::class => [
            ConfirmBooking::class,
            GenerateReceipt::class,
        ],

        PaymentFailed::class => [
            NotifyPaymentFailed::class,
        ],

        MagicLinkRequested::class => [
            SendMagicLinkNotification::class,
        ],

        ReceiptGenerated::class => [
            SendReceiptNotification::class,
        ],

        RefundIssued::class => [
            SendRefundIssuedNotification::class,
            GenerateCreditNote::class,
        ],
    ];
}
