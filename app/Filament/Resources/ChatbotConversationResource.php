<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatbotConversationResource\Pages;
use App\Models\ChatbotConversation;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatbotConversationResource extends Resource
{
    protected static ?string $model = ChatbotConversation::class;

    protected static ?string $navigationLabel = 'Conversations';

    protected static ?string $pluralLabel = 'Conversations chatbot';

    public static function getNavigationGroup(): ?string
    {
        return 'Chatbot';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Conversation')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('locale')->label('Langue'),
                        TextEntry::make('intent_resolved')->label('Intention'),
                        TextEntry::make('started_at')->label('Début')->dateTime('d/m/Y H:i'),
                        TextEntry::make('last_message_at')->label('Dernier message')->dateTime('d/m/Y H:i'),
                        TextEntry::make('client.full_name')->label('Client'),
                        IconColumn::make('is_reviewed')->boolean()->label('Revue'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn ($query) => $query->with('client'))
            ->columns([
                TextColumn::make('started_at')->label('Début')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('locale')->label('Langue'),
                TextColumn::make('intent_resolved')->label('Intention'),
                TextColumn::make('client.full_name')->label('Client'),
                IconColumn::make('is_reviewed')->boolean()->label('Revue'),
            ])
            ->defaultSort('started_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatbotConversations::route('/'),
            'view' => Pages\ViewChatbotConversation::route('/{record}'),
        ];
    }
}
