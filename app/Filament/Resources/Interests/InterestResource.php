<?php

namespace App\Filament\Resources\Interests;

use App\Filament\Resources\Interests\Pages\CreateInterest;
use App\Filament\Resources\Interests\Pages\EditInterest;
use App\Filament\Resources\Interests\Pages\ListInterests;
use App\Filament\Resources\Interests\Schemas\InterestForm;
use App\Filament\Resources\Interests\Tables\InterestTable;
use App\Models\Interest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InterestResource extends Resource
{
    protected static ?string $model = Interest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    public static function form(Schema $schema): Schema
    {
        return InterestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InterestTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInterests::route('/'),
            'create' => CreateInterest::route('/create'),
            'edit' => EditInterest::route('/{record}/edit'),
        ];
    }
}
