<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceiptResource\Pages;
use App\Models\Receipt;
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

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static ?string $navigationLabel = 'Reçus';

    protected static ?string $pluralLabel = 'Reçus';

    protected static ?string $recordTitleAttribute = 'number';

    public static function getNavigationGroup(): ?string
    {
        return 'Paiements';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reçu')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('number')->label('Numéro'),
                        TextEntry::make('booking.reference')->label('Réservation'),
                        TextEntry::make('amount_centimes')
                            ->label('Montant')
                            ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 2, ',', ' ').' MAD' : '-'),
                        TextEntry::make('vat_centimes')
                            ->label('TVA')
                            ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 2, ',', ' ').' MAD' : '-'),
                        TextEntry::make('issued_at')->label('Émis le')->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn ($query) => $query->with('booking'))
            ->columns([
                TextColumn::make('number')->label('Numéro')->searchable()->sortable(),
                TextColumn::make('booking.reference')->label('Réservation')->searchable(),
                TextColumn::make('amount_centimes')
                    ->label('Montant')
                    ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 2, ',', ' ').' MAD' : '-'),
                TextColumn::make('issued_at')->label('Émis le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('issued_at', 'desc')
            ->filters([
                SelectFilter::make('year')
                    ->options(fn () => collect(range(now()->year, 2024))->mapWithKeys(fn ($y) => [$y => $y]))
                    ->query(fn ($query, $state) => $query->whereYear('issued_at', $state['value'])),
                SelectFilter::make('month')
                    ->options([
                        1 => 'Janvier', 2 => 'Février', 3 => 'Mars',
                        4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
                        7 => 'Juillet', 8 => 'Août', 9 => 'Septembre',
                        10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                    ])
                    ->query(fn ($query, $state) => $query->whereMonth('issued_at', $state['value'])),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ViewAction::make(),
                Action::make('download')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Receipt $record) => route('admin.downloads.receipt', $record))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReceipts::route('/'),
            'view' => Pages\ViewReceipt::route('/{record}'),
        ];
    }
}
