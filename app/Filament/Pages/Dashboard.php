<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DatingStatsOverview;
use App\Filament\Widgets\NewUsersChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            DatingStatsOverview::class,
            NewUsersChart::class,
        ];
    }
}
