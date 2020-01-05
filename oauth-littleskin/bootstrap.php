<?php

use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(
        'SocialiteProviders\Manager\SocialiteWasCalled',
        'BlessingSocialiteProviders\LittleSkin\LittleSkinExtendSocialite@handle'
    );

    config(['services.littleskin' => [
        'client_id' => env('LITTLESKIN_KEY'),
        'client_secret' => env('LITTLESKIN_SECRET'),
        'redirect' => env('LITTLESKIN_REDIRECT_URI'),
    ]]);

    resolve('oauth.providers')
        ->put('littleskin', ['icon' => 'littleskin', 'displayName' => 'LittleSkin']);
};
