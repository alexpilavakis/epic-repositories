<?php

namespace Ulex\EpicRepositories\Decorators;
use Illuminate\Database\Eloquent\Collection;

abstract class EloquentCachingDecorator extends AbstractCachingDecorator
{
    /**
     * @param $key
     * @return string
     */
    protected function getKeyPrefix($key): string
    {
        return "eloquent:$this->name:$key";
    }

    /**
     * @return string
     */
    protected function getCollectionPrefix()
    {
        return "eloquent:$this->name:" . self::CACHE_TAG_COLLECTION;
    }

    /**
     * @param $id
     * @return void
     */
    protected function flushById($id): void
    {
        $this->forget("find:$id");
        $this->forget("findOrFail:$id");
    }


    /**
     * @param $attribute
     * @param $value
     * @return void
     */
    protected function flushByAttribute($attribute, $value): void
    {
        $this->flushFunction('findBy', [$attribute, $value]);
    }


    /**
     ************
     * Find Single
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
        return $this->remember(__FUNCTION__, func_get_args(), true);
    }

    /**
     **********
     * Find Many
     **********
     */

    /**
     * @return mixed
     */
    public function all()
    {
        return $this->remember(__FUNCTION__, func_get_args(), true);
    }

    /**
     * @param array $conditions
     * @return array|mixed
     */
    public function findByConditions(array $conditions)
    {
        return $this->remember(__FUNCTION__, func_get_args(), true);
    }

    /**
     * @param string $column
     * @param array $values
     * @return Collection
     */
    public function findWhereIn(string $column, array $values)
    {
        return $this->remember(__FUNCTION__, func_get_args(), true);
    }

    /**
     ****************
     * Create, Update, Delete
     ****************
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
     * @return mixed
     */
    public function createMany(array $attributes)
    {
        $result = $this->getEpic()->createMany($attributes);
        $this->flushCollections();
        return $result;
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
     * @param array $attributes
     * @param array $values
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $model = $this->getEpic()->updateOrCreate($attributes, $values);
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
        $result = $this->getEpic()->update($model, $attributes);
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
                $this->flushGetKeys($model);
            }
            $this->flushCollections();
        }
        return $result;
    }

    /**
     * @param string $column
     * @param array $whereIn
     * @param array $attributes
     * @return bool
     */
    public function updateWhereIn(string $column, array $whereIn, array $attributes)
    {
        $result = $this->getEpic()->updateWhereIn($column, $whereIn, $attributes);
        if ($result) {
            $models = $this->findWhereIn($column, $whereIn);
            foreach ($models as $model) {
                $this->flushGetKeys($model);
            }
            $this->flushCollections();
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
        $models = $this->getEpic()->findByConditions($conditions);
        foreach ($models as $model) {
            $this->delete($model);
        }
    }
}
