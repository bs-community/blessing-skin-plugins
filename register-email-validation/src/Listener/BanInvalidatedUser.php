<?php

namespace RegisterValidation\Listener;

use DB;
use Log;
use Mail;
use View;
use Utils;
use App\Events\UserAuthenticated;
use App\Exceptions\PrettyPageException;
use Illuminate\Contracts\Events\Dispatcher;

class BanInvalidatedUser
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(UserAuthenticated::class, [$this, 'checkUserValidated']);
    }

    /**
     * Handle the event.
     *
     * @param  UserAuthenticated  $event
     * @return void
     */
    public function checkUserValidated(UserAuthenticated $event)
    {
        $user = $event->user;

        if ($result = DB::table(RV_TABLE_NAME)->where('uid', $user->uid)->first()) {
            if ($result->validated == "1") {
                return true;
            }
        } else {
            DB::table(RV_TABLE_NAME)->insert([
                'uid' => $user->uid,
                'token' => '',
                'validated' => '0',
                'last_sent_at' => Utils::getTimeFormatted(time() - 180),
                'expired_at' => Utils::getTimeFormatted()
            ]);
        }

        $result = DB::table(RV_TABLE_NAME)->where('uid', $user->uid)->first();
        $remain = strtotime($result->last_sent_at) + 180 - time();
        $msg    = "";

        if (isset($_POST['validate_email']) && $remain <= 0) {
            $user->email = $_POST['validate_email'];
            $user->save();

            $token = base64_encode($user->getToken().substr(time(), 4, 6).str_random(16));

            $url = option('site_url')."/auth/validate?uid={$user->uid}&token=$token";

            try {
                Mail::send('RegisterValidation::mail', ['validate_url' => $url], function ($m) use ($user) {
                    $site_name = option('site_name');

                    $m->from(config('mail.username'), $site_name);
                    $m->to($user->email)->subject("验证你在 $site_name 上的邮箱");
                });

                Log::info("[Register Validation] Mail has been sent to [{$user->email}] with token [$token]");
            } catch(\Exception $e) {
                throw new PrettyPageException(trans('auth.mail.failed', ['msg' => $e->getMessage()]), 2);
            }

            DB::table(RV_TABLE_NAME)->where('uid', $user->uid)->update([
                'token' => $token,
                'last_sent_at' => Utils::getTimeFormatted(),
                'expired_at' => Utils::getTimeFormatted(time() + 1800)
            ]);

            $msg = '邮件已发送，30 分钟内有效。';
        }

        echo view('RegisterValidation::validate', compact('user', 'remain', 'msg'))->render();
        exit;
    }

}
