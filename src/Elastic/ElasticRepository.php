<?php

namespace Ulex\EpicRepositories\Elastic;

use Ulex\EpicRepositories\Interfaces\CachingDecoratorInterface;
use Ulex\EpicRepositories\Interfaces\RepositoryInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

abstract class ElasticRepository implements RepositoryInterface, CachingDecoratorInterface
{
    /**
     * @var
     */
    protected $model;

    /**
     * @param $name
     * @return $this|null
     */
    public function useRepository($name)
    {
        return $this;
    }

    /** TODO Implement these with Elasticsearch package */


    /**
     * @return mixed
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->model->find($id);
    }

    /**
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function getBy($attribute, $value)
    {
        return $this->model->where($attribute, '=', $value)->first();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param $attributes
     * @return mixed
     */
    public function create($attributes)
    {
        return $this->model::create($attributes);
    }

    /**
     * Example:
     * $attributes = [
     *      [
     *          'attribute_1' => 'some_value',
     *          'attribute_2' => 'some_value',
     *          ...
     *      ],
     *      [
     *          'attribute_1' => 'some_value',
     *          'attribute_2' => 'some_value',
     *      ],
     *      ...
     * ];
     *
     * @param array $attributes
     */
    public function createMany(array $attributes)
    {
        if ($this->model->timestamps) {
            $date = Carbon::now();
            $attributes = array_map($this->mapValues($date), $attributes);
        }
        DB::table($this->model->getTable())->insertOrIgnore($attributes);
    }

    /**
     * @param $attributes
     * @return mixed
     */
    public function firstOrCreate($attributes)
    {
        return $this->model::firstOrCreate($attributes);
    }

    /**
     * @param $attributes
     * @return mixed
     */
    public function updateOrCreate($attributes)
    {
        return $this->model::updateOrCreate($attributes);
    }

    /**
     * @param $model
     * @param $attributes
     * @return mixed
     */
    public function update($model, $attributes)
    {
        return $model->update($attributes);
    }

    /**
     * @param array $conditions
     * @param array $attributes
     * @return bool|int
     */
    public function updateWithConditions(array $conditions, array $attributes)
    {
        return $this->model->where($conditions)->update($attributes);
    }

    /**
     * @param $model
     */
    public function delete($model)
    {
        $model->delete();
    }

    /**
     * Example:
     * $attributes = [
     *      'value_1'
     *      'value_2'
     *      ...
     *  ];
     * @param string $column
     * @param array $emails
     */
    public function deleteManyBy(string $column, array $emails)
    {
        $this->model->query()->whereIn($column, $emails)->delete();
    }

    /**
     * @param $name
     * @return $this|null
     */
    public function withDecorator($name)
    {
        $decorators = app()->config['epic-repositories.decorators'];
        $namespace = $decorators[$name];
        if (is_null($namespace)) {
            return null;
        }
        $class = $namespace . "\\" . $this->tag() . "CachingDecorator";
        if(class_exists($class)) {
            return new $class($this->model);
        }
        return null;
    }

    /**
     * @param $attribute
     * @param $date
     *
     * @return Closure
     */
    protected function mapValues($date)
    {
        return function ($item) use ($date) {
            $item['created_at'] = $item['updated_at'] = $date;
            return $item;
        };
    }
}
