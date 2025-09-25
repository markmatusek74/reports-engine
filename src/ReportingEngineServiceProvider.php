<?php

namespace MarkMatusek74\ReportingEngine;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use MarkMatusek74\ReportingEngine\Http\Livewire\ReportBuilder;
use MarkMatusek74\ReportingEngine\Http\Livewire\ReportViewer;
use MarkMatusek74\ReportingEngine\Http\Livewire\ReportManager;

class ReportingEngineServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/reporting-engine.php', 'reporting-engine');

        $this->app->singleton('reporting-engine', function () {
            return new Services\ReportingEngineService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'reporting-engine');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Register Livewire components
        Livewire::component('report-builder', ReportBuilder::class);
        Livewire::component('report-viewer', ReportViewer::class);
        Livewire::component('report-manager', ReportManager::class);

        // Publishable files
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/reporting-engine.php' => config_path('reporting-engine.php'),
            ], 'reporting-engine-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/reporting-engine'),
            ], 'reporting-engine-views');

            $this->publishes([
                __DIR__ . '/Database/Migrations' => database_path('migrations'),
            ], 'reporting-engine-migrations');
        }
    }
}