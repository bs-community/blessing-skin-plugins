<?php

define('REPORT_STATUS_PENDING', 0);
define('REPORT_STATUS_RESOLVED', 1);
define('REPORT_STATUS_REJECTED', 2);

function report_status($code) {
    switch ($code) {
        case REPORT_STATUS_PENDING:
            return "正在处理";
            break;

        case REPORT_STATUS_RESOLVED:
            return "处理完毕";
            break;

        case REPORT_STATUS_REJECTED:
            return "已被拒绝";
            break;

        default:
            return null;
            break;
    }
}

function report_uid_to_nickname($uid) {
    $user = App\Models\User::find($uid);

    if ($user) {
        return $user->getNickName();
    } else {
        return "不存在的用户";
    }
}
