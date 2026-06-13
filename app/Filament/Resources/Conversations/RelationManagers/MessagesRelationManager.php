<?php

namespace App\Filament\Resources\Conversations\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sender_id')
                    ->relationship('sender', 'name')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                Textarea::make('content')
                    ->columnSpanFull(),
                TextInput::make('status'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sender.name')
                    ->label('From')
                    ->searchable(),
                TextColumn::make('content')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('type'),
                TextColumn::make('status'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('read_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
