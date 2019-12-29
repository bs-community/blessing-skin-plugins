<?php

use App\Services\Hook;

return function () {
    Hook::addRoute(function () {
        Route::any('/usm/{player}.json', 'Blessing\Usm\ProfileController@json');
    });
};
