<?php

namespace GPlane\Mojang\Providers;

use SocialiteProviders\Manager\SocialiteWasCalled;

class MicrosoftExtendSocialite
{
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('microsoft', __NAMESPACE__.'\MicrosoftProvider');
    }
}
