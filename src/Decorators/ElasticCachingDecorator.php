<?php

namespace Ulex\EpicRepositories\Decorators;

use Ulex\EpicRepositories\Helpers\Elastic\Hit;
use Ulex\EpicRepositories\Helpers\Elastic\Result;

abstract class ElasticCachingDecorator extends AbstractCachingDecorator
{
    /**
     * @param $key
     * @return string
     */
    protected function getKeyPrefix($key): string
    {
        return "elastic:$this->name:$key";
    }

    /**
     * @return string
     */
    protected function getCollectionPrefix()
    {
        return "elastic:$this->name:" . self::CACHE_TAG_COLLECTION;
    }

    /**
     * @param $id
     * @return void
     */
    protected function flushById($id): void
    {
        $this->flushFunction('get', compact('id'));
    }


    /**
     * @param $attribute
     * @param $value
     * @return void
     */
    protected function flushByAttribute($attribute, $value): void
    {
        $this->flushFunction('findBy', [$attribute, $value]);
        $this->flushFunction('countBy', [$attribute, $value]);
    }

    /**
     * Flush collection tags
     */
    protected function flushCollections()
    {
        $this->flushTag($this->getCollectionPrefix());
    }

    /**
     ************
     * Search ***
     ************
     */

    /**
     * @param $function
     * @param null $arguments
     * @return string
     */
    protected function key($function, $arguments = null)
    {
        if (empty($arguments)) {
            return $function;
        }
        return $this->advanceKey($function, $arguments);
    }

    /**
     * @param array $params
     * @return Hit
     */
    public function get(array $params)
    {
        return $this->remember(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $params
     * @return Result
     */
    public function search(array $params)
    {
        return $this->remember(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $params
     * @return int
     */
    public function count(array $params)
    {
        return $this->remember(__FUNCTION__, func_get_args());
    }

    /**
     * @param $field
     * @param $value
     * @return Result
     */
    public function findBy($field, $value): Result
    {
        return $this->remember(__FUNCTION__, func_get_args());
    }

    /**
     * @param $field
     * @param $value
     * @return int
     */
    public function countBy($field, $value): int
    {
        return $this->remember(__FUNCTION__, func_get_args());
    }

    /**
     ************
     * Index ***
     ************
     */

    /**
     * @param array $params
     * @return string
     */
    public function index(array $params)
    {
        return $this->getEpic()->index($params);
    }

    /**
     * @param int $id
     * @return string
     */
    public function delete(int $id)
    {
        return $this->getEpic()->delete($id);
    }

    /**
     * @param array $params
     * @return array
     */
    public function bulk(array $params)
    {
        return $this->getEpic()->bulk($params);
    }
}
