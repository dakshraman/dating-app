<?php

namespace App\Filament\Widgets;

use App\Models\Swipe;
use App\Models\UserMatch;
use Filament\Widgets\ChartWidget;

class SwipeAnalyticsChart extends ChartWidget
{
    protected ?string $heading = 'Swipes & Matches (Last 7 Days)';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $labels = [];
        $swipes = [];
        $matches = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M d');
            $swipes[] = Swipe::whereDate('created_at', $date)->count();
            $matches[] = UserMatch::whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Swipes',
                    'data' => $swipes,
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#f59e0b',
                ],
                [
                    'label' => 'Matches',
                    'data' => $matches,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
