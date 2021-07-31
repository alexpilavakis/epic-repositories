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
        $namespaces = $this->app->config['epic-repositories.namespaces'];
        $bindings = $this->app->config['epic-repositories.bindings'];
        foreach ($bindings as $index => $configuration) {
            $folder = ucfirst($index);
            $repositoryName = $folder . "Repository";
            foreach ($configuration['models'] as $model => $class) {
                $model = ucfirst($model);
                $repository = $namespaces['repositories'] . "\\" . $folder . "\\" . $model . $repositoryName;
                $interface = $namespaces['interfaces'] . "\\" . $model . $repositoryName . "Interface";
                foreach ($configuration['decorators'] as $decoratorName) {
                    $decorator = $namespaces['decorators'] . "\\" . $model . $folder . ucfirst($decoratorName) . "Decorator";
                    $this->app->bind($interface, function () use ($class, $decorator, $repository) {
                        $model = new $class();
                        $baseRepo = new $repository($model);
                        return new $decorator($baseRepo, $this->app['cache.store'], $model);
                });
                }
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
        $namespaces = $this->app->config['epic-repositories.namespaces'];
        $bindings = $this->app->config['epic-repositories.bindings'];
        foreach ($bindings as $index => $configuration) {
            $folder = ucfirst($index);
            $repositoryName = $folder . "Repository";
            foreach ($configuration['models'] as $model => $class) {
                $provides[] = $namespaces['interfaces'] . "\\" . $model . $repositoryName . "Interface";
            }
        }
        return $provides;
    }
}
