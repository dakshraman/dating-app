<?php

namespace App\Filament\Resources\UserPhotos\Pages;

use App\Filament\Resources\UserPhotos\UserPhotoResource;
use Filament\Resources\Pages\ListRecords;

class ListUserPhotos extends ListRecords
{
    protected static string $resource = UserPhotoResource::class;
}
