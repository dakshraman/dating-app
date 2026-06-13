<?php

namespace App\Filament\Resources\Interests\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InterestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->unique(),
            ]);
    }
}
