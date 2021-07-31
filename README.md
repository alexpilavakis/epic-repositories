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

<h2> Create Repository/ies, Decorator/s with their Interfaces for a Model </h2>

Run the following php artisan command where the argument is your Model name (example Post):
```php
php artisan make:epic:repository Post
```
Expected Result:
```php
Eloquent Repository created successfully.
Elastic Repository created successfully.
Interface created successfully.
Caching Decorator created successfully.
Add Model in `models` array in config/epic-repositories.php
```
The following folders will be created in your `app/Repositories` folder (if they don't exist):
```php
Decorators
Eloquent
Elastic //if enabled
Interfaces
```
As seen in the result remember to add the Model in `config/epic-repositories.php` :
```php
...
'models' => [
        'User' => App\Models\User::class,
        'Post' => App\Models\Post::class,
]
...
```



## What It Does
This package provides an abstract structure that uses the Repository design pattern with decorators (caching as base) for you application.

Once installed you can create Repositories for your models that cache the data from your queries.
EloquentRepository is provided and ready to use. ElasticRepository can be enabled as well if you choose. Follow the same principle for any data resource you have on your application.

```php
// Example when injecting to a controller 
/*
* @param UserRepositoryInterface $siteRepository
*/
public function __construct(UserRepositoryInterface $userRepository)
{
    $this->userRepository = $userRepository;
}

...

/** @var User $user */
$user = $this->userRepository->getBy('name', $userName);

$allFromElastic = $this->userRepository->useRepository('elastic')->all();
$allFromEloquent = $this->userRepository->all();
```

## Contributing

This package is mostly based on [Jeffrey Way](https://twitter.com/jeffrey_way)'s awesome [Laracasts](https://laracasts.com) lessons
when using the repository design pattern on [Laravel From Scratch](https://laracasts.com/series/laravel-6-from-scratch) series.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
