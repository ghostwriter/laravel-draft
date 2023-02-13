<?php

declare(strict_types=1);

namespace Ghostwriter\Draft;

use Ghostwriter\Draft\Command\InitCommand;
use Ghostwriter\Draft\Command\NewCommand;
use Ghostwriter\Draft\Command\TraceCommand;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use PhpParser\Parser;
use PhpParser\ParserFactory;

final class DraftServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'draft');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'draft');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/draft.php' => config_path('draft.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/draft'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/draft'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/draft'),
            ], 'lang');*/

            // Registering package commands.
            $this->commands([InitCommand::class, NewCommand::class, TraceCommand::class]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/draft.php', 'draft');

        // Register the main class to use with the facade
        $this->app->singleton(ParserFactory::class, static fn (): ParserFactory => new ParserFactory());
        $this->app->singleton(
            Parser::class,
            static fn (Container $container): Parser =>
            $container->build(ParserFactory::class)->create(ParserFactory::PREFER_PHP7)
        );
        $this->app->bind(Draft::class);
    }
}
