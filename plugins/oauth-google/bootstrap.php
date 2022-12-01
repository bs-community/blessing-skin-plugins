<?php

use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;

return function (Dispatcher $events, Filter $filter) {
    $events->listen(
        'SocialiteProviders\Manager\SocialiteWasCalled',
        'SocialiteProviders\Google\GoogleExtendSocialite@handle'
    );

    config(['services.google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_CALLBACK_URL'),
    ]]);

    $filter->add('oauth_providers', function (Collection $providers) {
        $providers->put('google', [
            'icon' => 'google',
            'displayName' => 'Google',
        ]);

        return $providers;
    });
};
