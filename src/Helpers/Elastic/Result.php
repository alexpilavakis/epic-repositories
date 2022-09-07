<?php

namespace Ulex\EpicRepositories\Helpers\Elastic;

class Result
{

    /** @var int */
    private $total;

    /** @var array */
    private $hits;

    /** @var array */
    private $aggregations;

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

    /**
     * @return array
     */
    public function getAggregations(): array
    {
        return $this->aggregations;
    }

    /**
     * @param array $aggregations
     */
    public function setAggregations(array $aggregations): void
    {
        $this->aggregations = $aggregations;
    }

    /**
     * @param $name
     * @return array|mixed
     */
    public function getAggregationBuckets($name)
    {
        return $this->aggregations[$name]['buckets'] ?? [];
    }
}
