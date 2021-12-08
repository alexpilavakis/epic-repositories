<?php

namespace Ulex\EpicRepositories\Repositories;

use Ulex\EpicRepositories\Interfaces\EpicInterface;
use Ulex\EpicRepositories\Interfaces\RepositoryInterface;

abstract class AbstractDatabase implements RepositoryInterface
{
    /**
     * @param $model
     * @param EpicInterface|null $epic
     */
    public function __construct($model, EpicInterface $epic = null)
    {

    }

    /**
     * @return EpicInterface
     */
    public function fromSource()
    {
        return $this;
    }
}
