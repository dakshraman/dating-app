<?php

namespace App\Filament\Resources\Matches;

use App\Filament\Resources\Matches\Pages\ListUserMatches;
use App\Filament\Resources\Matches\Schemas\UserMatchForm;
use App\Filament\Resources\Matches\Tables\UserMatchTable;
use App\Models\UserMatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserMatchResource extends Resource
{
    protected static ?string $model = UserMatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|UnitEnum|null $navigationGroup = 'Dating';

    public static function form(Schema $schema): Schema
    {
        return UserMatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserMatchTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserMatches::route('/'),
        ];
    }
}
