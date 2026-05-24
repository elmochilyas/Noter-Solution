<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsultationPlanResource\Pages;
use App\Models\ConsultationPlan;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
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

class ConsultationPlanResource extends Resource
{
    protected static ?string $model = ConsultationPlan::class;

    protected static ?string $navigationLabel = 'Plans de consultation';

    protected static ?string $pluralLabel = 'Plans de consultation';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-credit-card';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->components([
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('duration_minutes')
                            ->label('Durée (minutes)')
                            ->numeric()
                            ->required(),
                        TextInput::make('price_centimes')
                            ->label('Prix (MAD)')
                            ->numeric()
                            ->required()
                            ->formatStateUsing(fn (?int $state) => $state ? number_format($state / 100, 2, ',', '') : '0')
                            ->mutateDehydrate(fn (?string $state): int => (int) round(((float) str_replace(',', '.', $state ?? '0')) * 100)),
                        Select::make('format')
                            ->options([
                                'video' => 'En ligne',
                                'office' => 'Au cabinet',
                                'both' => 'Les deux',
                            ])->required(),
                        Toggle::make('is_recommended')
                            ->label('Recommandé'),
                        Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                        TextInput::make('display_order')
                            ->numeric()
                            ->default(1),
                    ])->columns(2),

                Section::make('Traductions')
                    ->components([
                        TextInput::make('name_translations.fr')
                            ->label('Nom (FR)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_translations.ar')
                            ->label('Nom (AR)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('description_translations.fr')
                            ->label('Description (FR)')
                            ->required()
                            ->maxLength(500),
                        TextInput::make('description_translations.ar')
                            ->label('Description (AR)')
                            ->required()
                            ->maxLength(500),
                    ]),

                Section::make('Fonctionnalités incluses')
                    ->components([
                        Repeater::make('included_features.fr')
                            ->label('Fonctionnalités (FR)')
                            ->simple(TextInput::make('value')->label(''))
                            ->addActionLabel('Ajouter'),
                        Repeater::make('included_features.ar')
                            ->label('Fonctionnalités (AR)')
                            ->simple(TextInput::make('value')->label(''))
                            ->addActionLabel('Ajouter'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_translations.fr')->label('Nom (FR)')->searchable(),
                TextColumn::make('price_centimes')
                    ->label('Prix')
                    ->formatStateUsing(fn ($state) => $state === 0 ? 'Gratuit' : number_format($state / 100, 2, ',', ' ').' MAD'),
                TextColumn::make('duration_minutes')->label('Durée'),
                IconColumn::make('is_recommended')->boolean()->label('Recommandé'),
                IconColumn::make('is_active')->boolean()->label('Actif'),
                TextColumn::make('display_order')->sortable(),
            ])
            ->defaultSort('display_order')
            ->actions([
                EditAction::make(),
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
            'index' => Pages\ListConsultationPlans::route('/'),
            'create' => Pages\CreateConsultationPlan::route('/create'),
            'edit' => Pages\EditConsultationPlan::route('/{record}/edit'),
        ];
    }
}
