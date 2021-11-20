<?php

namespace Ulex\EpicRepositories\Decorators;

use ReflectionException;
use Ulex\EpicRepositories\Interfaces\EpicInterface;
use Illuminate\Contracts\Cache\Repository as Cache;
use Closure;

abstract class EloquentCachingDecorator extends AbstractDecorator
{
    /** @var Cache */
    protected $cache;

    /** @var int */
    protected $ttl;

    /** @var bool */
    protected $cacheForever = false;

    const CACHE_TAG_COLLECTION = 'collection';

    /**
     * EloquentCachingDecorator constructor.
     * @param $model
     * @param EpicInterface|null $epic
     * @throws ReflectionException
     */
    public function __construct($model, EpicInterface $epic = null)
    {
        parent::__construct($model, $epic);
        $this->cache = app('cache.store');
        $this->ttl = $this->ttl();
    }

    /**
     ********************
     * Configurations ***
     ** Caching & *******
     *** Flushing * *****
     ********************
     */

    /**
     * @return EpicInterface
     */
    protected function getEpic()
    {
        return $this->epic;
    }

    /**
     * @return int
     */
    private function ttl(): int
    {
        $ttl = app()->config['epic-repositories.ttl'];
        return $ttl[$this->name] ?? $ttl['default'];
    }

    /**
     * NOTE: Cache tags are not supported when using the `file` or `database` cache drivers.
     * @return array
     */
    protected function tag(): array
    {
        return [$this->name];
    }

    /**
     * Cache with multiple tags that can be invalidated
     * @param array $extraTags
     * @return array
     */
    protected function tags(array $extraTags): array
    {
        return array_merge($this->tag(), $extraTags);
    }

    /**
     * Flush all 'get' keys for this model instance along with any collections
     *
     * @param $model
     * @param array|null $attributes
     */
    public function flushGetKeys($model, array $attributes = null)
    {
        if (isset($model->id)) {
            $this->forget("find:{$model->id}");
            $this->forget("findOrFail:{$model->id}");
        }
        $attributes = $attributes ?? (is_object($model) ? $model->getAttributes() : $model);
        $this->flushAttributes($attributes);
        $this->flushCollections();
    }


    /**
     * @param array $attributes
     */
    protected function flushAttributes(array $attributes)
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
            $this->flushFunction('findBy', [$attribute, $value]);
            $this->flushFunction('checkIfExists', [$attribute, $value]);
        }
    }

    /**
     * Flush specific collection used in multiple tags
     */
    protected function flushCollections()
    {
        $this->flushTag(self::CACHE_TAG_COLLECTION);
    }

    /**
     * @param null $tag
     */
    protected function flushTag($tag = null)
    {
        $tag = $tag ?? $this->tag();
        $this->cache->tags($tag)->flush();
    }

    /**
     * @param string $function
     * @param $attributes
     * @param null $tags
     */
    public function flushFunction(string $function, $attributes = null, $tags = null)
    {
        $key = $this->key($function, $attributes);
        $tags = $tags ?? $this->tag();
        $this->forget($key, $tags);
    }

    /**
     * @param $key
     * @param null $tags
     * @return bool
     */
    public function forget($key, $tags = null)
    {
        $tags = $tags ?? $this->tag();
        return $this->cache->tags($tags)->forget($key);
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
        if (isset($arguments[0]) && is_array($arguments[0])) {
            $key = $function;
            foreach ($arguments[0] as $name => $value) {
                $key .= ':' . $name . ':' . $value;
            }
            return $key;
        }
        return sprintf('%s:%s', $function, implode(':', $arguments));
    }

    /**
     * @param string $function
     * @param $arguments
     * @param array|null $tags
     * @return array|mixed
     */
    protected function remember(string $function, $arguments, $tags = null)
    {
        $key = $this->key($function, $arguments);
        $closure = $this->closure($function, $arguments);
        $tags = $tags ?? $this->tag();
        if ($this->cacheForever) {
            return $this->cache->tags($tags)->rememberForever($key, $closure);
        }
        return $this->cache->tags($tags)->remember($key, $this->ttl, $closure);
    }

    /**
     * @param $function
     * @param $arguments
     * @return Closure
     */
    private function closure($function, $arguments)
    {
        $repository = $this->getEpic();
        return function () use ($function, $arguments, $repository) {
            return $repository->$function(...$arguments);
        };
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
        if ($result) {
            $this->flushGetKeys($model);
        }
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
