<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Domain\Services\BookingService;
use App\Domain\Services\PaymentService;
use App\Filament\Resources\BookingResource;
use App\Filament\Resources\ClientResource;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    public function getTitle(): string
    {
        return "Réservation {$this->record->reference}";
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $user = auth()->user();
        $cacheKey = "viewed_notes:booking:{$this->record->id}:user:{$user->id}";

        if (! Cache::has($cacheKey)) {
            activity()
                ->performedOn($this->record)
                ->causedBy($user)
                ->log('Notes internes consultées par '.$user->name);

            Cache::put($cacheKey, true, now()->addHour());
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
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
            ]),

            Action::make('mark_cash_succeeded')
                ->label('Marquer paiement cash reçu')
                ->color('success')
                ->icon('heroicon-o-currency-dollar')
                ->visible(fn (Booking $record) => $record->payment?->gateway === 'cash' && $record->payment?->status === 'pending')
                ->requiresConfirmation()
                ->action(function (Booking $record): void {
                    app(PaymentService::class)->markCashSucceeded($record->payment, auth()->user());
                    activity()
                        ->performedOn($record)
                        ->causedBy(auth()->user())
                        ->log('Paiement cash marqué comme reçu par '.auth()->user()->name);
                }),

            Action::make('approve_refund')
                ->label('Approuver le remboursement')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn (Booking $record) => $record->payment?->refunds()->where('status', 'requested')->exists())
                ->requiresConfirmation()
                ->action(function (Booking $record): void {
                    $refund = $record->payment->refunds()->where('status', 'requested')->first();
                    if ($refund) {
                        app(PaymentService::class)->processRefund($refund);
                    }
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Section::make('Réservation')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('reference')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('status')
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
                                TextEntry::make('plan.name_translations.fr')->label('Formule'),
                                TextEntry::make('format')->label('Format'),
                                TextEntry::make('starts_at')->label('Début')->dateTime('d/m/Y H:i'),
                                TextEntry::make('ends_at')->label('Fin')->dateTime('d/m/Y H:i'),
                                TextEntry::make('service_category')->label('Catégorie'),
                                TextEntry::make('description')->label('Description')->columnSpanFull(),
                            ]),

                        Section::make('Client')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('client.full_name')->label('Nom')
                                    ->url(fn ($record) => $record->client ? Page::getResourceUrl(ClientResource::class, 'view', [$record->client]) : null),
                                TextEntry::make('client.email')->label('Email'),
                                TextEntry::make('client.phone')->label('Téléphone'),
                                TextEntry::make('client.preferred_locale')->label('Langue'),
                            ]),
                    ]),

                Grid::make(2)
                    ->schema([
                        Section::make('Paiement')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('payment.gateway')->label('Passerelle'),
                                TextEntry::make('payment.status')->label('Statut')
                                    ->badge()
                                    ->color(fn (?string $state) => match ($state) {
                                        'succeeded' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('payment.amount_centimes')
                                    ->label('Montant')
                                    ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 2, ',', ' ').' MAD' : '-'),
                                TextEntry::make('payment.paid_at')->label('Payé le')->dateTime('d/m/Y H:i'),
                            ]),

                        Section::make('Reçu')
                            ->schema([
                                TextEntry::make('receipt.number')->label('Numéro'),
                                TextEntry::make('receipt.issued_at')->label('Émis le')->dateTime('d/m/Y H:i'),
                                TextEntry::make('receipt')
                                    ->label('')
                                    ->visible(fn ($record) => $record->receipt !== null)
                                    ->formatStateUsing(fn ($state, $record) => '<a href="'.route('admin.downloads.receipt', $record->receipt).'" class="text-primary-600 underline">Télécharger le PDF</a>')
                                    ->html(),
                            ])
                            ->visible(fn ($record) => $record->receipt !== null),
                    ]),

                Section::make('Remboursements')
                    ->schema(function ($record) {
                        if (! $record->payment?->refunds->isEmpty()) {
                            return $record->payment->refunds->map(fn ($refund, $i) => TextEntry::make("refund_{$refund->id}")
                                ->label('Remboursement #'.($i + 1))
                                ->formatStateUsing(fn () => sprintf(
                                    '%s MAD - %s (%s)',
                                    number_format($refund->amount_centimes / 100, 2, ',', ' '),
                                    $refund->status,
                                    $refund->processed_at?->format('d/m/Y H:i') ?? 'en attente',
                                )))->toArray();
                        }

                        return [TextEntry::make('no_refund')->label('Remboursements')->default('Aucun')];
                    })
                    ->visible(fn ($record) => $record->payment !== null),

                Section::make('Documents')
                    ->schema(function ($record) {
                        if ($record->documents->isEmpty()) {
                            return [TextEntry::make('no_docs')->label('')->default('Aucun document')];
                        }

                        return $record->documents->map(fn ($doc) => TextEntry::make("doc_{$doc->id}")
                            ->label($doc->original_filename)
                            ->formatStateUsing(fn () => '<a href="'.route('admin.downloads.document', $doc).'" class="text-primary-600 underline">Télécharger</a>')
                            ->html())->toArray();
                    }),

                Section::make('Notes internes')
                    ->schema([
                        TextEntry::make('internal_notes')
                            ->label(''),
                    ]),

                Section::make('Journal d\'activité')
                    ->schema(function ($record) {
                        $activities = Activity::where('subject_type', Booking::class)
                            ->where('subject_id', $record->id)
                            ->latest()
                            ->take(20)
                            ->get();

                        if ($activities->isEmpty()) {
                            return [TextEntry::make('no_activity')->label('')->default('Aucune activité')];
                        }

                        return $activities->map(fn ($activity) => TextEntry::make("activity_{$activity->id}")
                            ->label($activity->created_at->format('d/m/Y H:i'))
                            ->formatStateUsing(fn () => ($activity->causer?->name ?? 'Système').' — '.$activity->description)
                            ->html())->toArray();
                    }),
            ]);
    }
}
