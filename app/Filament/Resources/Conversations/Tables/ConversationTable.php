<?php

namespace App\Filament\Resources\Conversations\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConversationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user1.name')
                    ->label('User 1')
                    ->searchable(),
                TextColumn::make('user2.name')
                    ->label('User 2')
                    ->searchable(),
                TextColumn::make('messages_count')
                    ->label('Messages')
                    ->counts('messages')
                    ->sortable(),
                TextColumn::make('last_message_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('last_message_at', 'desc');
    }
}
