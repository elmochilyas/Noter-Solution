<?php

namespace App\Filament\Resources;

use App\Domain\Services\BookingService;
use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationLabel = 'Réservations';

    protected static ?string $pluralLabel = 'Réservations';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar-days';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Réservation')
                    ->components([
                        TextInput::make('reference')->disabled(),
                        TextInput::make('status')->disabled(),
                        TextInput::make('starts_at')->disabled(),
                        TextInput::make('ends_at')->disabled(),
                    ]),
                Section::make('Notes internes')
                    ->components([
                        Textarea::make('internal_notes')
                            ->label('')
                            ->rows(4),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn ($query) => $query->with(['client', 'plan', 'payment', 'receipt', 'documents']))
            ->columns([
                TextColumn::make('reference')->searchable()->sortable(),
                TextColumn::make('client.full_name')->label('Client')->searchable(),
                TextColumn::make('plan.name_translations.fr')->label('Formule'),
                TextColumn::make('starts_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'confirmed' => 'success',
                        'pending_payment' => 'warning',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                        'no_show' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => __("booking.status.{$state}")),
                TextColumn::make('payment.amount_centimes')
                    ->label('Montant')
                    ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 2, ',', ' ').' MAD' : '-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(BookingStatus::cases())->mapWithKeys(fn ($s) => [$s->value => __("booking.status.{$s->value}")])),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ViewAction::make(),
                Action::make('cancel')
                    ->label('Annuler')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Booking $record) => in_array($record->status, ['pending_payment', 'confirmed']))
                    ->form([
                        Textarea::make('reason')->label('Motif')->required(),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        app(BookingService::class)->cancel($record, $data['reason'], auth()->user());
                    }),
                Action::make('complete')
                    ->label('Terminer')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Booking $record) => $record->status === 'confirmed')
                    ->action(fn (Booking $record) => app(BookingService::class)->complete($record)),
                Action::make('mark_no_show')
                    ->label('Absent')
                    ->color('gray')
                    ->icon('heroicon-o-user-x')
                    ->visible(fn (Booking $record) => $record->status === 'confirmed')
                    ->action(fn (Booking $record) => app(BookingService::class)->markNoShow($record)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'view' => Pages\ViewBooking::route('/{record}'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
