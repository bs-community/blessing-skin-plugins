<?php

namespace Blessing\DisableRegistration;

class AuthController
{
    public function handle()
    {
        abort(503, trans('Blessing\DisableRegistration::general.notice'));
    }
}
