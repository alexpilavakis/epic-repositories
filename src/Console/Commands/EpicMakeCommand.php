<?php

namespace Ulex\EpicRepositories\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputArgument;

class EpicMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:epic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create epic repository/ies with configured decorators based on your config/epic-repositories';

    /**
     * The repository.
     *
     * @var string
     */
    protected $type;

    /**
     * The repository type
     *
     * @var string
     */
    protected $repositoryType;

    /**
     * The name of class being generated.
     *
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $repositoryPath;

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $config = $this->laravel['config'];
        $namespaces = $config->get('epic-repositories.namespaces');
        $bindings = $config->get('epic-repositories.bindings');
        foreach ($bindings as $index => $configuration) {
            $this->repositoryType = ucfirst($index);
            foreach ($configuration['models'] as $name => $class) {
                $this->model = ucfirst($name);
                $this->repositoryPath = $namespaces['repositories'] . "\\" . $this->repositoryType;
                $this->type = $this->getNameInput();
                foreach ($configuration['decorators'] as $decorator) {
                    parent::handle();
                    $this->call('make:epic:interface', ['name' => $this->model, 'repository' => $this->repositoryType]);
                    $this->call('make:epic:decorator', ['name' => $this->model, 'repository' => $this->repositoryType, 'decorator' => ucfirst($decorator)]);
                }
            }
        }
    }

    /**
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->model . $this->repositoryType . 'Repository');
    }

    /**
     * Replace the model for the given stub.
     *
     * @param $stub
     * @param $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);
        $stub = str_replace('Type', $this->repositoryType, $stub);

        return str_replace('Dummy', $this->model, $stub);
    }

    /**
     *
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/Repository.stub';
    }

    /**
     * @param $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $this->repositoryPath;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'The name of the model class.']
        ];
    }

}
