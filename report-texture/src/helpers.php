<?php

use ReportTexture\Report;

function report_status($code) {
    switch ($code) {
        case Report::STATUS_PENDING:
            return trans('ReportTexture::config.status_pending');
            break;
        case Report::STATUS_RESOLVED:
            return trans('ReportTexture::config.status_resolved');
            break;
        case Report::STATUS_REJECTED:
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
