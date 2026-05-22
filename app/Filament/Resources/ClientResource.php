<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationLabel = 'Clients';

    protected static ?string $pluralLabel = 'Clients';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getNavigationGroup(): ?string
    {
        return 'Clients';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['full_name', 'email', 'phone'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->label('Nom')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->label('Téléphone'),
                TextColumn::make('preferred_locale')->label('Langue'),
                TextColumn::make('bookings_count')->label('Rendez-vous')->counts('bookings'),
                TextColumn::make('last_login_at')->label('Dernière connexion')->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('preferred_locale')
                    ->label('Langue')
                    ->options([
                        'fr' => 'Français',
                        'ar' => 'العربية',
                    ]),
                Filter::make('has_active_booking')
                    ->label('Avec rendez-vous actif')
                    ->query(fn (Builder $query) => $query->whereHas('bookings', fn ($q) => $q->whereIn('status', ['pending_payment', 'confirmed']))),
                Filter::make('no_bookings_90d')
                    ->label('Aucun RDV depuis 90j')
                    ->query(fn (Builder $query) => $query->whereDoesntHave('bookings', fn ($q) => $q->where('created_at', '>=', now()->subDays(90)))),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ViewAction::make(),
                Action::make('anonymize')
                    ->label('Anonymiser')
                    ->color('danger')
                    ->icon('heroicon-o-shield-exclamation')
                    ->visible(fn () => auth()->user()->can('delete', Client::class))
                    ->requiresConfirmation()
                    ->action(function (Client $record): void {
                        $record->update([
                            'email' => 'deleted-'.$record->uuid.'@anonymized.local',
                            'full_name' => '(supprimé)',
                            'phone' => '+212000000000',
                            'national_id' => null,
                            'national_id_last4' => null,
                        ]);

                        activity()
                            ->performedOn($record)
                            ->causedBy(auth()->user())
                            ->log('Client anonymisé depuis l\'administration');
                    }),
                Action::make('toggle_active')
                    ->label(fn (Client $record) => $record->trashed() ? 'Réactiver' : 'Désactiver')
                    ->color(fn (Client $record) => $record->trashed() ? 'success' : 'gray')
                    ->icon(fn (Client $record) => $record->trashed() ? 'heroicon-o-arrow-uturn-up' : 'heroicon-o-pause')
                    ->visible(fn () => auth()->user()->can('delete', Client::class))
                    ->action(function (Client $record): void {
                        if ($record->trashed()) {
                            $record->restore();
                        } else {
                            $record->delete();
                        }
                    }),
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
            'index' => Pages\ListClients::route('/'),
            'view' => Pages\ViewClient::route('/{record}'),
        ];
    }
}
