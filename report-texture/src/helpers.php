<?php

use ReportTexture\Report;

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

function report_uid_to_nickname($uid) {
    $user = App\Models\User::find($uid);
    if ($user) {
        return $user->getNickName();
    } else {
        return trans('general.unexistent-user');
    }
}
