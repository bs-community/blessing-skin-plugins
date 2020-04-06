<?php

use App\Services\Hook;
use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;

return function (Filter $filter, Dispatcher $events) {
    Hook::addScriptFileToPage(
        plugin('share-registration-link')->assets('generate.js'),
        ['user']
    );
    Hook::addScriptFileToPage(
        plugin('share-registration-link')->assets('registration.js'),
        ['auth/register']
    );

    Hook::addRoute(function () {
        Route::namespace('GPlane\ShareRegistrationLink')
            ->middleware(['web', 'auth'])
            ->prefix('user/reg-links')
            ->group(function () {
                Route::get('', 'CodeController@list');
                Route::post('', 'CodeController@generate');
                Route::delete('{id}', 'CodeController@remove');
            });
    });

    $filter->add('grid:user.index', function ($grid) {
        $grid['widgets'][0][1][] = 'GPlane\ShareRegistrationLink::generate';

        return $grid;
    });

    $events->listen(
        'auth.registration.completed',
        GPlane\ShareRegistrationLink\CheckCode::class
    );
};
