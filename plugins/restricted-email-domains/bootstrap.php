<?php

namespace Blessing\RestrictedEmailDomains;

use App\Events\RenderingFooter;
use App\Services\Hook;
use App\Services\Plugin;
use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Route;

return function (Filter $filter, Dispatcher $events, Request $request, Plugin $plugin) {
    $filter->add('can_register', EmailFilter::class);
    $filter->add('user_can_edit_profile', EmailFilter::class);

    $events->listen(RenderingFooter::class, function (RenderingFooter $event) use ($request) {
        if (!$request->is('auth/*')) {
            return;
        }

        $domains = option('restricted-email-domains.allow', '[]');
        $event->addContent('<script type="application/json" id="allowed-email-domains">'.$domains.'</script>');
    });

    $filter->add('scripts', function ($scripts) use ($plugin, $request) {
        if ($request->is('auth/*')) {
            $scripts[] = ['src' => $plugin->assets('autocomplete.js'), 'crossorigin' => 'anonymous'];
        }

        return $scripts;
    });

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
