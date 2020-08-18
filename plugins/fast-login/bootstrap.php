<?php

use Blessing\FastLogin\InsertToFastLogin;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;

return function (Dispatcher $events) {
    $events->listen('user.mojang.verified', InsertToFastLogin::class);

    config(['database.connections.fast-login' => [
        'driver' => env('FAST_LOGIN_DRIVER', 'mysql'),
        'host' => env('FAST_LOGIN_HOST', 'localhost'),
        'port' => env('FAST_LOGIN_PORT', 3306),
        'username' => env('FAST_LOGIN_USERNAME'),
        'password' => env('FAST_LOGIN_PASSWORD'),
        'database' => env('FAST_LOGIN_DATABASE'),
    ]]);

    /** @var MySqlConnection|SQLiteConnection */
    $connection = DB::connection('fast-login')->table('premium')->getConnection();
    $connection->getPdo(); // to check if database is available
};
