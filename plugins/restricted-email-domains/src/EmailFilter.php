<?php

namespace Blessing\RestrictedEmailDomains;

use Blessing\Rejection;
use Illuminate\Support\Str;

class EmailFilter
{
    public function filter($can)
    {
        $allowList = json_decode(option('restricted-email-domains.allow', '[]'), true);
        $denyList = json_decode(option('restricted-email-domains.deny', '[]'), true);

        $request = request();
        if ($request->missing('email')) {
            return $can;
        }

        $email = $request->input('email', '');
        if (!Str::contains($email, '@')) {
            return $can;
        }

        [$head, $domain] = explode('@', $email);

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
