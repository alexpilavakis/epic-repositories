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

First declare your models in config/epic-repositories

Run the following php artisan command:
```php
php artisan make:epic
```
The following folders will be created in your `app/Repositories` folder (if they don't exist):
```php
Decorators
Eloquent
Elastic //if enabled
Interfaces
```

## Contributing

This package is mostly based on [Jeffrey Way](https://twitter.com/jeffrey_way)'s awesome [Laracasts](https://laracasts.com) lessons
when using the repository design pattern on [Laravel From Scratch](https://laracasts.com/series/laravel-6-from-scratch) series.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
