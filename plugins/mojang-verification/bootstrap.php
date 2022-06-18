<?php

use App\Services\Hook;
use Blessing\Filter;
use GPlane\Mojang\Listeners;
use GPlane\Mojang\MojangVerification;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events, Filter $filter) {
    config(['logging.channels.mojang-verification' => [
        'driver' => 'single',
        'path' => storage_path('logs/mojang-verification.log'),
    ]]);

    config(['services.microsoft' => [
        'client_id' => env('MICROSOFT_KEY'),
        'client_secret' => env('MICROSOFT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
    ]]);

    View::composer('GPlane\Mojang::bind', function ($view) {
        $view->with('score', option('mojang_verification_score_award', 0));
    });

    $events->listen(
        'SocialiteProviders\Manager\SocialiteWasCalled',
        'GPlane\Mojang\Providers\MicrosoftExtendSocialite@handle'
    );

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

    Hook::addRoute(function () {
        Route::prefix('mojang')
            ->middleware(['web', 'auth'])
            ->namespace('GPlane\Mojang')
            ->group(function () {
                Route::get('verify', 'AccountController@verify');
                Route::get('callback', 'AccountController@verifyCallback');
                Route::post('update-uuid', 'AccountController@uuid');
            });
    });
};
