<?php

namespace Ulex\EpicRepositories\Decorators;

use ReflectionException;
use Ulex\EpicRepositories\Interfaces\EpicInterface;
use Illuminate\Contracts\Cache\Repository as Cache;
use Closure;

abstract class AbstractCachingDecorator extends AbstractDecorator
{
    /** @var Cache */
    protected $cache;

    /** @var int */
    protected $ttl;

    /** @var bool */
    protected $cacheForever = false;

    /** @var bool */
    protected $fromSource = false;

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
     * Cache with multiple tags that can be invalidated
     * @param array $extraTags
     * @return string
     */
    protected function tags(array $extraTags): string
    {
        return array_merge($this->getKeyPrefix('key'), $extraTags);
    }

    /**
     * Flush all 'get' keys for this model instance along with any collections
     *
     * @param $model
     */
    public function flushGetKeys($model)
    {
        if (isset($model->id)) {
            $this->forget("find:{$model->id}");
            $this->forget("findOrFail:{$model->id}");
        }
        $this->flushAttributes($model->getAttributes());
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
     * Flush collection tags
     */
    protected function flushCollections()
    {
        $this->flushTag(self::CACHE_TAG_COLLECTION);
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
        if (isset($arguments[0]) && is_array($arguments[0])) {
            $arguments = hash('sha1', (json_encode($arguments)));
            return "{$function}:{$arguments}";
        }
        return sprintf('%s:%s', $function, implode(':', $arguments));
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
