<?php

use App\Services\Hook;
use Blessing\Filter;
use GPlane\Mojang\Listeners;
use GPlane\Mojang\MojangVerification;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events, Filter $filter) {
    View::composer('GPlane\Mojang::bind', function ($view) {
        $view->with('score', option('mojang_verification_score_award', 0));
    });

    $events->listen('auth.login.attempt', Listeners\CreateNewUser::class);

    $events->listen(
        Illuminate\Auth\Events\Authenticated::class,
        Listeners\OnAuthenticated::class
    );

    $filter->add('user_badges', function ($badges, $user) {
        if (MojangVerification::where('user_id', $user->uid)->count() == 1) {
            $badges[] = ['text' => trans('GPlane\Mojang::general.pro'), 'color' => 'purple'];
        }

        return $badges;
    });

    $filter->add('auth_page_rows:register', function ($rows) {
        $rows[] = 'GPlane\Mojang::notice';

        return $rows;
    });

    Hook::addRoute(function () {
        Route::prefix('mojang')
            ->middleware(['web', 'auth'])
            ->namespace('GPlane\Mojang')
            ->group(function () {
                Route::post('verify', 'AccountController@verify');
                Route::post('update-uuid', 'AccountController@uuid');
            });
    });
};
