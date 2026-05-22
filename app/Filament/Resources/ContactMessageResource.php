<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationLabel = 'Messages';

    protected static ?string $pluralLabel = 'Messages contact';

    protected static ?string $recordTitleAttribute = 'subject';

    public static function getNavigationGroup(): ?string
    {
        return 'Quotidien';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-envelope';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Message')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nom'),
                        TextEntry::make('email')->label('Email'),
                        TextEntry::make('subject')->label('Sujet')->columnSpanFull(),
                        TextEntry::make('message')->label('Message')->columnSpanFull(),
                        IconColumn::make('is_handled')->boolean()->label('Traité'),
                        TextEntry::make('handledBy.name')->label('Traité par'),
                        TextEntry::make('handled_at')->label('Traité le')->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('subject')->label('Sujet')->searchable()->limit(40),
                TextColumn::make('message')->label('Message')->limit(60),
                IconColumn::make('is_handled')->boolean()->label('Traité'),
                TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('is_handled')
                    ->label('Statut')
                    ->options([
                        '0' => 'Non traité',
                        '1' => 'Traité',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ViewAction::make(),
                Action::make('mark_handled')
                    ->label('Marquer traité')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (ContactMessage $record) => ! $record->is_handled)
                    ->form([
                        Textarea::make('note')->label('Note interne')->rows(2),
                    ])
                    ->action(function (ContactMessage $record, array $data): void {
                        $record->update([
                            'is_handled' => true,
                            'handled_by' => auth()->id(),
                            'handled_at' => now(),
                        ]);

                        if (! empty($data['note'])) {
                            activity()
                                ->performedOn($record)
                                ->causedBy(auth()->user())
                                ->log('Message marqué traité: '.$data['note']);
                        }

                        Notification::make()->title('Message marqué comme traité')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'view' => Pages\ViewContactMessage::route('/{record}'),
        ];
    }
}
