<?php

namespace App\Providers;

use App\Services\Document\DocumentParser;
use App\Services\Document\RuleBasedParser;
use App\Services\Templates\TemplateManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TemplateManager::class);

        // Swap this binding to change how prompts are read; nothing else moves.
        $this->app->bind(DocumentParser::class, RuleBasedParser::class);
    }

    public function boot(): void
    {
        //
    }
}
