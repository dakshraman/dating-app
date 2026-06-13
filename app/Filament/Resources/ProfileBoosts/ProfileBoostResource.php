<?php

namespace App\Filament\Resources\ProfileBoosts;

use App\Filament\Resources\ProfileBoosts\Pages\ListProfileBoosts;
use App\Filament\Resources\ProfileBoosts\Tables\ProfileBoostTable;
use App\Models\ProfileBoost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProfileBoostResource extends Resource
{
    protected static ?string $model = ProfileBoost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRocketLaunch;

    protected static string|UnitEnum|null $navigationGroup = 'Premium';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return ProfileBoostTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfileBoosts::route('/'),
        ];
    }
}
