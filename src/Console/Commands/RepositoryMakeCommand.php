<?php

namespace Ulex\EpicRepositories\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;

class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:epic:repository {model : The required model of the repository class} {--all : Includes all repositories, decorators (as set in config) & interfaces}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create epic repository/ies with configured decorators based on your config/epic-repositories';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * The name of class being generated.
     *
     * @var string
     */
    private $repositoryClass;

    /**
     * The name of class being generated.
     *
     * @var string
     */
    private $model;

    private $repositoryType;
    private $repositoryPath;

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $this->setRepositoryClass();

        $config = $this->laravel['config'];
        $repositories = $config->get('epic-repositories.repositories');
        foreach ($repositories as $repository => $path) {
            $this->repositoryType = $repository;
            $this->repositoryPath = $path;
            parent::handle();
        }
        $this->call('make:epic:interface', ['name' => $this->argument('model')]);
        $this->call('make:epic:decorator', ['name' => $this->argument('model')]);

        $this->line("<info>Add Model in `models` array in config/epic-repositories.php</info>");
    }

    /**
     * Set repository class name
     *
     * @return  void
     */
    private function setRepositoryClass(): void
    {
        $model = (trim($this->argument('model')));

        $this->model = $model;

        $this->repositoryClass = $model . 'Repository';
        $this->type = $this->repositoryType . $this->type;
    }

    /**
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->repositoryClass);
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
        if (!$this->argument('model')) {
            throw new InvalidArgumentException("Missing required argument model name");
        }

        $stub = parent::replaceClass($stub, $name);
        $stub = str_replace('DummyType', ucfirst($this->repositoryType), $stub);

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
