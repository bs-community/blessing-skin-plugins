<?php

namespace Blessing\Report;

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
            return json(trans('Blessing\Report::config.reported'), 1);
        }

        $report = new Report;

        $report->tid = $tid;
        $report->reason = $request->get('reason');
        $report->uploader = Texture::find($report->tid)->uploader;
        $report->reporter = $reporter;
        $report->status = REPORT_STATUS_PENDING;
        $report->report_at = Utils::getTimeFormatted();

        $report->save();

        return json(trans('Blessing\Report::config.submitted_report'), 0);
    }

    public function showMyReports()
    {
        $user = app('user.current');
        $reports = Report::where('reporter', $user->uid)->get();

        return view('Blessing\Report::report')->with('user', $user)->with('reports', $reports);
    }

    public function showReportsManage()
    {
        $user = app('user.current');
        // 懒得做分页了，有缘再说
        $reports = Report::all();

        return view('Blessing\Report::manage')->with('user', $user)->with('reports', $reports);
    }

    public function handleReports(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'operation' => 'required'
        ]);

        $report = Report::find($request->get('id'));

        if (! $report) {
            return json(trans('Blessing\Report::config.nonexistent_report'));
        }

        switch ($request->get('operation')) {
            case 'ban':
                $uploader = User::find($report->uploader);

                if (app('user.current')->permission > $uploader->permission) {
                    User::find($report->uploader)->setPermission(User::BANNED);
                    $report->update(['status' => REPORT_STATUS_RESOLVED]);

                    return json(trans('Blessing\Report::config.blocked'), 0);
                } else {
                    return json(trans('Blessing\Report::config.permission_denied_user'), 1);
                }

                break;

            case 'delete':

                if (app('user.current')->permission > User::find($report->uploader)->permission) {
                    Texture::find($report->tid)->delete();
                    $report->update(['status' => REPORT_STATUS_RESOLVED]);

                    return json(trans('Blessing\Report::config.texture_deleted'), 0);
                } else {
                    return json(trans('Blessing\Report::config.permission_denied_texture'), 1);
                }

                break;

            case 'reject':
                $report->update(['status' => REPORT_STATUS_REJECTED]);

                return json(trans('Blessing\Report::config.rejected'), 0);
                break;

            default:
                # code...
                break;
        }
    }
}
