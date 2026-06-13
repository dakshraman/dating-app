<?php

namespace App\Filament\Resources\Conversations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('match_id')
                    ->relationship('match', 'id')
                    ->required(),
                Select::make('user1_id')
                    ->relationship('user1', 'name')
                    ->required(),
                Select::make('user2_id')
                    ->relationship('user2', 'name')
                    ->required(),
                DateTimePicker::make('last_message_at'),
            ]);
    }
}
