<?php

namespace Blessing\DisableRegistration;

use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function handle()
    {
        abort(503, trans('Blessing\DisableRegistration::general.notice'));
    }
}
