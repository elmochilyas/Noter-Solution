<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationLabel = 'Documents';

    protected static ?string $pluralLabel = 'Documents';

    protected static ?string $recordTitleAttribute = 'original_filename';

    public static function getNavigationGroup(): ?string
    {
        return 'Clients';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-folder';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Document')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('original_filename')->label('Fichier'),
                        TextEntry::make('booking.reference')->label('Réservation'),
                        TextEntry::make('mime_type')->label('Type'),
                        TextEntry::make('size_bytes')
                            ->label('Taille')
                            ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 1024, 1).' KB' : '-'),
                        TextEntry::make('scan_status')->label('Scan'),
                        TextEntry::make('purge_after')->label('Suppression le')->dateTime('d/m/Y'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn ($query) => $query->with(['booking', 'client']))
            ->columns([
                TextColumn::make('original_filename')->label('Fichier')->searchable()->limit(30),
                TextColumn::make('booking.reference')->label('Réservation')->searchable(),
                TextColumn::make('mime_type')->label('Type')->limit(20),
                TextColumn::make('size_bytes')
                    ->label('Taille')
                    ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 1024, 1).' KB' : '-'),
                TextColumn::make('scan_status')->label('Scan'),
                TextColumn::make('purge_after')->label('Suppression le')->dateTime('d/m/Y'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('scan_status')
                    ->options([
                        'pending' => 'En attente',
                        'clean' => 'Propre',
                        'suspicious' => 'Suspect',
                        'failed' => 'Échoué',
                    ]),
                SelectFilter::make('expired')
                    ->options([
                        'yes' => 'Expirés',
                        'no' => 'Non expirés',
                    ])
                    ->query(fn ($query, $state) => $state === 'yes'
                        ? $query->where('purge_after', '<=', now())
                        : $query->where(function ($q) {
                            $q->whereNull('purge_after')->orWhere('purge_after', '>', now());
                        })),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ViewAction::make(),
                Action::make('download')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Document $record) => route('admin.downloads.document', $record))
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('delete', Document::class))
                    ->after(function (Document $record): void {
                        activity()
                            ->performedOn($record)
                            ->causedBy(auth()->user())
                            ->log('Document supprimé depuis l\'administration');
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'view' => Pages\ViewDocument::route('/{record}'),
        ];
    }
}
