<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Événement')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->label('Date')->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('causer.name')->label('Acteur'),
                        TextEntry::make('description')->label('Description')->columnSpanFull(),
                        TextEntry::make('event')->label('Événement'),
                        TextEntry::make('subject_type')
                            ->label('Type')
                            ->formatStateUsing(fn (string $state) => class_basename($state)),
                        TextEntry::make('subject_id')->label('ID sujet'),
                        TextEntry::make('properties')
                            ->label('Propriétés')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—')
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
