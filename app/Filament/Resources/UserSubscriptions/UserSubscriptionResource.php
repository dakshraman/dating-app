<?php

namespace App\Filament\Resources\UserSubscriptions;

use App\Filament\Resources\UserSubscriptions\Pages\ListUserSubscriptions;
use App\Filament\Resources\UserSubscriptions\Tables\UserSubscriptionTable;
use App\Models\UserSubscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserSubscriptionResource extends Resource
{
    protected static ?string $model = UserSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Premium';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return UserSubscriptionTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserSubscriptions::route('/'),
        ];
    }
}
