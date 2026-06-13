<?php

namespace App\Filament\Widgets;

use App\Models\Conversation;
use App\Models\Report;
use App\Models\Swipe;
use App\Models\User;
use App\Models\UserMatch;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Number;

class DatingStatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $todaySwipes = Swipe::whereDate('created_at', today())->count();
        $todayMatches = UserMatch::whereDate('created_at', today())->count();
        $swipesTotal = Swipe::count();

        $matchRate = $swipesTotal > 0
            ? round(($todayMatches / max($todaySwipes, 1)) * 100, 1)
            : 0;

        return [
            StatsOverviewWidget\Stat::make(
                'Total Users',
                Number::format(User::count()),
            )
                ->description('All registered users')
                ->color('success'),

            StatsOverviewWidget\Stat::make(
                'Swipes Today',
                Number::format($todaySwipes),
            )
                ->description('Total likes + nopes today')
                ->color('info'),

            StatsOverviewWidget\Stat::make(
                'Matches Today',
                Number::format($todayMatches),
            )
                ->description('New matches today')
                ->color('warning'),

            StatsOverviewWidget\Stat::make(
                'Match Rate',
                Number::format($matchRate).'%',
            )
                ->description('Today likes → matches conversion')
                ->color('primary'),

            StatsOverviewWidget\Stat::make(
                'Active Conversations',
                Number::format(Conversation::whereNotNull('last_message_at')->count()),
            )
                ->color('gray'),

            StatsOverviewWidget\Stat::make(
                'Pending Reports',
                Number::format(Report::count()),
            )
                ->description('Reports to review')
                ->color('danger'),
        ];
    }
}
