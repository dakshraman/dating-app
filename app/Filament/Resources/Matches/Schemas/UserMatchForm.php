<?php

namespace App\Filament\Resources\Matches\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class UserMatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user1_id')
                    ->relationship('user1', 'name')
                    ->required(),
                Select::make('user2_id')
                    ->relationship('user2', 'name')
                    ->required(),
                DateTimePicker::make('matched_at'),
            ]);
    }
}
