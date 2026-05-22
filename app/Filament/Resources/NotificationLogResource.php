<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationLogResource\Pages;
use App\Models\NotificationLog;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NotificationLogResource extends Resource
{
    protected static ?string $model = NotificationLog::class;

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $pluralLabel = 'Notifications';

    public static function getNavigationGroup(): ?string
    {
        return 'Système';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-bell';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Notification')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('channel')->label('Canal'),
                        TextEntry::make('template_key')->label('Template'),
                        TextEntry::make('recipient_type')->label('Type destinataire'),
                        TextEntry::make('recipient_id')->label('ID destinataire'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'sent' => 'info',
                                'delivered' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('provider_message_id')->label('ID fournisseur'),
                        TextEntry::make('sent_at')->label('Envoyé le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('delivered_at')->label('Délivré le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('failed_at')->label('Échec le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('failure_reason')->label('Raison échec')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('channel')->label('Canal'),
                TextColumn::make('template_key')->label('Template')->limit(30),
                TextColumn::make('recipient_id')->label('Destinataire'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'sent' => 'info',
                        'delivered' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('channel')
                    ->options([
                        'mail' => 'Email',
                        'sms' => 'SMS',
                        'whatsapp' => 'WhatsApp',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'sent' => 'Envoyé',
                        'delivered' => 'Délivré',
                        'failed' => 'Échoué',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationLogs::route('/'),
            'view' => Pages\ViewNotificationLog::route('/{record}'),
        ];
    }
}
