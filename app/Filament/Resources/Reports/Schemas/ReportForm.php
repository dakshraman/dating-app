<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('reporter_id')
                    ->relationship('reporter', 'name')
                    ->required(),
                Select::make('reported_id')
                    ->relationship('reported', 'name')
                    ->required(),
                Textarea::make('reason')
                    ->columnSpanFull(),
                Textarea::make('details')
                    ->columnSpanFull(),
            ]);
    }
}
