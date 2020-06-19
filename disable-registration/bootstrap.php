<?php

use App\Services\Hook;
use App\Services\Plugin;
use Blessing\Filter;
use Blessing\Rejection;

return function (Filter $filter, Plugin $plugin) {
    $filter->add('can_register', function () {
        return new Rejection(trans('Blessing\DisableRegistration::general.title'));
    });
    $filter->add('auth_page_rows:login', function ($rows) {
        return array_filter($rows, function ($row) {
            return $row !== 'auth.rows.login.registration-link';
        });
    });

    Hook::addScriptFileToPage($plugin->assets('modifyButton.js'), ['/']);
    Hook::addRoute(function () {
        Route::get('auth/register', 'Blessing\DisableRegistration\AuthController@handle');
    });
};
