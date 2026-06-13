<?php

namespace App\Filament\Resources\UserPhotos;

use App\Filament\Resources\UserPhotos\Pages\ListUserPhotos;
use App\Filament\Resources\UserPhotos\Tables\UserPhotoTable;
use App\Models\UserPhoto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserPhotoResource extends Resource
{
    protected static ?string $model = UserPhoto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return UserPhotoTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserPhotos::route('/'),
        ];
    }
}
