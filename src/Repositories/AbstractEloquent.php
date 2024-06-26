<?php

namespace Ulex\EpicRepositories\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Ulex\EpicRepositories\Interfaces\RepositoryInterface;
use Ulex\EpicRepositories\Interfaces\EpicInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Closure;

abstract class AbstractEloquent implements RepositoryInterface
{
    protected $model;
    protected $epic;

    /**
     * @param $model
     * @param EpicInterface|null $epic
     */
    public function __construct($model, EpicInterface $epic = null)
    {
        $this->model = new $model();
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
     * Flush collection tag
     */
    public function flushCollections()
    {
    }

    /**
     *************
     * Find Single
     *************
     */

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->find($id);
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
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function findBy($attribute, $value)
    {
        return $this->model->where($attribute, '=', $value)->first();
    }

    /**
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function checkIfExists($attribute, $value)
    {
        return $this->model->where($attribute, '=', $value)->exists();
    }

    /**
     ***********
     * Find Many
     ***********
     */

    /**
     * @return mixed
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * @param array $conditions
     * @return mixed
     */
    public function findByConditions(array $conditions)
    {
        return $this->model->where($conditions)->get();
    }


    /**
     * @param string $column
     * @param array $values
     * @return Collection
     */
    public function findWhereIn(string $column, array $values)
    {
        return $this->model->whereIn($column, $values)->get();
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
     * @return int
     */
    public function createMany(array $attributes)
    {
        if ($this->model->timestamps) {
            $date = Carbon::now();
            $attributes = array_map($this->mapValues($date), $attributes);
        }
        return DB::table($this->model->getTable())->insertOrIgnore($attributes);
    }

    /**
     * @param $attributes
     * @return mixed
     */
    public function firstOrCreate($attributes)
    {
        return $this->model->firstOrCreate($attributes);
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->model->updateOrCreate($attributes, $values);
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
    public function updateByConditions(array $conditions, array $attributes)
    {
        return $this->model->where($conditions)->update($attributes);
    }

    /**
     * @param string $column
     * @param array $whereIn
     * @param array $attributes
     * @return bool
     */
    public function updateWhereIn(string $column, array $whereIn, array $attributes)
    {
        return $this->model->whereIn($column, $whereIn)->update($attributes);
    }

    /**
     * @param $model
     * @return mixed
     */
    public function delete($model)
    {
        return $model->delete();
    }

    /**
     * @param array $conditions
     */
    public function deleteByConditions(array $conditions)
    {
    }

    /**
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
