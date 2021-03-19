<?php

namespace Ulex\EpicRepositories;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Ulex\EpicRepositories\Console\Commands\DecoratorMakeCommand;
use Ulex\EpicRepositories\Console\Commands\InterfaceMakeCommand;
use Ulex\EpicRepositories\Console\Commands\RepositoryMakeCommand;

class RepositoriesServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        if (function_exists('config_path')) { // function not available and 'publish' not relevant in Lumen
            $this->publishes([__DIR__ . '/../config/epic-repositories.php' => config_path('epic-repositories.php')], 'config');
        }
        if ($this->app->runningInConsole()) {
            $this->commands([
                RepositoryMakeCommand::class,
                InterfaceMakeCommand::class,
                DecoratorMakeCommand::class,
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $models = $this->app->config['epic-repositories.models'];
        $namespaces = $this->app->config['epic-repositories.namespaces'];
        $repositories = $this->app->config['epic-repositories.repositories'];
        $repositoryNamespace = reset($repositories);
        $decorators = $this->app->config['epic-repositories.decorators'];
        foreach ($models as $name => $class) {
            $interface = $namespaces['interfaces'] . "\\" . $name . "RepositoryInterface";
            $repository = $repositoryNamespace . "\\" . $name . "Repository";
            foreach ($decorators as $decorator) {
                $decorator = $namespaces['decorators'] . "\\" . $name . ucfirst($decorator) . "Decorator";
                $this->app->bind($interface, function () use ($name, $class, $decorator, $repository) {
                    $model = new $class();
                    $baseRepo = new $repository($model);
                    return new $decorator($baseRepo, $this->app['cache.store'], $model);
                });
            }
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        $provides = [];
        $models = $this->app->config['epic-repositories.models'];
        $namespaces = $this->app->config['epic-repositories.namespaces'];
        foreach ($models as $name => $class) {
            $provides[] = $namespaces['interfaces'] . "\\" . $name . "RepositoryInterface";
        }
        return $provides;
    }
}
