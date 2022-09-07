<?php

namespace Ulex\EpicRepositories\Helpers\Elastic;

class Result
{
    const AGGS_KEY = 'key';
    const AGGS_COUNT = 'doc_count';

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
     * Result Example
     *
     * "buckets": [
     *  {
     *      "key": "name_1",
     *      "doc_count": 10
     *  },
     *  {
     *      "key": "name_2",
     *      "doc_count": 6
     *  }]
     *
     * @param $name
     * @return array
     */
    public function getAggregationBuckets($name)
    {
        return $this->aggregations[$name]['buckets'] ?? [];
    }
}
