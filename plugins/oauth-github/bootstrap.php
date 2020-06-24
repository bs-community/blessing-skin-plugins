<?php

use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;

return function (Dispatcher $events, Filter $filter) {
    $events->listen(
        'SocialiteProviders\Manager\SocialiteWasCalled',
        'SocialiteProviders\GitHub\GitHubExtendSocialite@handle'
    );

    config(['services.github' => [
        'client_id' => env('GITHUB_KEY'),
        'client_secret' => env('GITHUB_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI'),
    ]]);

    $filter->add('oauth_providers', function (Collection $providers) {
        $providers->put('github', [
            'icon' => 'github',
            'displayName' => 'GitHub',
        ]);

        return $providers;
    });
};
