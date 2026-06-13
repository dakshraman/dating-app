<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Textarea::make('description'),
                TextInput::make('price')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                TextInput::make('duration_days')
                    ->numeric()
                    ->required()
                    ->suffix('days'),
                KeyValue::make('features'),
                Toggle::make('is_active'),
            ]);
    }
}
