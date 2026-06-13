<?php

namespace App\Filament\Resources\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PhotosRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('photo_url')
                    ->required(),
                Toggle::make('is_primary'),
                TextInput::make('order')
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('photo_url')
                    ->limit(40)
                    ->searchable(),
                IconColumn::make('is_primary')
                    ->boolean(),
                TextColumn::make('order')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
