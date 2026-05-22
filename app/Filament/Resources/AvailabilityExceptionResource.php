<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AvailabilityExceptionResource\Pages;
use App\Models\AvailabilityException;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AvailabilityExceptionResource extends Resource
{
    protected static ?string $model = AvailabilityException::class;

    protected static ?string $navigationLabel = 'Exceptions';

    protected static ?string $pluralLabel = 'Exceptions';

    protected static ?string $slug = 'availability/exceptions';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-exclamation-triangle';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Contenu';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Exception')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Début')
                            ->required(),
                        DateTimePicker::make('ends_at')
                            ->label('Fin')
                            ->required(),
                        TextInput::make('reason')
                            ->label('Motif')
                            ->maxLength(255),
                        Toggle::make('is_holiday')
                            ->label('Jour férié'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('starts_at')->label('Début')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('ends_at')->label('Fin')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('reason')->label('Motif')->limit(40),
                IconColumn::make('is_holiday')->boolean()->label('Férié'),
            ])
            ->defaultSort('starts_at', 'desc')
            ->actions([EditAction::make()])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAvailabilityExceptions::route('/'),
            'create' => Pages\CreateAvailabilityException::route('/create'),
            'edit' => Pages\EditAvailabilityException::route('/{record}/edit'),
        ];
    }
}
