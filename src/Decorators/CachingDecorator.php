<?php

namespace Ulex\EpicRepositories\Decorators;

use Ulex\EpicRepositories\Interfaces\CachingDecoratorInterface;
use Ulex\EpicRepositories\Interfaces\DecoratorInterface;
use Ulex\EpicRepositories\Interfaces\RepositoryInterface;
use Illuminate\Contracts\Cache\Repository as Cache;
use ReflectionClass;
use Closure;

abstract class CachingDecorator implements CachingDecoratorInterface, DecoratorInterface
{
    /** @var RepositoryInterface */
    public $repository;

    /** @var RepositoryInterface */
    public $customRepository = null;

    /** @var DecoratorInterface */
    protected $decorator;

    /** @var Cache */
    protected $cache;

    /** @var */
    protected $model;

    /** @var bool */
    protected $cacheForever = false;

    /**
     * @param $name
     * @return $this|null
     */
    public function useRepository($name)
    {
        $repositories = app()->config['epic-repositories.repositories'];
        $namespace = $repositories[$name];
        if (is_null($namespace)) {
            return null;
        }
        $class = $namespace . "\\" . $this->tag() . "Repository";
        if(class_exists($class)) {
            $this->customRepository = new $class($this->model);
            return $this;
        }
        return null;
    }

    /**
     * @param $name
     * @return $this|null
     */
    public function withDecorator($name)
    {
        if ($name == 'caching') {
            return $this;
        }
        $decorators = app()->config['epic-repositories.decorators'];
        $namespace = $decorators[$name];
        if (is_null($namespace)) {
            return null;
        }
        $class = $namespace . "\\" . $this->tag() . ucfirst($name) . "Decorator";
        if(class_exists($class)) {
            return new $class($this->model);
        }
        return null;
    }

    /**
     * NOTE: Cache tags are not supported when using the `file` or `database` cache drivers.
     * @return string
     */
    protected function tag(): string
    {
        return (new ReflectionClass($this->model))->getShortName();
    }

    /**
     * @return int
     */
    protected function ttl(): int
    {
        return app()->config['epic-repositories.ttl.default'];
    }

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
        return sprintf('%s:%s', $function, implode(':', $arguments));
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function has(string $key)
    {
        return $this->cache->tags($this->tag())->has($key);
    }

    /**
     * @return bool
     */
    public function flushTag()
    {
        return $this->cache->tags($this->tag())->flush();
    }


    /**
     * Flush all 'get' keys for this model instance
     *
     * @param $model
     */
    public function flushGetKeys($model)
    {
        $this->forget("getAll");
        $this->forget("getById:{$model->id}");
        $oldAttributeValues = $model->getAttributes();
        $this->forgetAttributes($oldAttributeValues);
    }

    /**
     * @param $key
     * @return bool
     */
    public function forget($key)
    {
        return $this->cache->tags($this->tag())->forget($key);
    }

    /**
     * @param array $attributes
     */
    public function forgetAttributes(array $attributes)
    {
        if (empty($attributes)) {
            return;
        }
        /** when timestamps() is used */
        unset($attributes['created_at']);
        unset($attributes['updated_at']);
        /** when softDeletes() is used */
        unset($attributes['deleted_at']);

        foreach ($attributes as $attribute => $value) {
            $key = $this->key('getBy', [$attribute, $value]);
            if ($this->has($key)) {
                $this->forget($key);
            }
        }
    }

    /**
     * @return RepositoryInterface
     */
    protected function getRepository()
    {
        $repository = $this->customRepository;
        if (isset($repository)) {
            $this->customRepository = null;
            return $repository;
        }
        return $this->repository;
    }

    /**
     * @param string $function
     * @param $arguments
     * @return array|mixed
     */
    protected function remember(string $function, $arguments)
    {
        $key = $this->key($function, $arguments);
        $closure = $this->closure($function, $arguments);
        if ($this->cacheForever) {
            return $this->cache->tags($this->tag())->rememberForever($key, $closure);
        }
        return $this->cache->tags($this->tag())->remember($key, $this->ttl(), $closure);
    }

    /**
     * @param $function
     * @param $arguments
     * @return Closure
     */
    private function closure($function, $arguments)
    {
        $repository = $this->getRepository();
        return function () use ($function, $arguments, $repository) {
            return $repository->$function(...$arguments);
        };
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        return $this->remember(__FUNCTION__, func_get_args());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->remember(__FUNCTION__, func_get_args());
    }

    /**
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function getBy($attribute, $value)
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
     * @param $attributes
     * @return mixed
     */
    public function create($attributes)
    {
        $this->forget("getAll");
        return $this->getRepository()->create($attributes);
    }

    /**
     * @param array $attributes
     */
    public function createMany(array $attributes)
    {
        $this->forget("getAll");
        $this->repository->createMany($attributes);
    }

    /**
     * @param $attributes
     * @return mixed
     */
    public function firstOrCreate($attributes)
    {
        /** TODO Flush cache in case of first */
        return $this->getRepository()->firstOrCreate($attributes);
    }

    /**
     * @param $attributes
     * @return mixed
     */
    public function updateOrCreate($attributes)
    {
        /** TODO Flush cache in case of update */
        return $this->getRepository()->updateOrCreate($attributes);
    }

    /**
     * @param $model
     * @param $attributes
     * @return mixed
     */
    public function update($model, $attributes)
    {
        $this->flushGetKeys($model);
        return $this->getRepository()->update($model, $attributes);
    }

    /**
     * @param array $conditions
     * @param array $attributes
     * @return bool
     */
    public function updateWithConditions(array $conditions, array $attributes)
    {
        $result = $this->repository->updateWithConditions($conditions, $attributes);
        if ($result) {
            /** TODO Find better approach than to invalidate the entire tag */
            $this->flushTag();
        }
        return $result;
    }

    /**
     * @param $model
     * @return mixed
     */
    public function delete($model)
    {
        $this->forget("getAll");
        return $this->getRepository()->delete($model);
    }

    /**
     * @param string $column
     * @param array $attributes
     */
    public function deleteManyBy(string $column, array $attributes)
    {
        $this->forget("getAll");
        $this->model->query()->whereIn($column, $attributes)->delete();
    }
}
