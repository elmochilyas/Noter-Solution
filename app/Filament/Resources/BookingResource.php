<?php

namespace App\Filament\Resources;

use App\Domain\Services\AvailabilityService;
use App\Domain\Services\BookingService;
use App\Enums\BookingStatus;
use App\Events\BookingConfirmed;
use App\Events\ReceiptGenerated;
use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\ValueObjects\TimeSlot;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationLabel = 'Réservations';

    protected static ?string $pluralLabel = 'Réservations';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getNavigationGroup(): ?string
    {
        return 'Quotidien';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar-days';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['reference', 'client.full_name', 'client.email', 'client.phone'];
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
                TextColumn::make('client.full_name')
                    ->label('Client')
                    ->searchable(query: fn (Builder $query, string $search) => $query->whereHas('client', fn ($q) => $q->where('full_name', 'like', "%{$search}%")))
                    ->sortable(),
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
                IconColumn::make('format')
                    ->label('Format')
                    ->icon(fn (string $state) => $state === 'online' ? 'heroicon-o-video-camera' : 'heroicon-o-building-office')
                    ->color(fn (string $state) => $state === 'online' ? 'info' : 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(BookingStatus::cases())->mapWithKeys(fn ($s) => [$s->value => __("booking.status.{$s->value}")])),
                SelectFilter::make('format')
                    ->options([
                        'online' => 'En ligne',
                        'in_office' => 'Au cabinet',
                    ]),
                SelectFilter::make('consultation_plan_id')
                    ->label('Formule')
                    ->relationship('plan', 'slug'),
                Filter::make('starts_at')
                    ->form([
                        TextInput::make('from')->label('Du')->type('date'),
                        TextInput::make('until')->label('Au')->type('date'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'], fn ($q, $v) => $q->whereDate('starts_at', '>=', $v))
                        ->when($data['until'], fn ($q, $v) => $q->whereDate('starts_at', '<=', $v))),
                Filter::make('has_documents')
                    ->label('Avec documents')
                    ->query(fn ($query) => $query->whereHas('documents')),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ViewAction::make(),
                ActionGroup::make([
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
                    Action::make('reschedule')
                        ->label('Reporter')
                        ->color('warning')
                        ->icon('heroicon-o-calendar')
                        ->visible(fn (Booking $record) => in_array($record->status, ['pending_payment', 'confirmed']))
                        ->form([
                            Select::make('slot')
                                ->label('Nouveau créneau')
                                ->options(fn (Booking $record) => collect(
                                    app(AvailabilityService::class)->availableSlots(
                                        $record->plan,
                                        $record->format,
                                        now()->addDay()->startOfDay(),
                                        now()->addMonth()->endOfDay(),
                                    )
                                )->mapWithKeys(fn ($slot) => [$slot->startsAt->toIso8601String() => $slot->startsAt->format('d/m/Y H:i').' - '.$slot->endsAt->format('H:i')]))
                                ->required(),
                        ])
                        ->action(function (Booking $record, array $data): void {
                            $slot = TimeSlot::fromIso($data['slot'], $record->plan->duration_minutes);
                            app(BookingService::class)->reschedule($record, $slot);
                        }),
                    Action::make('send_confirmation')
                        ->label('Renvoyer confirmation')
                        ->icon('heroicon-o-envelope')
                        ->action(function (Booking $record): void {
                            // Re-dispatches the BookingConfirmed event
                            BookingConfirmed::dispatch($record);
                        }),
                    Action::make('send_receipt')
                        ->label('Renvoyer reçu')
                        ->icon('heroicon-o-document-arrow-down')
                        ->visible(fn (Booking $record) => $record->receipt !== null)
                        ->action(function (Booking $record): void {
                            ReceiptGenerated::dispatch($record->receipt);
                        }),
                    Action::make('view_as_client')
                        ->label('Voir comme client')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalContent(fn (Booking $record): View => view('filament.resources.booking-resource.view-as-client', ['booking' => $record]))
                        ->modalWidth(MaxWidth::SevenExtraLarge)
                        ->action(function (Booking $record): void {
                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->log('Consultation du dossier en mode client');
                        }),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('export_csv')
                        ->label('Exporter en CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (Collection $records) => response()->streamDownload(function () use ($records) {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, ['Référence', 'Client', 'Email', 'Formule', 'Date', 'Statut', 'Montant', 'Format']);
                            foreach ($records as $booking) {
                                fputcsv($handle, [
                                    $booking->reference,
                                    $booking->client?->full_name,
                                    $booking->client?->email,
                                    $booking->plan?->name_translations['fr'] ?? '',
                                    $booking->starts_at?->format('d/m/Y H:i'),
                                    $booking->status,
                                    $booking->payment?->amount_centimes ? number_format($booking->payment->amount_centimes / 100, 2, ',', ' ').' MAD' : '',
                                    $booking->format,
                                ]);
                            }
                            fclose($handle);
                        }, 'reservations-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv'])),
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
