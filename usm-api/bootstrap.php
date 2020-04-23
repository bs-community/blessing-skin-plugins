<?php

use App\Services\Hook;

return function () {
    Hook::addRoute(function () {
        Route::get('/usm/{player}.json', 'Blessing\Usm\ProfileController@json');
    });
};
