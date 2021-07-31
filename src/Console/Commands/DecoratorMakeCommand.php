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
    protected $signature = 'make:epic:decorator {name : The required model name of the decorator class}';

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
    protected $type = 'Decorator';

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
    private $decoratorType;

    /**
     * @var string
     */
    private $decoratorPath;

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $config = $this->laravel['config'];
        $decorators = $config->get('epic-repositories.decorators');
        $this->decoratorPath = $config->get('epic-repositories.namespaces.decorators');
        foreach ($decorators as $decorator) {
            $this->decoratorType = $decorator;
            $this->setDecoratorClass();
            parent::handle();
        }
    }

    /**
     * Set repository class name
     *
     * @return  void
     */
    private function setDecoratorClass(): void
    {
        $name = (trim($this->argument('name')));

        $this->model = $name;
        $decoratorName = ucfirst($this->decoratorType);
        $this->decoratorClass = $name . "{$decoratorName}Decorator";
        $this->type = $this->decoratorType . $this->type;
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

        $stub = parent::replaceClass($stub, $name);
        $stub = str_replace('DummyType', ucfirst($this->decoratorType), $stub);

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
