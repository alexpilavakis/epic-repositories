<?php

namespace Ulex\EpicRepositories\Console\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\GeneratorCommand;
use InvalidArgumentException;

class DecoratorMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:epic:decorator {name : The required model name of the decorator class} {decorator : The required decorator} {repository : The required repository}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an epic decorator based on your config/epic-repositories';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $hidden = true;

    /**
     * The name of class being generated.
     *
     * @var string
     */
    private $decoratorClass;

    /**
     * The name of class being generated.
     *
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $decoratorPath;

    /**
     * @var string
     */
    private $decorator;

    /**
     * @var string
     */
    private $repository;

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $config = $this->laravel['config'];
        $this->decoratorPath = $config->get('epic-repositories.namespaces.decorators');

        $this->model = (trim($this->argument('name')));
        $this->decorator = (trim($this->argument('decorator')));
        $this->repository = (trim($this->argument('repository')));
        $this->decoratorClass = $this->type = $this->model . $this->repository. $this->decorator . 'Decorator';
        parent::handle();
    }

    /**
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->decoratorClass);
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  $stub
     * @param  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        if(!$this->argument('name')){
            throw new InvalidArgumentException("Missing required argument model name");
        }
        if(!$this->argument('decorator')){
            throw new InvalidArgumentException("Missing required argument decorator name");
        }
        $type = isset($this->repository) ? ucfirst($this->repository) : '';

        $stub = parent::replaceClass($stub, $name);
        $stub = str_replace('Type', $type, $stub);
        $stub = str_replace('Domm', $this->decorator, $stub);

        return str_replace('Dummy', $this->model, $stub);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/Decorator.stub';
    }

    /**
     * @param $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $this->decoratorPath;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the decorator.']
        ];
    }

}
