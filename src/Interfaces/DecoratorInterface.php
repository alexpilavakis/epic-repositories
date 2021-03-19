<?php

namespace Ulex\EpicRepositories\Interfaces;

interface DecoratorInterface
{
    /**
     * @param $name
     * @return DecoratorInterface
     */
    public function useRepository($name);

    /**
     * @param $name
     * @return DecoratorInterface
     */
    public function withDecorator($name);
}
