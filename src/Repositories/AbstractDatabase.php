<?php

namespace Ulex\EpicRepositories\Repositories;

use Ulex\EpicRepositories\Interfaces\DecoratorInterface;
use Ulex\EpicRepositories\Interfaces\EpicInterface;
use Ulex\EpicRepositories\Interfaces\RepositoryInterface;

abstract class AbstractDatabase implements RepositoryInterface, DecoratorInterface
{
    public function __construct(string $model, EpicInterface $epic = null)
    {

    }
}
