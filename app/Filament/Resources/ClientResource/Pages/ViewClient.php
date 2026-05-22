<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Jobs\GenerateClientDataExport;
use App\Models\Client;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;
use Spatie\Activitylog\Models\Activity;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_data')
                ->label('Exporter les données')
                ->color('warning')
                ->icon('heroicon-o-archive-box')
                ->action(function (): void {
                    /** @var Client $client */
                    $client = $this->record;

                    GenerateClientDataExport::dispatch($client, auth()->user());

                    Notification::make()
                        ->title('Export en cours')
                        ->body('Les données seront envoyées par email une fois prêtes.')
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
                        Section::make('Client')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('full_name')->label('Nom'),
                                TextEntry::make('email')->label('Email'),
                                TextEntry::make('phone')->label('Téléphone'),
                                TextEntry::make('preferred_locale')->label('Langue'),
                                TextEntry::make('preferred_channel')->label('Canal préféré'),
                                TextEntry::make('last_login_at')->label('Dernière connexion')->dateTime('d/m/Y H:i'),
                            ]),
                        Section::make('Statistiques')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('bookings_count')
                                    ->label('Nombre de rendez-vous')
                                    ->default(fn ($record) => $record->bookings()->count()),
                                TextEntry::make('documents_count')
                                    ->label('Documents')
                                    ->default(fn ($record) => $record->documents()->count()),
                            ]),
                    ]),

                Section::make('Historique des rendez-vous')
                    ->schema(function ($record) {
                        $bookings = $record->bookings()->latest()->take(10)->get();

                        if ($bookings->isEmpty()) {
                            return [TextEntry::make('no_bookings')->label('')->default('Aucun rendez-vous')];
                        }

                        return $bookings->map(fn ($booking) => TextEntry::make("booking_{$booking->id}")
                            ->label($booking->starts_at?->format('d/m/Y H:i'))
                            ->formatStateUsing(fn () => sprintf(
                                '%s — %s — %s MAD',
                                $booking->reference,
                                $booking->status,
                                $booking->total_centimes ? number_format($booking->total_centimes / 100, 2, ',', ' ') : '0',
                            ))->html())->toArray();
                    }),

                Section::make('Documents')
                    ->schema(function ($record) {
                        $docs = $record->documents()->latest()->take(10)->get();

                        if ($docs->isEmpty()) {
                            return [TextEntry::make('no_docs')->label('')->default('Aucun document')];
                        }

                        return $docs->map(fn ($doc) => TextEntry::make("doc_{$doc->id}")
                            ->label($doc->original_filename)
                            ->formatStateUsing(fn () => sprintf(
                                '%s — %s',
                                $doc->mime_type,
                                $doc->created_at->format('d/m/Y'),
                            ))->html())->toArray();
                    }),

                Section::make('Journal d\'activité')
                    ->schema(function ($record) {
                        $activities = Activity::where('subject_type', Client::class)
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
