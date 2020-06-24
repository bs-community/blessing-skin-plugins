<?php

use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;

return function (Dispatcher $events, Filter $filter) {
    $events->listen(
        'SocialiteProviders\Manager\SocialiteWasCalled',
        'LittleSkinChina\BsSocialiteProviderLittleSkin\LittleSkinExtendSocialite@handle'
    );

    config(['services.littleskin' => [
        'client_id' => env('LITTLESKIN_KEY'),
        'client_secret' => env('LITTLESKIN_SECRET'),
        'redirect' => env('LITTLESKIN_REDIRECT_URI'),
    ]]);

    $filter->add('oauth_providers', function (Collection $providers) {
        $providers->put('littleskin', [
            'icon' => 'littleskin',
            'displayName' => 'LittleSkin',
        ]);

        return $providers;
    });
};
