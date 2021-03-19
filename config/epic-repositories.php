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
    | Namespaces of you Repositories sub folders
    |--------------------------------------------------------------------------
    |
    | NOTE: If these are modified then remember to change the Commands FOLDER constant as well.
    |
    */

    'namespaces' => [
        'interfaces' => 'App\Repositories\Interfaces',
        'decorators' => 'App\Repositories\Decorators',
    ],

    'decorators' => [
        'caching',
        //'logging'
    ],

    'repositories' => [
        'eloquent' => 'App\Repositories\Eloquent',
        'elastic' => 'App\Repositories\Elastic',
        //...
    ],
    /*
    |--------------------------------------------------------------------------
    | Models that need Repository Binding
    |--------------------------------------------------------------------------
    |
    |
    */

    'models' => [
        //'User' => App\Models\User::class
    ]
];
