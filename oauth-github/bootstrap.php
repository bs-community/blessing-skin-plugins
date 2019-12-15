<?php

use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(
        'SocialiteProviders\Manager\SocialiteWasCalled',
        'SocialiteProviders\GitHub\GitHubExtendSocialite@handle'
    );

    config(['services.github' => [
        'client_id' => env('GITHUB_KEY'),
        'client_secret' => env('GITHUB_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI'),
    ]]);

    resolve('oauth.providers')
        ->put('github', ['icon' => 'github', 'displayName' => 'GitHub']);
};
