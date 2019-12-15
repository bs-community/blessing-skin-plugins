<?php

use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(
        'SocialiteProviders\Manager\SocialiteWasCalled',
        'SocialiteProviders\Live\LiveExtendSocialite@handle'
    );

    config(['services.live' => [
        'client_id' => env('LIVE_KEY'),
        'client_secret' => env('LIVE_SECRET'),
        'redirect' => env('LIVE_REDIRECT_URI'),
    ]]);

    resolve('oauth.providers')
        ->put('live', ['icon' => 'microsoft', 'displayName' => 'Microsoft Live']);
};
