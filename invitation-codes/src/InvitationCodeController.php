<?php

namespace InvitationCodes;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class InvitationCodeController extends Controller
{
    public function list()
    {
        $free = DB::table('invitation_codes')->where('used_by', 0)->get();
        $used = DB::table('invitation_codes')->where('used_by', '<>', 0)->get();

        return view('InvitationCodes::codes', compact('free', 'used'));
    }

    public function generate(Request $request)
    {
        ['amount' => $amount] = $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $records = Collection::times($amount)
            ->map(function () {
                return [
                    'code' => md5(Str::random()),
                    'generated_at' => Carbon::now(),
                ];
            })
            ->values()
            ->toArray();

        DB::table('invitation_codes')->insert($records);

        return back();
    }
}
