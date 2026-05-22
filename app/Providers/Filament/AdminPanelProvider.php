<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Reports;
use App\Filament\Pages\Settings;
use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\AvailabilityExceptionResource;
use App\Filament\Resources\AvailabilityRuleResource;
use App\Filament\Resources\BookingResource;
use App\Filament\Resources\ChatbotConversationResource;
use App\Filament\Resources\ClientResource;
use App\Filament\Resources\ConsultationPlanResource;
use App\Filament\Resources\ContactMessageResource;
use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\FaqResource;
use App\Filament\Resources\NotificationLogResource;
use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\ReceiptResource;
use App\Filament\Resources\RefundResource;
use App\Filament\Resources\ServiceResource;
use App\Filament\Resources\UserResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->darkMode(false)
            ->colors([
                'primary' => Color::hex('#B68A3E'),
            ])
            ->font('Inter')
            ->viteTheme('resources/css/filament/admin.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->groups([
                        NavigationGroup::make('Quotidien')
                            ->items([
                                NavigationItem::make('Tableau de bord')
                                    ->icon('heroicon-o-home')
                                    ->url(fn () => Dashboard::getUrl())
                                    ->isActiveWhen(fn () => request()->routeIs('filament.admin.pages.dashboard')),
                                ...BookingResource::getNavigationItems(),
                                ...ContactMessageResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Clients')
                            ->items([
                                ...ClientResource::getNavigationItems(),
                                ...DocumentResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Paiements')
                            ->items([
                                ...PaymentResource::getNavigationItems(),
                                ...RefundResource::getNavigationItems(),
                                ...ReceiptResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Contenu')
                            ->items([
                                ...ServiceResource::getNavigationItems(),
                                ...FaqResource::getNavigationItems(),
                                ...ConsultationPlanResource::getNavigationItems(),
                                ...AvailabilityRuleResource::getNavigationItems(),
                                ...AvailabilityExceptionResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Chatbot')
                            ->items([
                                ...ChatbotConversationResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Système')
                            ->items([
                                ...NotificationLogResource::getNavigationItems(),
                                ...ActivityLogResource::getNavigationItems(),
                                ...UserResource::getNavigationItems(),
                                NavigationItem::make('Paramètres')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->url(fn () => Settings::getUrl())
                                    ->isActiveWhen(fn () => request()->routeIs('filament.admin.pages.settings')),
                                NavigationItem::make('Rapports')
                                    ->icon('heroicon-o-document-chart-bar')
                                    ->url(fn () => Reports::getUrl())
                                    ->isActiveWhen(fn () => request()->routeIs('filament.admin.pages.reports')),
                                NavigationItem::make('Pulse')
                                    ->icon('heroicon-o-activity')
                                    ->url('/admin/pulse')
                                    ->visible(fn () => auth()->user()?->isOwner())
                                    ->isActiveWhen(fn () => request()->is('admin/pulse*')),
                            ]),
                    ]);
            })
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
