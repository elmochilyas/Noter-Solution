<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationLabel = 'Journal d\'activité';

    protected static ?string $pluralLabel = 'Journal d\'activité';

    public static function getNavigationGroup(): ?string
    {
        return 'Système';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clipboard-document-list';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn ($query) => $query->with('causer'))
            ->columns([
                TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i:s')->sortable(),
                TextColumn::make('causer.name')->label('Acteur'),
                TextColumn::make('description')->label('Action')->limit(60),
                TextColumn::make('subject_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state) => class_basename($state)),
                TextColumn::make('subject_id')->label('ID'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('subject_type')
                    ->options([
                        'App\Models\Booking' => 'Booking',
                        'App\Models\Client' => 'Client',
                        'App\Models\Payment' => 'Payment',
                        'App\Models\User' => 'User',
                        'App\Models\Document' => 'Document',
                        'App\Models\ContactMessage' => 'ContactMessage',
                    ])
                    ->query(fn ($query, $state) => $query->where('subject_type', $state['value'])),
                SelectFilter::make('event')
                    ->options([
                        'created' => 'Création',
                        'updated' => 'Modification',
                        'deleted' => 'Suppression',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->searchable();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isOwner() ?? false;
    }
}
