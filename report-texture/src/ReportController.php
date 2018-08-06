<?php

namespace ReportTexture;

use Utils;
use App\Models\User;
use App\Models\Texture;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function report(Request $request)
    {
        $this->validate($request, [
            'tid' => 'required',
            'reason' => 'required'
        ]);

        $tid = $request->get('tid');
        $reporter = app('user.current')->uid;

        if (Report::where('reporter', $reporter)->where('tid', $tid)->first()) {
            return json(trans('ReportTexture::general.report.duplicate'), 1);
        }

        $report = new Report;
        $report->tid = $tid;
        $report->reason = $request->get('reason');
        $report->uploader = Texture::find($report->tid)->uploader;
        $report->reporter = $reporter;
        $report->status = Report::STATUS_PENDING;
        $report->report_at = Utils::getTimeFormatted();
        $report->save();

        return json(trans('ReportTexture::general.report.success'), 0);
    }

    public function showMyReports()
    {
        $user = app('user.current');
        $reports = Report::where('reporter', $user->uid)->get();

        return view('ReportTexture::report', compact('user', 'reports'));
    }

    public function showReportsManage()
    {
        $user = app('user.current');
        // 懒得做分页了，有缘再说
        $reports = Report::all();

        return view('ReportTexture::manage', compact('user', 'reports'));
    }

    public function handleReports(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'operation' => 'required'
        ]);

        $report = Report::find($request->get('id'));

        if (! $report) {
            return json(trans('general.illegal-parameters'));
        }

        switch ($request->get('operation')) {
            case 'ban':
                $uploader = User::find($report->uploader);

                // 检查权限
                if (app('user.current')->permission > $uploader->permission) {
                    // 封禁用户
                    User::find($report->uploader)->setPermission(User::BANNED);
                    $report->update(['status' => Report::STATUS_RESOLVED]);

                    return json(trans('ReportTexture::general.moderation.banned'), 0);
                } else {
                    return json(trans('ReportTexture::general.moderation.permission-denied.ban'), 1);
                }

                break;

            case 'delete':

                if (app('user.current')->permission > User::find($report->uploader)->permission) {
                    Texture::find($report->tid)->delete();
                    $report->update(['status' => Report::STATUS_RESOLVED]);

                    return json(trans('ReportTexture::general.moderation.deleted'), 0);
                } else {
                    return json(trans('ReportTexture::general.moderation.permission-denied.delete'), 1);
                }

                break;

            case 'reject':
                $report->update(['status' => Report::STATUS_REJECTED]);

                return json(trans('ReportTexture::general.moderation.rejected'), 0);
                break;

            default:
                # code...
                break;
        }
    }
}
