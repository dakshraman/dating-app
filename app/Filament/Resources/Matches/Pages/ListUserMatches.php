<?php

namespace App\Filament\Resources\Matches\Pages;

use App\Filament\Resources\Matches\UserMatchResource;
use Filament\Resources\Pages\ListRecords;

class ListUserMatches extends ListRecords
{
    protected static string $resource = UserMatchResource::class;
}
