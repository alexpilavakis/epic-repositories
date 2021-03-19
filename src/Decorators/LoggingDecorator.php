<?php

namespace Ulex\EpicRepositories\Decorators;

use Ulex\EpicRepositories\Interfaces\LoggingDecoratorInterface;
use Ulex\EpicRepositories\Interfaces\DecoratorInterface;
use Illuminate\Contracts\Cache\Repository as Cache;

abstract class LoggingDecorator implements LoggingDecoratorInterface, DecoratorInterface
{
    /**
     * @var LoggingDecoratorInterface
     */
    protected $repository;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var
     */
    protected $model;

    /**
     * @param $name
     * @return $this|null
     */
    public function useRepository($name)
    {
        /** TODO Implement this */
        return $this;
    }

    /**
     * @param $name
     * @return $this|null
     */
    public function withDecorator($name)
    {
        /** TODO Implement this */
        return $this;
    }
}
