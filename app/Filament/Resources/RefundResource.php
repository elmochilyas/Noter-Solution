<?php

namespace App\Filament\Resources;

use App\Domain\Services\PaymentService;
use App\Filament\Resources\RefundResource\Pages;
use App\Models\Refund;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RefundResource extends Resource
{
    protected static ?string $model = Refund::class;

    protected static ?string $navigationLabel = 'Remboursements';

    protected static ?string $pluralLabel = 'Remboursements';

    public static function getNavigationGroup(): ?string
    {
        return 'Paiements';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-uturn-left';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Remboursement')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('payment.gateway_intent_id')->label('ID Paiement'),
                        TextEntry::make('amount_centimes')
                            ->label('Montant')
                            ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 2, ',', ' ').' MAD' : '-'),
                        TextEntry::make('reason')->label('Motif'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'requested' => 'warning',
                                'approved' => 'info',
                                'succeeded' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('requestedBy.name')->label('Demandé par'),
                        TextEntry::make('approvedBy.name')->label('Approuvé par'),
                        TextEntry::make('processed_at')->label('Traité le')->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn ($query) => $query->with(['payment', 'requestedBy', 'approvedBy']))
            ->columns([
                TextColumn::make('payment.gateway_intent_id')->label('Paiement')->limit(20),
                TextColumn::make('amount_centimes')
                    ->label('Montant')
                    ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 2, ',', ' ').' MAD' : '-'),
                TextColumn::make('reason')->label('Motif')->limit(30),
                TextColumn::make('requestedBy.name')->label('Demandé par'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'requested' => 'warning',
                        'approved' => 'info',
                        'succeeded' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'requested' => 'Demandé',
                        'approved' => 'Approuvé',
                        'succeeded' => 'Réussi',
                        'failed' => 'Échoué',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('Approuver')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (Refund $record) => $record->status === 'requested' && auth()->user()->can('approve', $record))
                    ->requiresConfirmation()
                    ->action(function (Refund $record): void {
                        try {
                            app(PaymentService::class)->processRefund($record);
                            Notification::make()->title('Remboursement approuvé')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Erreur: '.$e->getMessage())->danger()->send();
                        }
                    }),
                Action::make('reject')
                    ->label('Rejeter')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn (Refund $record) => $record->status === 'requested' && auth()->user()->can('reject', $record))
                    ->requiresConfirmation()
                    ->action(function (Refund $record): void {
                        $record->update(['status' => 'failed', 'processed_at' => now()]);
                        Notification::make()->title('Remboursement rejeté')->warning()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRefunds::route('/'),
            'view' => Pages\ViewRefund::route('/{record}'),
        ];
    }
}
