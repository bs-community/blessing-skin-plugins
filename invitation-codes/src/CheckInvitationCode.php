<?php

namespace InvitationCodes;

use DB;
use Closure;

class CheckInvitationCode
{
    public function handle($request, Closure $next)
    {
        if (! $request->input('invitationCode')) {
            return json('邀请码不能为空', 1);
        }

        $code = request('invitationCode');
        $result = DB::table('invitation_codes')->where('code', $code)->first();

        if ($result && $result->used_by == 0) {
            session(['using_invitation_code' => $code]);

            return $next($request);
        }

        return json('邀请码无效', 1);
    }
}
