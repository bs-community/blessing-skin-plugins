<?php

namespace InvitationCodes;

use App\Http\Controllers\Controller;
use DB;
use Utils;

class InvitationCodeController extends Controller
{
    public function generate()
    {
        if (request()->method() == 'POST' && request('amount')) {
            $this->generateInvitationCodes(request('amount'));
        }

        return view('InvitationCodes::generate', [
            'available' => DB::table('invitation_codes')->where('used_by', 0)->get(),
            'used'      => DB::table('invitation_codes')->where('used_by', '<>', 0)->get(),
        ]);
    }

    protected function generateInvitationCodes($amount)
    {
        $codes = [];

        for ($i = 0; $i < $amount; $i++) {
            $codes[] = [
                'code'         => md5(time().rand()),
                'generated_at' => Utils::getTimeFormatted(),
            ];
        }

        DB::table('invitation_codes')->insert($codes);
    }
}
