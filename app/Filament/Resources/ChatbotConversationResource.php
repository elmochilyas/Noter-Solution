<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatbotConversationResource\Pages;
use App\Models\ChatbotConversation;
use App\Models\Faq;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

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
                        TextEntry::make('messages_count')->label('Messages'),
                        TextEntry::make('client.full_name')->label('Client'),
                        IconColumn::make('is_reviewed')->boolean()->label('Revue'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn ($query) => $query->with('client')->withCount('messages'))
            ->columns([
                TextColumn::make('started_at')->label('Début')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('locale')->label('Langue'),
                TextColumn::make('intent_resolved')->label('Intention')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'escalated' => 'danger',
                        'booked' => 'success',
                        'out_of_scope' => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('messages_count')->label('Msg')->sortable(),
                TextColumn::make('client.full_name')->label('Client')
                    ->default('Anonyme'),
                IconColumn::make('is_reviewed')->boolean()->label('Revue'),
            ])
            ->filters([
                SelectFilter::make('intent_resolved')
                    ->label('Intention')
                    ->options([
                        'escalated' => 'Escalade',
                        'booked' => 'Réservation',
                        'info_only' => 'Info',
                        'faq_query' => 'FAQ',
                        'out_of_scope' => 'Hors sujet',
                    ]),
                SelectFilter::make('is_reviewed')
                    ->label('Revue')
                    ->options([
                        '0' => 'Non revu',
                        '1' => 'Revu',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('promoteToFaq')
                    ->label('Promouvoir en FAQ')
                    ->icon('heroicon-o-star')
                    ->color('success')
                    ->form([
                        TextInput::make('question_fr')->label('Question (FR)')->required(),
                        TextInput::make('question_ar')->label('Question (AR)')->required(),
                        TextInput::make('answer_fr')->label('Réponse (FR)')->required(),
                        TextInput::make('answer_ar')->label('Réponse (AR)')->required(),
                        Select::make('category')
                            ->label('Catégorie')
                            ->options([
                                'family' => 'Famille',
                                'real_estate' => 'Immobilier',
                                'financial' => 'Financier',
                                'contracts' => 'Contrats',
                                'other' => 'Autre',
                            ])->required(),
                    ])
                    ->action(function (array $data): void {
                        Faq::create([
                            'question_translations' => [
                                'fr' => $data['question_fr'],
                                'ar' => $data['question_ar'],
                            ],
                            'answer_translations' => [
                                'fr' => $data['answer_fr'],
                                'ar' => $data['answer_ar'],
                            ],
                            'category' => $data['category'],
                            'is_published' => true,
                            'display_order' => Faq::max('display_order') + 1,
                        ]);

                        Notification::make()
                            ->title('FAQ créée')
                            ->success()
                            ->send();
                    }),
                Action::make('markReviewed')
                    ->label('Marquer revu')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (ChatbotConversation $record): void {
                        $record->update(['is_reviewed' => true]);
                        Notification::make()->title('Marqué comme revu')->success()->send();
                    })
                    ->visible(fn (ChatbotConversation $record) => ! $record->is_reviewed),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('markReviewed')
                        ->label('Marquer comme revu')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_reviewed' => true]);
                            Notification::make()
                                ->title(count($records).' conversations marquées comme revues')
                                ->success()
                                ->send();
                        }),
                ]),
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

    public static function canEdit($record = null): bool
    {
        return auth()->user()?->isOwner() ?? false;
    }
}
