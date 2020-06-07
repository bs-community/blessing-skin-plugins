<?php

use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;

return function (Dispatcher $events, Filter $filter) {
    $events->listen(
        'SocialiteProviders\Manager\SocialiteWasCalled',
        'SocialiteProviders\Live\LiveExtendSocialite@handle'
    );

    config(['services.live' => [
        'client_id' => env('LIVE_KEY'),
        'client_secret' => env('LIVE_SECRET'),
        'redirect' => env('LIVE_REDIRECT_URI'),
    ]]);

    $filter->add('oauth_providers', function (Collection $providers) {
        $providers->put('littleskin', [
            'icon' => 'microsoft',
            'displayName' => 'Microsoft Live',
        ]);

        return $providers;
    });
};
