<?php

namespace Ulex\EpicRepositories\Repositories;

use Ulex\EpicRepositories\Helpers\Elastic\Hit;
use Ulex\EpicRepositories\Helpers\Elastic\Result;
use Ulex\EpicRepositories\Interfaces\EpicInterface;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Client;
use Ulex\EpicRepositories\Interfaces\RepositoryInterface;

abstract class AbstractElastic implements RepositoryInterface
{
    /** @var string */
    protected $model;

    /** @var string */
    protected $index;

    /** @var Client */
    public $client;

    /**
     * AbstractElastic constructor.
     * @param $model
     * @param EpicInterface|null $epic
     * @return Client
     */
    public function __construct($model, EpicInterface $epic = null)
    {
        $this->index = $this->index ?? strtolower(class_basename($model) . 's');
        $configs = config('epic-repositories.configs.elastic');
        $hosts = $configs['hosts'] ?? null;
        $clientBuilder = ClientBuilder::create()->setHosts($hosts);
        if (isset($config['retries'])) {
            $clientBuilder->setRetries($config['retries']);
        }
        $this->client = $clientBuilder->build();
        return $this->client;
    }

    /**
     * @return EpicInterface
     */
    public function fromSource()
    {
        return $this;
    }

    /**
     * @param $params
     * @return Result
     */
    protected function processResult($params)
    {
        $result = new Result();
        $result->setTotal($params['hits']['total']['value'] ?? null);

        $hits = isset($params['hits']['hits']) ? array_column($params['hits']['hits'], '_source') : [];
        $result->setHits($hits);
        $result->setAggregations($params['aggregations'] ?? []);
        return $result;
    }

    /**
     * @param $hit
     * @return Hit
     */
    protected function extractHit($result)
    {
        $hit = new Hit();
        $hit->setIndex($result['_index'] ?? '');
        $hit->setId($result['_id'] ?? '');
        $hit->setSource($result['_source'] ?? []);
        return $hit;
    }

    /**
     * @param array $params
     * @return array
     */
    protected function setParams(array $params)
    {
        $params['index'] = $params['index'] ?? $this->index;
        $params['type'] = $params['type'] ?? null;
        return $params;
    }

    /**
     ************
     * Search ***
     ************
     */

    /**
     * @param array $params
     * @return Hit
     */
    public function get(array $params)
    {
        $result = $this->client->get($this->setParams($params));
        return $this->extractHit($result);
    }

    /**
     * @param $params
     * @return Result
     */
    public function search($params)
    {
        $result = $this->client->search($this->setParams($params));
        return $this->processResult($result);
    }

    /**
     * @param $params
     * @return Result
     */
    public function count($params)
    {
        $result = $this->client->count($this->setParams($params));
        return $result['count'];
    }

    /**
     ************
     * Index ***
     ************
     */

    /**
     * @param array $attributes
     * @return string
     */
    public function index(array $attributes)
    {
        $params = [];
        if (isset($attributes['id'])) {
            $params['id'] = $attributes['id'];
            unset($attributes['id']);
        }
        $params['body'] = $attributes;
        $result = $this->client->index($this->setParams($params));
        return $result['result'];
    }

    /**
     * @param int $id
     * @return string
     */
    public function delete(int $id)
    {
        $result = $this->client->delete($this->setParams(compact('id')));
        return $result['result'];
    }

    /**
     * @param array $params
     * @return string
     */
    public function bulk(array $params)
    {
        $result = $this->client->bulk($this->setParams($params));
        return $result;
    }
}
