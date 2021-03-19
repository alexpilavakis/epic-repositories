<?php

namespace Ulex\EpicRepositories\Interfaces;

interface CachingDecoratorInterface
{

    /**
     * @return mixed
     */
    public function getAll();

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id);

    /**
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function getBy($attribute, $value);

    /**
     * @param $id
     * @return mixed
     */
    public function findOrFail($id);

    /**
     * @param $attributes
     * @return mixed
     */
    public function create($attributes);

    /**
     * @param array $attributes
     * @return mixed
     */
    public function createMany(array $attributes);

    /**
     * @param $attributes
     * @return mixed
     */
    public function firstOrCreate($attributes);

    /**
     * @param $attributes
     * @return mixed
     */
    public function updateOrCreate($attributes);

    /**
     * @param $model
     * @param $attributes
     * @return mixed
     */
    public function update($model, $attributes);

    /**
     * @param array $conditions
     * @param array $attributes
     * @return bool|int
     */
    public function updateWithConditions(array $conditions, array $attributes);

    /**
     * @param $model
     * @return mixed
     */
    public function delete($model);

    /**
     * @param string $column
     * @param array $attributes
     * @return mixed
     */
    public function deleteManyBy(string $column, array $attributes);
}
