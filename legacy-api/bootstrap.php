<?php

use App\Services\Hook;

return function () {
    Hook::addRoute(function () {
        Route::namespace('Blessing\Legacy')
            ->group(function () {
                Route::get('/skin/{player}.png', 'TextureController@skin');
                Route::get('/cape/{player}.png', 'TextureController@cape');
            });
    });
};
