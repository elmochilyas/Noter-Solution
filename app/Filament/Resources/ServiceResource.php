<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
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
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationLabel = 'Services';

    protected static ?string $pluralLabel = 'Services';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title_translations.fr', 'title_translations.ar', 'intro_translations.fr', 'intro_translations.ar'];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-briefcase';
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
                        Select::make('icon')
                            ->options([
                                'heart' => 'Cœur',
                                'home' => 'Maison',
                                'briefcase' => 'Mallette',
                                'scale' => 'Balance',
                            ]),
                        Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                        TextInput::make('display_order')
                            ->numeric()
                            ->default(1),
                    ])->columns(2),

                Section::make('Traductions')
                    ->components([
                        TextInput::make('title_translations.fr')
                            ->label('Titre (FR)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('title_translations.ar')
                            ->label('Titre (AR)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('intro_translations.fr')
                            ->label('Introduction (FR)')
                            ->required()
                            ->maxLength(500),
                        TextInput::make('intro_translations.ar')
                            ->label('Introduction (AR)')
                            ->required()
                            ->maxLength(500),
                        TextInput::make('body_translations.fr')
                            ->label('Contenu (FR)'),
                        TextInput::make('body_translations.ar')
                            ->label('Contenu (AR)'),
                    ]),

                Section::make('Transactions et documents')
                    ->components([
                        Repeater::make('transactions_translations.fr')
                            ->label('Transactions (FR)')
                            ->simple(TextInput::make('value')->label(''))
                            ->addActionLabel('Ajouter une transaction'),
                        Repeater::make('transactions_translations.ar')
                            ->label('Transactions (AR)')
                            ->simple(TextInput::make('value')->label(''))
                            ->addActionLabel('Ajouter une transaction'),
                        Repeater::make('required_documents_translations.fr')
                            ->label('Documents requis (FR)')
                            ->simple(TextInput::make('value')->label(''))
                            ->addActionLabel('Ajouter un document'),
                        Repeater::make('required_documents_translations.ar')
                            ->label('Documents requis (AR)')
                            ->simple(TextInput::make('value')->label(''))
                            ->addActionLabel('Ajouter un document'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')->searchable()->sortable(),
                TextColumn::make('title_translations.fr')->label('Titre (FR)')->searchable(),
                TextColumn::make('title_translations.ar')->label('Titre (AR)')->searchable(),
                IconColumn::make('is_active')->boolean()->sortable(),
                TextColumn::make('display_order')->sortable(),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Aperçu')
                    ->url(fn ($record) => url('/'.app()->getLocale().'/services/'.$record->slug))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
