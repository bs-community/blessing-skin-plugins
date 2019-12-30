<?php

use App\Services\Hook;
use GPlane\Mojang\Listeners;
use Illuminate\Contracts\Events\Dispatcher;

require __DIR__.'/src/helpers.php';

return function (Dispatcher $events) {
    View::composer('GPlane\Mojang::bind', function ($view) {
        $view->with('score', option('mojang_verification_score_award', 0));
    });

    $events->listen('auth.login.attempt', Listeners\CreateNewUser::class);

    $events->listen(
        Illuminate\Auth\Events\Authenticated::class,
        Listeners\OnAuthenticated::class
    );

    Hook::addScriptFileToPage(
        plugin_assets('mojang-verification', 'register-notice.js'),
        ['auth/register']
    );

    Hook::addRoute(function ($router) {
        Route::prefix('mojang')
            ->middleware(['web', 'auth'])
            ->namespace('GPlane\Mojang')
            ->group(function () {
                Route::post('verify', 'AccountController@verify');
                Route::post('update-uuid', 'AccountController@uuid');
            });
    });
};
