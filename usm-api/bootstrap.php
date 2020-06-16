<?php

use App\Services\Hook;

return function () {
    Hook::addRoute(function () {
        Route::prefix('usm')->group(function () {
            Route::get('{player}.json', 'Blessing\Usm\ProfileController@json');
            Route::get('textures/{hash}', 'TextureController@texture');
        });
    });
};
