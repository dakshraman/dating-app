<?php

namespace App\Filament\Resources\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReceivedSwipesRelationManager extends RelationManager
{
    protected static string $relationship = 'receivedSwipes';

    protected static ?string $title = 'Received Swipes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('swiper_id')
                    ->relationship('swiper', 'name')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('swiper.name')
                    ->label('Swiped By')
                    ->searchable(),
                TextColumn::make('direction'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}
