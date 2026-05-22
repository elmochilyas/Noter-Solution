<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationLabel = 'Paiements';

    protected static ?string $pluralLabel = 'Paiements';

    protected static ?string $recordTitleAttribute = 'gateway_intent_id';

    public static function getNavigationGroup(): ?string
    {
        return 'Paiements';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-currency-dollar';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Paiement')
                    ->columns(2)
                    ->schema([
                        TextInput::make('gateway_intent_id')->disabled(),
                        TextInput::make('gateway')->disabled(),
                        TextInput::make('status')->disabled(),
                        TextInput::make('amount_centimes')
                            ->disabled()
                            ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 2, ',', ' ').' MAD' : '-'),
                        TextInput::make('paid_at')->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn ($query) => $query->with(['booking', 'refunds']))
            ->columns([
                TextColumn::make('gateway_intent_id')->label('ID Intent')->searchable()->limit(20),
                TextColumn::make('booking.reference')->label('Réservation')->searchable(),
                TextColumn::make('amount_centimes')
                    ->label('Montant')
                    ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 2, ',', ' ').' MAD' : '-')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'succeeded' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('paid_at')->label('Payé le')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('refunds_count')
                    ->label('Remb.')
                    ->counts('refunds'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'succeeded' => 'Réussi',
                        'failed' => 'Échoué',
                    ]),
                SelectFilter::make('gateway')
                    ->options([
                        'stripe' => 'Stripe',
                        'cash' => 'Espèces',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
