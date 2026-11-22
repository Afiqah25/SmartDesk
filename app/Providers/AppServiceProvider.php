<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\TicketAiService;
use App\Services\OllamaTicketAiService;

class AppServiceProvider extends ServiceProvider
{
    
    public function register(): void
    {
        $this->app->bind(
            TicketAiService::class,
            OllamaTicketAiService::class
        );
    }


    public function boot(): void
    {
        //
    }
}
