<?php

define('REPORT_STATUS_PENDING', 0);
define('REPORT_STATUS_RESOLVED', 1);
define('REPORT_STATUS_REJECTED', 2);

function report_status($code) {
    switch ($code) {
        case REPORT_STATUS_PENDING:
            return trans('ReportTexture::config.status_pending');
            break;
        case REPORT_STATUS_RESOLVED:
            return trans('ReportTexture::config.status_resolved');
            break;
        case REPORT_STATUS_REJECTED:
            return trans('ReportTexture::config.status_rejected');
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
        return trans('ReportTexture::config.nonexistent_user');
    }
}
