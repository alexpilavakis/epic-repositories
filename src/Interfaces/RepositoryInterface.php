<?php

namespace Ulex\EpicRepositories\Interfaces;

interface RepositoryInterface extends EpicInterface
{
    /**
     * @return EpicInterface
     */
    public function fromSource();
}
