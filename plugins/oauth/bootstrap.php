<?php

use App\Services\Hook;
use Blessing\Filter;
use Illuminate\Support\Facades\View;

return function (Filter $filter) {
    app()->register(SocialiteProviders\Manager\ServiceProvider::class);

    View::composer('Blessing\OAuthCore::providers', function ($view) use ($filter) {
        $providers = $filter->apply('oauth_providers', collect());

        $view->with('providers', $providers);
    });

    $filter->add('auth_page_rows:login', function ($rows) {
        $length = count($rows);
        array_splice($rows, $length - 1, 0, ['Blessing\OAuthCore::providers']);

        return $rows;
    });

    $filter->add('auth_page_rows:register', function ($rows) {
        $rows[] = 'Blessing\OAuthCore::providers';

        return $rows;
    });

    Hook::addRoute(function () {
        Route::prefix('auth/login')
            ->namespace('Blessing\OAuthCore')
            ->middleware(['web', 'guest'])
            ->group(function () {
                Route::get('{driver}', 'AuthController@login');
                Route::get('{driver}/callback', 'AuthController@callback');
            });
    });
};
