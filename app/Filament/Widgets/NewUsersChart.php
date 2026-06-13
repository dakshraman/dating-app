<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class NewUsersChart extends ChartWidget
{
    protected ?string $heading = 'New Users (Last 7 Days)';

    protected ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $data = collect();
        for ($i = 6; $i >= 0; $i--) {
            $data->push(
                User::whereDate('created_at', now()->subDays($i))->count(),
            );
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $data->toArray(),
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#f59e0b',
                ],
            ],
            'labels' => collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
