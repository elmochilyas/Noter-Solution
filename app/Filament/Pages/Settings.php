<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page
{
    protected static ?string $navigationLabel = 'Paramètres';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $slug = 'settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->view = 'filament.pages.settings';
        $this->form->fill([
            'practice_info' => Setting::get('practice_info', []),
            'refund_policy' => Setting::get('refund_policy', [
                'cancellation_hours' => 24,
                'reschedule_hours' => 2,
            ]),
            'quiet_hours' => Setting::get('quiet_hours', [
                'start' => '22:00',
                'end' => '07:00',
            ]),
            'booking_lead_time' => Setting::get('booking_lead_time', 2),
            'vat' => Setting::get('vat', [
                'rate' => 20,
                'enabled' => false,
            ]),
            'feature_flags' => Setting::get('feature_flags', [
                'chatbot_enabled' => false,
                'online_payment_enabled' => true,
                'free_orientation_enabled' => true,
            ]),
        ]);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Système';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du cabinet')
                    ->description('ICE, IF, RC, Patente et coordonnées')
                    ->schema([
                        TextInput::make('practice_info.ice')->label('ICE')->maxLength(15),
                        TextInput::make('practice_info.if')->label('IF')->maxLength(15),
                        TextInput::make('practice_info.rc')->label('RC')->maxLength(15),
                        TextInput::make('practice_info.patente')->label('Patente')->maxLength(15),
                        TextInput::make('practice_info.phone')->label('Téléphone')->tel()->maxLength(20),
                        TextInput::make('practice_info.mobile')->label('Mobile')->tel()->maxLength(20),
                        TextInput::make('practice_info.whatsapp')->label('WhatsApp')->tel()->maxLength(20),
                        TextInput::make('practice_info.email')->label('Email')->email()->maxLength(255),
                        TextInput::make('practice_info.address')->label('Adresse')->maxLength(500),
                        TextInput::make('practice_info.hours_fr')->label('Horaires (FR)')->maxLength(100),
                        TextInput::make('practice_info.hours_ar')->label('Horaires (AR)')->maxLength(100),
                    ])->columns(2),

                Section::make('Politique de remboursement')
                    ->schema([
                        TextInput::make('refund_policy.cancellation_hours')
                            ->label('Délai d\'annulation (heures)')
                            ->numeric()
                            ->default(24),
                        TextInput::make('refund_policy.reschedule_hours')
                            ->label('Délai de report (heures)')
                            ->numeric()
                            ->default(2),
                    ])->columns(2),

                Section::make('Heures silencieuses')
                    ->description('Aucune notification SMS/WhatsApp pendant cette plage')
                    ->schema([
                        TimePicker::make('quiet_hours.start')->label('Début')->default('22:00'),
                        TimePicker::make('quiet_hours.end')->label('Fin')->default('07:00'),
                    ])->columns(2),

                Section::make('Délai de réservation')
                    ->description('Délai minimum avant un rendez-vous (heures)')
                    ->schema([
                        TextInput::make('booking_lead_time')
                            ->label('Délai (heures)')
                            ->numeric()
                            ->default(2),
                    ]),

                Section::make('TVA')
                    ->schema([
                        Toggle::make('vat.enabled')->label('TVA appliquée'),
                        TextInput::make('vat.rate')
                            ->label('Taux (%)')
                            ->numeric()
                            ->default(20),
                    ])->columns(2),

                Section::make('Fonctionnalités')
                    ->schema([
                        Toggle::make('feature_flags.chatbot_enabled')->label('Chatbot activé'),
                        Toggle::make('feature_flags.online_payment_enabled')->label('Paiement en ligne activé'),
                        Toggle::make('feature_flags.free_orientation_enabled')->label('Orientation gratuite activée'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->authorize('update', Setting::class);

        $state = $this->form->getState();

        Setting::set('practice_info', $state['practice_info'] ?? []);
        Setting::set('refund_policy', $state['refund_policy'] ?? []);
        Setting::set('quiet_hours', $state['quiet_hours'] ?? []);
        Setting::set('booking_lead_time', $state['booking_lead_time'] ?? 2);
        Setting::set('vat', $state['vat'] ?? []);
        Setting::set('feature_flags', $state['feature_flags'] ?? []);

        activity()
            ->causedBy(auth()->user())
            ->log('Paramètres mis à jour');

        Notification::make()
            ->title('Paramètres enregistrés')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Enregistrer')
                ->action('save'),
        ];
    }
}
