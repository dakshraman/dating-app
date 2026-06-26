<?php

namespace App\Providers;

use App\Listeners\ProcessReverbMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Reverb\Events\MessageReceived;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            MessageReceived::class,
            ProcessReverbMessage::class,
        );
    }
}
