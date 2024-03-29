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
    protected string $model;
    protected string $index;
    public Client $client;

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
     * Flush all 'get' keys for this model instance along with any collections
     *
     * @param $model
     */
    public function flushGetKeys($model)
    {
    }

    /**
     * @param $params
     * @return Result
     */
    protected function processResult($params)
    {
        $result = new Result();
        $result->setTotal($params['hits']['total']['value'] ?? null);
        $resultArray = $params['hits']['hits'] ?? [];
        $hits = [];
        foreach ($resultArray as $item) {
            $item['_source']['_id'] = $item['_id'];
            $hits[] = $item['_source'];
        }
        $result->setHits($hits);
        $result->setAggregations($params['aggregations'] ?? []);
        return $result;
    }

    /**
     * @param $result
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
     * @param $field
     * @param $value
     * @return array
     */
    protected function match($field, $value)
    {
        return [
            'match' => [
                $field => $value
            ]
        ];
    }

    /**
     ********
     * Search
     ********
     */

    /**
     * @param int $id
     * @return Hit
     */
    public function get(int $id)
    {
        $params['id'] = $id;
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
     * @return int
     */
    public function count($params)
    {
        $result = $this->client->count($this->setParams($params));
        return $result['count'];
    }

    /**
     * @param $field
     * @param $value
     * @return Result
     */
    public function findBy($field, $value): Result
    {
        return $this->search([
            'body' => [
                'query' => $this->match($field, $value)
            ]
        ]);
    }

    /**
     * @param $field
     * @param $value
     * @return int
     */
    public function countBy($field, $value): int
    {
        return $this->count([
            'body' => [
                'query' => $this->match($field, $value)
            ]
        ]);
    }

    /**
     *******
     * Index
     *******
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
        return $this->client->bulk($this->setParams($params));
    }
}
