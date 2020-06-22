<?php

namespace InvitationCodes;

use Blessing\Rejection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckInvitationCode
{
    /** @var Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function filter($can)
    {
        $code = $this->request->input('invitationCode');
        if (empty($code)) {
            return new Rejection(trans('InvitationCodes::messages.empty'));
        }

        $result = DB::table('invitation_codes')->where('code', $code)->first();

        if ($result && $result->used_by == 0) {
            session(['using_invitation_code' => $code]);

            return $can;
        }

        return new Rejection(trans('InvitationCodes::messages.invalid'));
    }
}
