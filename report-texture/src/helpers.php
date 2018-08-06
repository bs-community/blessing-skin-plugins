<?php

use ReportTexture\Report;

if (! function_exists('report_init_options')) {

    function report_init_options() {
        $items = [
            'reporter_score_modification' => 0,
            'reporter_reward_score' => 0
        ];

        foreach ($items as $key => $value) {
            if (! Option::has($key)) {
                Option::set($key, $value);
            }
        }
    }
}

if (! function_exists('report_status')) {

    function report_status($code) {
        switch ($code) {
            case Report::STATUS_PENDING:
                return trans('ReportTexture::general.status.pending');
                break;
            case Report::STATUS_RESOLVED:
                return trans('ReportTexture::general.status.resolved');
                break;
            case Report::STATUS_REJECTED:
                return trans('ReportTexture::general.status.rejected');
                break;
            default:
                return null;
                break;
        }
    }
}

if (! function_exists('report_uid_to_nickname')) {

    function report_uid_to_nickname($uid) {
        $user = App\Models\User::find($uid);
        if ($user) {
            return $user->getNickName();
        } else {
            return trans('general.unexistent-user');
        }
    }
}

if (! function_exists('report_get_option_as_int')) {
    /**
     * 获取一个 option 的整数值。如果值不能被转化为整数则返回 0。
     *
     * @param string $key
     * @param mixed  $default
     * @return int
     */
    function report_get_option_as_int($key, $default = null) {
        $value = filter_var(option($key, $default), FILTER_VALIDATE_INT);
        return $value === false ? 0 : $value;
    }
}
