<?php

namespace GPlane\Mojang;

use App\Http\Controllers\Controller;
use Composer\CaBundle\CaBundle;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

require_once __DIR__.'/helpers.php';

class AccountController extends Controller
{
    public function verify(Request $request)
    {
        $user = auth()->user();

        if (MojangVerification::where('user_id', $user->uid)->count() === 1) {
            return back();
        }

        $result = validate_mojang_account($user->email, $request->input('password'));
        if ($result['valid']) {
            bind_mojang_account($user, $result['profiles'], $result['selected']);

            return back();
        } else {
            return back()->with('mojang-failed', true);
        }
    }

    public function uuid()
    {
        $uuid = MojangVerification::where('user_id', auth()->id())->first()->uuid;
        try {
            $response = Http::withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
                ->get("https://api.mojang.com/user/profiles/$uuid/names");
            $name = $response->json()[0];

            DB::table('uuid')->where('name', $name)->update(['uuid' => $uuid]);

            return json(trans('GPlane\Mojang::uuid.success'), 0);
        } catch (\Exception $e) {
            return json(trans('GPlane\Mojang::uuid.failed'), 1);
        }
    }
}
