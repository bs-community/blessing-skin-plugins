<?php

namespace Blessing\RestrictedEmailDomains;

use Blessing\Rejection;

class EmailFilter
{
    public function filter($can)
    {
        $allowList = json_decode(option('restricted-email-domains.allow', '[]'), true);
        $denyList = json_decode(option('restricted-email-domains.deny', '[]'), true);

        [$head, $domain] = explode('@', request('email', ''));

        if (count($allowList) > 0 && !in_array($domain, $allowList)) {
            return new Rejection(
                trans('Blessing\RestrictedEmailDomains::general.rejection', compact('domain'))
            );
        }
        if (count($denyList) > 0 && in_array($domain, $denyList)) {
            return new Rejection(
                trans('Blessing\RestrictedEmailDomains::general.rejection', compact('domain'))
            );
        }

        return $can;
    }
}
