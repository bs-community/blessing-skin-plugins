<?php

use App\Services\Hook;

return function () {
    Hook::addRoute(function () {
        Route::namespace('Blessing\Legacy')
            ->group(function () {
                Route::get('/skin/{player}', 'TextureController@skin');
                Route::get('/cape/{player}', 'TextureController@cape');
            });
    });
};
