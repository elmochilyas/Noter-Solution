<?php

namespace App\Filament\Resources\ChatbotConversationResource\Pages;

use App\Filament\Resources\ChatbotConversationResource;
use App\Models\ChatbotMessage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class ViewChatbotConversation extends ViewRecord
{
    protected static string $resource = ChatbotConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markReviewed')
                ->label('Marquer comme revu')
                ->action(function () {
                    $this->record->update(['is_reviewed' => true]);
                    Notification::make()->title('Marqué comme revu')->success()->send();
                })
                ->visible(fn () => ! $this->record->is_reviewed),

            Action::make('flagForReview')
                ->label('Signaler')
                ->color('warning')
                ->action(function () {
                    Notification::make()->title('Conversation signalée')->warning()->send();
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        $messages = ChatbotMessage::where('conversation_id', $this->record->id)
            ->orderBy('created_at')
            ->get();

        $messageComponents = [];

        foreach ($messages as $i => $msg) {
            $icon = $msg->role === 'user' ? 'heroicon-o-user' : 'heroicon-o-robot';
            $color = $msg->role === 'user' ? 'gray' : 'success';

            $faqInfo = '';
            if ($msg->retrieved_faq_ids) {
                $ids = is_array($msg->retrieved_faq_ids) ? $msg->retrieved_faq_ids : json_decode($msg->retrieved_faq_ids, true);
                if (! empty($ids)) {
                    $faqInfo = 'FAQ: #'.implode(', #', $ids);
                }
            }

            $tokenInfo = '';
            if ($msg->tokens_in !== null) {
                $tokenInfo = "IN: {$msg->tokens_in} | OUT: {$msg->tokens_out} | Lat: {$msg->latency_ms}ms";
            }

            $messageComponents[] = TextEntry::make("msg_{$i}")
                ->label(ucfirst($msg->role)." — {$msg->created_at?->format('H:i:s')}")
                ->state($msg->content.($faqInfo ? "\n\n---\n{$faqInfo}" : '').($tokenInfo ? "\n{$tokenInfo}" : ''))
                ->markdown()
                ->color($color)
                ->icon($icon);
        }

        return $schema
            ->components([
                Section::make('Conversation')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('locale')->label('Langue'),
                        TextEntry::make('intent_resolved')->label('Intention'),
                        TextEntry::make('started_at')->label('Début')->dateTime('d/m/Y H:i'),
                        TextEntry::make('last_message_at')->label('Dernier message')->dateTime('d/m/Y H:i'),
                        TextEntry::make('ended_at')->label('Fin')->dateTime('d/m/Y H:i')->hidden(fn ($state) => ! $state),
                        TextEntry::make('client.full_name')->label('Client'),
                        TextEntry::make('is_reviewed')->label('Revue')->state(fn ($record) => $record->is_reviewed ? 'Oui' : 'Non'),
                    ]),
                Section::make('Transcript')
                    ->schema($messageComponents)
                    ->collapsible(),
            ]);
    }
}
