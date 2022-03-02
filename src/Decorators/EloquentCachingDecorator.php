<?php

namespace Ulex\EpicRepositories\Decorators;

abstract class EloquentCachingDecorator extends AbstractCachingDecorator
{
    /**
     * NOTE: Cache tags are not supported when using the `file` or `database` cache drivers.
     * @return array
     */
    protected function tag(): array
    {
        $name = "eloquent:{$this->name}";
        return [$name];
    }

    /**
     ************
     * Find *****
     ** Single **
     ************
     */

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->remember(__FUNCTION__, func_get_args());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findOrFail($id)
    {
        return $this->remember(__FUNCTION__, func_get_args());
    }

    /**
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function findBy($attribute, $value)
    {
        return $this->remember(__FUNCTION__, func_get_args());
    }

    /**
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function checkIfExists($attribute, $value)
    {
        return $this->remember(__FUNCTION__, func_get_args());
    }

    /**
     **********
     * Find ***
     ** Many **
     **********
     */

    /**
     * @return mixed
     */
    public function all()
    {
        return $this->remember(__FUNCTION__, func_get_args(), $this->tags([self::CACHE_TAG_COLLECTION]));
    }

    /**
     * @param array $conditions
     * @return array|mixed
     */
    public function findByConditions(array $conditions)
    {
        return $this->remember(__FUNCTION__, func_get_args(), $this->tags([self::CACHE_TAG_COLLECTION]));
    }

    /**
     *************
     * Create ****
     ** Update ***
     *** Delete **
     *************
     */

    /**
     * @param $attributes
     * @return mixed
     */
    public function create($attributes)
    {
        $model = $this->getEpic()->create($attributes);
        $this->flushGetKeys($model);
        return $model;
    }

    /**
     * @param array $attributes
     */
    public function createMany(array $attributes)
    {
        $this->repository->createMany($attributes);
        $this->flushCollections();
    }

    /**
     * @param $attributes
     * @return mixed
     */
    public function firstOrCreate($attributes)
    {
        $model = $this->getEpic()->firstOrCreate($attributes);
        $this->flushGetKeys($model);
        return $model;
    }

    /**
     * @param $attributes
     * @return mixed
     */
    public function updateOrCreate($attributes)
    {
        $model = $this->getEpic()->updateOrCreate($attributes);
        $this->flushGetKeys($model);
        return $model;
    }

    /**
     * @param $model
     * @param $attributes
     * @return mixed
     */
    public function update($model, $attributes)
    {
        $repository = $this->getEpic();
        $result = $repository->update($model, $attributes);
        if ($result) {
            $this->flushGetKeys($model);
        }
        return $result;
    }

    /**
     * @param array $conditions
     * @param array $attributes
     * @return bool
     */
    public function updateByConditions(array $conditions, array $attributes)
    {
        $result = $this->getEpic()->updateByConditions($conditions, $attributes);
        if ($result) {
            $models = $this->findByConditions($conditions);
            foreach ($models as $model) {
                $this->flushGetKeys($model, $attributes);
            }
        }
        return $result;
    }

    /**
     * @param $model
     * @return mixed
     */
    public function delete($model)
    {
        $result = $this->getEpic()->delete($model);
        $this->flushGetKeys($model);
        return $result;
    }

    /**
     * @param array $conditions
     * @return void
     */
    public function deleteByConditions(array $conditions)
    {
        $models = $this->findByConditions($conditions);
        foreach ($models as $model) {
            $this->delete($model);
        }
    }
}
