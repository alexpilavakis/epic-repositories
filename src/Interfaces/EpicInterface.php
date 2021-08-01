<?php

namespace Ulex\EpicRepositories\Interfaces;

interface EpicInterface
{

    /**
     * EpicInterface constructor.
     * @param $model
     * @param EpicInterface|null $epic
     */
    public function __construct($model, EpicInterface $epic = null);
}
