<?php

namespace Ulex\EpicRepositories\Decorators;

use Ulex\EpicRepositories\Interfaces\DecoratorInterface;
use Ulex\EpicRepositories\Interfaces\RepositoryInterface;
use Illuminate\Contracts\Cache\Repository as Cache;

abstract class LoggingDecorator implements DecoratorInterface
{
    /** @var RepositoryInterface */
    protected $repository;

    /** @var Cache */
    protected $cache;

    /** @var */
    protected $model;

}
