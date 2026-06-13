<?php

namespace App\Filament\Resources\Matches\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserMatchTable
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
                TextColumn::make('matched_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('conversation.last_message_at')
                    ->label('Last Message')
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
            ->defaultSort('created_at', 'desc');
    }
}
