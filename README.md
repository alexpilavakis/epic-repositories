# Add a powerful core setup to your application's queries

## Documentation, Installation, and Usage Instructions

First, install the package via Composer:
```
composer require ulex/epic-repositories
```

------------------------------------------
<h2> Service Provider </h2>
<h3>For Laravel</h3>

You should publish the RepositoriesServiceProvider:
```php
php artisan vendor:publish --provider="Ulex\EpicRepositories\RepositoriesServiceProvider" --tag=config
```

Optional: The service provider will automatically get registered. Or you may manually add the service provider in your config/app.php file:
Laravel
```php
'providers' => [
// ...
Ulex\EpicRepositories\RepositoriesServiceProvider::class,
];
```
<h3>For Lumen</h3>

In your `bootstrap/app.php` add this:
```
$app->register(Ulex\EpicRepositories\RepositoriesServiceProvider::class);
```

---------------

<h2> Config </h2>

If config file `epic-repositories.php` was not published copy it to config folder with:
```
cp vendor/ulex/epic-repositories/config/epic-repositories.php config/epic-repositories.php
```

Take some time to review this config file. Here you can adjust various configurations for your repositories setup.
- Set a custom TTL for each of you models.
- Set the namespaces of your folders
- Enable/add the repositories and decorators you will use.
- Add your model bindings for each repository and which decorators to use for that repository.

#### Note: In order to use `elastic` repository you will have to add "elasticsearch/elasticsearch" package to your composer.json.

<h2> Create Repository/ies, Decorator/s with their Interfaces for a Model </h2>

First declare your models in config/epic-repositories

Run the following php artisan command:
```php
php artisan make:epic
```
Depending on you configuration, the necessary folders & classes will be created in your `app/Repositories` folder. 
Example:
```php
UserEloquentRepository created successfully.
UserEloquentInterface created successfully.
UserEloquentCachingDecorator created successfully.
```

## How to use
This package provides an abstract structure that uses the Repository design pattern with caching decorator/s for you application.

Once installed you can create Repositories for your models that cache the data from your queries.
Eloquent and Elastic Repositories are provided and ready to use if enabled. Follow the same principle for any data resource you have on your application.

```php
# Example when injecting to a controller 
use App\Repositories\Interfaces\UserEloquentInterface;

public function __construct(UserEloquentInterface $userRepository)
{
    $this->userRepository = $userRepository;
}

...

public function find($id)
{
    //retrieve from db and then cache the result
    $user = $this->userRepository->find($id);
    //retrieve straight from source, uncached
    $user = $this->userRepository->fromSource()->find($id);
} 
```
## Extending a model's CachingDecorator
For GET functions use the `remember` function the same way as in the AbstractCachingDecorator. This will ensure that this function is cached and invalidated properly. 
NOTE that this will return a single result. 
#### PostsEloquentCachingDecorator.php
```php
public function getLatestPost($user_id)
    {
        return $this->remember(__FUNCTION__, func_get_args());
    }
```
<b>Note:</b> Remember to add the cache invalidation of the new function by extending flushGetKeys in the model's CachingDecorator.
```php
public function flushGetKeys($model, $attributes = null)
{
    $user_id = $model->user_id;
    $key = $this->key('getLatestPost', compact('user_id'));
    parent::flushGetKeys($model, $attributes);
}
```
#### PostsEloquentRepository.php
Add the query in the model's repository
```php
public function getLatestPost($user_id)
{
    return $this->model->query()->where('user_id', '=', $user_id)->latest()->first();
}
```
For GET functions that return collections you can pass tags to `remember` function. Use `collection` tag or add a custom one or multiple ones.
```php
public function getUserPosts($user_id)
{
    return $this->remember(__FUNCTION__, func_get_args(), [self::CACHE_TAG_COLLECTION]);
}
```
The above tag will be flushed in `flushGetKeys` which calls `flushCollections()`. If you add a custom tag and you want to it to be flushed as well then you can extend `flushCollections` like:  
```php
public function flushCollections()
{
    $this->flushTag('myTag');
    parent::flushCollections();
}

```

## Contributing

This package is mostly based on [Jeffrey Way](https://twitter.com/jeffrey_way)'s awesome [Laracasts](https://laracasts.com) lessons
when using the repository design pattern on [Laravel From Scratch](https://laracasts.com/series/laravel-6-from-scratch) series.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
