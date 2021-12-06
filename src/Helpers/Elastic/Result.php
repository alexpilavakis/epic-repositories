<?php

namespace Ulex\EpicRepositories\Helpers\Elastic;

class Result
{
    /** @var int */
    private $total;

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /**
     * @return array
     */
    public function getHits(): array
    {
        return $this->hits;
    }

    /**
     * @param array $hits
     */
    public function setHits(array $hits): void
    {
        $this->hits = $hits;
    }
    /** @var array */
    private $hits;
}
