<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Domain\Services\PaymentService;
use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use App\ValueObjects\MoneyMad;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('request_refund')
                ->label('Demander un remboursement')
                ->color('warning')
                ->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn (Payment $record) => $record->status === 'succeeded')
                ->form([
                    TextInput::make('amount_centimes')
                        ->label('Montant (MAD)')
                        ->numeric()
                        ->required()
                        ->default(fn (Payment $record) => $record->amount_centimes)
                        ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 2, ',', '') : '0')
                        ->mutateDehydrate(fn (?string $state): int => (int) round(((float) str_replace(',', '.', $state ?? '0')) * 100)),
                    TextInput::make('reason')
                        ->label('Motif')
                        ->required()
                        ->maxLength(500),
                ])
                ->action(function (Payment $record, array $data): void {
                    app(PaymentService::class)->refund(
                        $record,
                        new MoneyMad((int) $data['amount_centimes']),
                        $data['reason'],
                        auth()->user(),
                    );

                    Notification::make()
                        ->title('Remboursement demandé')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Section::make('Paiement')
                            ->schema([
                                TextEntry::make('gateway_intent_id')->label('ID Intent'),
                                TextEntry::make('gateway')->label('Passerelle'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (?string $state) => match ($state) {
                                        'succeeded' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('amount_centimes')
                                    ->label('Montant')
                                    ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 2, ',', ' ').' MAD' : '-'),
                                TextEntry::make('paid_at')->label('Payé le')->dateTime('d/m/Y H:i'),
                            ]),
                        Section::make('Réservation liée')
                            ->schema([
                                TextEntry::make('booking.reference')->label('Référence'),
                                TextEntry::make('booking.status')->label('Statut'),
                            ]),
                    ]),
                Section::make('Remboursements')
                    ->schema(function ($record) {
                        if ($record->refunds->isEmpty()) {
                            return [TextEntry::make('no_refunds')->label('')->default('Aucun remboursement')];
                        }

                        return $record->refunds->map(fn ($refund, $i) => TextEntry::make("refund_{$refund->id}")
                            ->label('Remboursement #'.($i + 1))
                            ->formatStateUsing(fn () => sprintf(
                                '%s MAD - %s (%s)',
                                number_format($refund->amount_centimes / 100, 2, ',', ' '),
                                $refund->status,
                                $refund->processed_at?->format('d/m/Y H:i') ?? 'en attente',
                            )))->toArray();
                    }),
            ]);
    }
}
