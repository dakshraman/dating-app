<?php

namespace App\Filament\Resources\UserSubscriptions\Pages;

use App\Filament\Resources\UserSubscriptions\UserSubscriptionResource;
use Filament\Resources\Pages\ListRecords;

class ListUserSubscriptions extends ListRecords
{
    protected static string $resource = UserSubscriptionResource::class;
}
