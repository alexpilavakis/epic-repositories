<?php

namespace Ulex\EpicRepositories\Console\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\GeneratorCommand;

class InterfaceMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:epic:interface {name : The required model name of the repository interface}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an epic repository interface';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Interface';

    /**
     * @var bool
     */
    protected $hidden = true;

    /**
     * The name of class being generated.
     *
     * @var string
     */
    private $interfaceClass;

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $this->setInterfaceClass();
        parent::handle();
    }

    /**
     * Set interface class name
     * @return InterfaceMakeCommand
     */
    private function setInterfaceClass()
    {
        $name = (trim($this->argument('name')));

        $this->interfaceClass = $name . 'RepositoryInterface';

        return $this;
    }

    /**
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->interfaceClass);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/Interface.stub';
    }

    /**
     * @param $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $config = $this->laravel['config'];
        return $config->get('epic-repositories.namespaces.interfaces');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the contract.']
        ];
    }
}
