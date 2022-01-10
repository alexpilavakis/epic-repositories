<?php

namespace Ulex\EpicRepositories\Decorators;

use Ulex\EpicRepositories\Helpers\Elastic\Hit;
use Ulex\EpicRepositories\Helpers\Elastic\Result;

abstract class ElasticCachingDecorator extends AbstractCachingDecorator
{
    /**
     * NOTE: Cache tags are not supported when using the `file` or `database` cache drivers.
     * @return array
     */
    protected function tag(): array
    {
        $name = "elastic:{$this->name}";
        return [$name];
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
        $arguments = json_encode($arguments);
        return "{$function}:{$arguments}";
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
