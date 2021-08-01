<?php

namespace Ulex\EpicRepositories\Decorators;

use Ulex\EpicRepositories\Interfaces\EpicInterface;

abstract class AbstractDecorator implements EpicInterface
{
    /** @var EpicInterface|null */
    protected $epic;

    /** @var */
    protected $model;

    /** @var string */
    protected $name;

    /**
     * EpicAbstract constructor.
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
