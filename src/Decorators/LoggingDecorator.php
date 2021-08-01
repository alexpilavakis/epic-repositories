<?php

namespace Ulex\EpicRepositories\Decorators;

use Ulex\EpicRepositories\Interfaces\DecoratorInterface;
use Ulex\EpicRepositories\Interfaces\EpicInterface;

abstract class LoggingDecorator implements DecoratorInterface
{
    /** @var EpicInterface */
    protected $epic;

    /** @var string */
    protected $model;

    /** @var string */
    protected $name;

    /**
     * LoggingCachingDecorator constructor.
     * @param $model
     * @param EpicInterface|null $epic
     */
    public function __construct($model, EpicInterface $epic = null)
    {
        $this->epic = $epic;
        $this->model = $model;
        $this->name = strtolower(class_basename($model));
    }

}
