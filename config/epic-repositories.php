<?php

/*
 * This file is part of ulex/epic-repositories.
 *
 * (c) Alexandros Pilavakis <alexpilavakis@hotmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Time To Live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in seconds) that data will be cached.
    |
    | The Time To Live (TTL) of an item is the amount of time between when that item is stored,
    | and it is considered stale. The TTL is normally defined by an integer representing time
    | in seconds, or a DateInterval object.
    |
    | Defaults to 1 hour.
    |
    */

    'ttl' => [
        'default' => 3600,
        //'user' => 604800
    ],

    /*
    |--------------------------------------------------------------------------
    | Repositories and Decorators configuration
    |--------------------------------------------------------------------------
    | Set the namespaces of your repositories sub folder.
    | Set all available repositories and decorators.
    | Set the base Repository that is used to access the database.
    |
    */

    'namespaces' => [
        'interfaces' => 'App\Repositories\Interfaces',
        'decorators' => 'App\Repositories\Decorators',
        'repositories' => 'App\Repositories'
    ],

    'repositories' => [
        'eloquent',
        //'database',
        //'elastic', //Note: In order to use `elastic` repository you will have to add "elasticsearch/elasticsearch" package to your composer.json
    ],

    'decorators' => [
        'caching',
    ],

    /*
    |--------------------------------------------------------------------------
    | Repositories/Decorators/Models Binding
    |--------------------------------------------------------------------------
    |   Structure:
    |
    |   'custom_repository' => [
    |       'decorators' => ['custom_decorator', 'custom_decorator_2'],
    |       'models' => [
    |           'User' => App\Models\User::class,
    |           'Post' => App\Models\Post::class,
    |           ...
    |       ]
    |   ],
    |
    */

    'bindings' => [
        'eloquent' => [
            'decorators' => ['caching'],
            'models' => [
                'User' => App\Models\User::class,
                //'Model => App\Models\Model::class,
            ]
        ],
        /*'elastic' => [
            'decorators' => ['caching'],
            'models' => [
                'User' => App\Models\User::class
            ]
        ],*/
    ],

    'configs' => [
    /*
    |--------------------------------------------------------------------------
    | Custom Elasticsearch Client Configuration
    |--------------------------------------------------------------------------
    |
    | This array will be passed to the Elasticsearch client.
    | See configuration options here:
    |
    | https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/configuration.html
    */
        'elastic' => [
            'hosts' => explode(',', env('ELASTICSEARCH_HOSTS', 'localhost:9200')),
            'retries' => 3,
        ],
        //...
    ]
];
