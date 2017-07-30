<?php

use App\Services\Hook;
use Yggdrasil\Service\BlessingYggdrasilService;
use Yggdrasil\Service\YggdrasilServiceInterface;
use Illuminate\Contracts\Events\Dispatcher;

// Load configuration
require __DIR__.'/config.php';

return function (Dispatcher $events) {

    // Create tables
    if (! Schema::hasTable('uuid')) {
        Schema::create('uuid', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('uuid', 255);
        });
    }

    if (app('request')->is('api/yggdrasil/*')) {
        Log::info('============================================================');
        Log::info(app('request')->method(), [app('request')->path()]);
    }

    app()->bind(YggdrasilServiceInterface::class, BlessingYggdrasilService::class);

    Hook::addRoute(function ($router) {
        $router->group([
            'middleware' => ['web'],
            'namespace'  => 'Yggdrasil\Controllers',
            'prefix' => 'api/yggdrasil'
        ], function ($router) {
            require __DIR__.'/routes.php';
        });
    });
};
