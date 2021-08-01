<?php

namespace Ulex\EpicRepositories\Repositories;

use Ulex\EpicRepositories\Interfaces\EpicInterface;

abstract class AbstractDatabase implements EpicInterface
{
    /**
     * @param $model
     * @param EpicInterface|null $epic
     */
    public function __construct($model, EpicInterface $epic = null)
    {

    }
}
