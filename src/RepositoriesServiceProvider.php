<?php

namespace Ulex\EpicRepositories;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Ulex\EpicRepositories\Console\Commands\DecoratorMakeCommand;
use Ulex\EpicRepositories\Console\Commands\EpicMakeCommand;
use Ulex\EpicRepositories\Console\Commands\InterfaceMakeCommand;

class RepositoriesServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        if (function_exists('config_path')) { // function not available and 'publish' not relevant in Lumen
            $this->publishes([__DIR__ . '/../config/epic-repositories.php' => config_path('epic-repositories.php')], 'epic-repositories-config');
        }
        if ($this->app->runningInConsole()) {
            $this->commands([
                InterfaceMakeCommand::class,
                DecoratorMakeCommand::class,
                EpicMakeCommand::class
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
            foreach ($configuration['models'] as $model => $class) {
                $model = ucfirst($model);
                $repository = $namespaces['repositories'] . "\\" . $folder . "\\" . $model . $folder . "Repository";
                if ($this->app->runningInConsole() && !class_exists($repository)) {
                    continue;
                }
                $interface = $namespaces['interfaces'] . "\\" . $model . $folder . "Interface";
                $epic = new $repository($class);
                foreach ($configuration['decorators'] as $decoratorName) {
                    $decorator = $namespaces['decorators'] . "\\" . $model . $folder . ucfirst($decoratorName) . "Decorator";
                    if ($this->app->runningInConsole() && !class_exists($decorator)) {
                        continue;
                    }
                    $decorators[] = new $decorator($class, $epic);
                }
                $this->app->singleton($interface,
                    empty($decorators) ? function () use ($epic) {
                        return $epic;
                    } : function () use ($decorators, $class, $epic) {
                        //TODO adjust to accept multiple decorators
                        return reset($decorators);
                    }
                );
                $decorators = null;
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
            foreach ($configuration['models'] as $model => $class) {
                $model = ucfirst($model);
                $provides[] = $namespaces['interfaces'] . "\\" . $model . $folder . "Interface";
            }
        }
        return $provides;
    }
}
