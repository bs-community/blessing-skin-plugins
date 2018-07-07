<?php

namespace InvitationCodes;

use DB;
use Closure;
use App\Http\Controllers\AuthController;

class CheckInvitationCode extends AuthController
{
    public function handle($request, Closure $next)
    {
        if (! $this->checkCaptcha($request)) {
            return json(trans('auth.validation.captcha'), 1);
        }

        $this->validate($request, [
            'invitationCode' => 'required'
        ], [
            'invitationCode.required' => '邀请码不能为空'
        ]);

        $code = request('invitationCode');
        $result = DB::table('invitation_codes')->where('code', $code)->first();

        if ($result && $result->used_by == 0) {
            session(['using_invitation_code' => $code]);

            return $next($request);
        }

        return json('邀请码无效', 1);
    }
}
