<?php

namespace Ulex\EpicRepositories\Decorators;

use ReflectionException;
use Ulex\EpicRepositories\Interfaces\EpicInterface;
use Illuminate\Contracts\Cache\Repository as Cache;
use Closure;

abstract class AbstractCachingDecorator extends AbstractDecorator
{
    protected Cache $cache;

    protected int $ttl;

    protected bool $cacheForever = false;

    protected bool $fromSource = false;

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
     *****************
     * Configurations
     * Caching & Flushing
     *****************
     */

    /**
     * @return EpicInterface
     */
    protected function getEpic()
    {
        return $this->epic;
    }

    /**
     * @return EpicInterface
     */
    public function fromSource()
    {
        $this->fromSource = true;
        return $this;
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
     * This will prefix the key base on the CachingDecorator
     * @return string
     */
    abstract protected function getKeyPrefix($key);

    /**
     * This will prefix the collection base on the CachingDecorator
     *
     * @return string
     */
    abstract protected function getCollectionPrefix();

    /**
     * @param $id
     * @return void
     */
    abstract protected function flushById($id);

    /**
     * @param $attribute
     * @param $value
     * @return void
     */
    abstract protected function flushByAttribute($attribute, $value);

    /**
     * @return void
     */
    abstract protected function flushCollections();

    /**
     * Flush all 'get' keys for this model instance along with any collections
     *
     * @param $model
     */
    public function flushGetKeys($model)
    {
        if (isset($model->id)) {
            $this->flushById($model->id);
        }
        $attributes = is_object($model) ? $model->getAttributes() : $model;
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
            $this->flushByAttribute($attribute, $value);
        }
    }

    /**
     * @param array|string $tags
     */
    protected function flushTag($tags)
    {
        $this->cache->tags($tags)->flush();
    }

    /**
     * @param string $function
     * @param null $attributes
     */
    public function flushFunction(string $function, $attributes = null)
    {
        $this->forget($this->key($function, $attributes));
    }

    /**
     * @param $key
     * @return bool
     */
    public function forget($key)
    {
        return $this->cache->forget($this->getKeyPrefix($key));
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
        foreach ($arguments as $key => $value) {
            if (is_object($value)) {
                $value = (array)$value;
            }
            if (is_array($value)) {
                sort($value);
                $arguments[$key] = $this->hashArray($value);
            }
        }
        return $this->simpleKey($function, $arguments);
    }

    /**
     * @param $array
     * @return string
     */
    protected function hashArray($array)
    {
        return hash('sha1', (json_encode($array)));
    }

    /**
     * @param $function
     * @param $arguments
     * @return string
     */
    protected function simpleKey($function, $arguments)
    {
        return sprintf('%s:%s', $function, implode(':', $arguments));
    }

    /**
     * @param $function
     * @param $arguments
     * @return string
     */
    protected function advanceKey($function, $arguments)
    {
        /** Convert any objects to arrays */
        foreach ($arguments as $key => $argument) {
            if (is_object($argument)) {
                $arguments[$key] = (array)$argument;
            }
        }
        return $this->createHashKey($function, $arguments);
    }

    /**
     * @param $function
     * @param $arguments
     * @return string
     */
    protected function createHashKey($function, $arguments)
    {
        $arguments = hash('sha1', (json_encode($arguments)));
        return "$function:$arguments";
    }

    /**
     * @param string $function
     * @param $arguments
     * @param array|null $tags
     * @return array|mixed
     */
    protected function remember(string $function, $arguments, array $tags = null)
    {
        $closure = $this->closure($function, $arguments);
        if ($this->fromSource) {
            $this->fromSource = false;
            return $closure();
        }
        $key = $this->key($function, $arguments);
        if ($this->cacheForever) {
            return $this->cache->rememberForever($this->getKeyPrefix($key), $closure);
        }
        if (!empty($tags)) {
            return $this->cache->tags($tags)->remember($this->getKeyPrefix($key), $this->ttl, $closure);
        }
        return $this->cache->remember($this->getKeyPrefix($key), $this->ttl, $closure);
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
}
