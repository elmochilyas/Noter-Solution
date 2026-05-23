<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $pluralLabel = 'FAQ';

    public static function getGloballySearchableAttributes(): array
    {
        return ['question_translations.fr', 'question_translations.ar', 'answer_translations.fr', 'answer_translations.ar'];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-question-mark-circle';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->components([
                        Select::make('category')
                            ->options([
                                'general' => 'Général',
                                'booking' => 'Rendez-vous',
                                'payment' => 'Paiement',
                                'services' => 'Services',
                            ])->required(),
                        Toggle::make('is_published')
                            ->label('Publié')
                            ->default(true),
                        TextInput::make('display_order')
                            ->numeric()
                            ->default(1),
                    ])->columns(3),

                Section::make('Question')
                    ->components([
                        TextInput::make('question_translations.fr')
                            ->label('Question (FR)')
                            ->required()
                            ->maxLength(500),
                        TextInput::make('question_translations.ar')
                            ->label('Question (AR)')
                            ->required()
                            ->maxLength(500),
                    ]),

                Section::make('Réponse')
                    ->components([
                        Textarea::make('answer_translations.fr')
                            ->label('Réponse (FR)')
                            ->required()
                            ->rows(4),
                        Textarea::make('answer_translations.ar')
                            ->label('Réponse (AR)')
                            ->required()
                            ->rows(4),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category')
                    ->label('Catégorie')
                    ->formatStateUsing(fn ($state) => __("faq.category_{$state}")),
                TextColumn::make('question_translations.fr')
                    ->label('Question (FR)')
                    ->searchable()
                    ->limit(50),
                IconColumn::make('is_published')->boolean()->label('Publié'),
                TextColumn::make('display_order')->sortable(),
                TextColumn::make('view_count')->label('Vues')->sortable(),
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
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
