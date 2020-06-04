<?php

use App\Services\Hook;

return function () {
    Hook::addMenuItem('user', 3, [
        'title' => 'Blessing\ConfigGenerator::config.generate-config',
        'link' => 'user/config',
        'icon' => 'fa-book',
    ]);

    Hook::addRoute(function () {
        Route::get(
            '/user/config',
            'Blessing\ConfigGenerator\Controller@generate'
        )->middleware(['web', 'auth']);
    });
};
