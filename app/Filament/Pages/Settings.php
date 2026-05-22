<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->view = 'filament.pages.settings';
        $this->form->fill(Setting::practiceInfo());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Practice Information')
                    ->description('ICE, IF, RC, Patente, and contact details')
                    ->schema([
                        TextInput::make('ice')->label('ICE')->required()->maxLength(15),
                        TextInput::make('if')->label('IF')->maxLength(15),
                        TextInput::make('rc')->label('RC')->maxLength(15),
                        TextInput::make('patente')->label('Patente')->maxLength(15),
                        TextInput::make('phone')->label('Phone')->tel()->maxLength(20),
                        TextInput::make('mobile')->label('Mobile')->tel()->maxLength(20),
                        TextInput::make('whatsapp')->label('WhatsApp')->tel()->maxLength(20),
                        TextInput::make('email')->label('Email')->email()->maxLength(255),
                        TextInput::make('address')->label('Address')->maxLength(500),
                        TextInput::make('hours_fr')->label('Hours (French)')->maxLength(100),
                        TextInput::make('hours_ar')->label('Hours (Arabic)')->maxLength(100),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Setting::set('practice_info', $this->form->getState());

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->action('save'),
        ];
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form($this->makeForm()),
        ];
    }
}
