<?php

use DataIntegration\Utils;
use DataIntegration\Listener;

return function () {
    Utils::init();

    if (option('da_adapter') == "") return;

    // bind synchronizer to container
    App::bind('synchronizer', function() {
        $classname = "DataIntegration\Synchronizer\\".option('da_adapter');

        if (class_exists($classname)) {
            return new $classname;
        } else {
            option(['da_adapter' => '']);
        }
    });

    App::singleton('db.target', function() {
        $config = unserialize(option('da_connection'));

        $db = new App\Services\Database($config);
        return $db->table($config['table'], true);
    });

    App::instance('db.self', DB::table('users'));

    Event::subscribe(Listener\DisableMultiPlayer::class);
    Event::subscribe(Listener\SynchronizeUser::class);
    Event::subscribe(Listener\EncryptPassword::class);

    if (option('da_bilateral')) {
        Event::subscribe(Listener\BilateralSync::class);
    }

};
