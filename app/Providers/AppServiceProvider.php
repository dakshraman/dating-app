<?php

namespace App\Providers;

use App\Listeners\ProcessReverbMessage;
use App\Services\ReverbChannelStore;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Reverb\Events\MessageReceived;
use Laravel\Reverb\Protocols\Pusher\Contracts\ChannelManager;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving(ChannelManager::class, function ($cm) {
            ReverbChannelStore::set($cm);
        });
    }

    public function boot(): void
    {
        Event::listen(
            MessageReceived::class,
            ProcessReverbMessage::class,
        );
    }
}
