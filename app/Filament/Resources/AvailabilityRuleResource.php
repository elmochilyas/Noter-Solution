<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AvailabilityRuleResource\Pages;
use App\Models\AvailabilityRule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
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

class AvailabilityRuleResource extends Resource
{
    protected static ?string $model = AvailabilityRule::class;

    protected static ?string $navigationLabel = 'Règles';

    protected static ?string $pluralLabel = 'Règles';

    protected static ?string $slug = 'availability/rules';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clock';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Contenu';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Règle de disponibilité')
                    ->columns(2)
                    ->schema([
                        Select::make('day_of_week')
                            ->label('Jour')
                            ->options([
                                1 => 'Lundi',
                                2 => 'Mardi',
                                3 => 'Mercredi',
                                4 => 'Jeudi',
                                5 => 'Vendredi',
                                6 => 'Samedi',
                                0 => 'Dimanche',
                            ])
                            ->required(),
                        TimePicker::make('starts_at')
                            ->label('Début')
                            ->required(),
                        TimePicker::make('ends_at')
                            ->label('Fin')
                            ->required(),
                        Select::make('format')
                            ->options([
                                'video' => 'En ligne',
                                'office' => 'Au cabinet',
                                'both' => 'Les deux',
                            ])
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')
                    ->label('Jour')
                    ->formatStateUsing(fn (int $state) => match ($state) {
                        1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi',
                        4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi',
                        0 => 'Dimanche', default => '—',
                    })
                    ->sortable(),
                TextColumn::make('starts_at')->label('Début'),
                TextColumn::make('ends_at')->label('Fin'),
                TextColumn::make('format')->label('Format'),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('day_of_week')
            ->actions([EditAction::make()])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAvailabilityRules::route('/'),
            'create' => Pages\CreateAvailabilityRule::route('/create'),
            'edit' => Pages\EditAvailabilityRule::route('/{record}/edit'),
        ];
    }
}
