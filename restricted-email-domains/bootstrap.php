<?php

namespace Blessing\RestrictedEmailDomains;

use App\Services\Hook;
use Blessing\Filter;
use Route;

return function (Filter $filter) {
    $filter->add('can_register', EmailFilter::class);
    $filter->add('user_can_edit_profile', EmailFilter::class);

    Hook::addRoute(function () {
        Route::namespace('Blessing\RestrictedEmailDomains')
            ->prefix('admin/restricted-email-domains')
            ->middleware(['web', 'authorize', 'role:admin'])
            ->group(function () {
                Route::get('', 'ConfigController@list');
                Route::put('{list}', 'ConfigController@save')
                    ->where('list', 'allow|deny');
            });
    });
};
