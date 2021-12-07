<?php

namespace Ulex\EpicRepositories\Decorators;

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

    const CACHE_TAG_COLLECTION = 'collection';

    /**
     * EloquentCachingDecorator constructor.
     * @param $model
     * @param EpicInterface|null $epic
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
    abstract protected function tag();

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
}
