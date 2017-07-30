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
            return json('你已经举报过该材质了，请耐心等待管理员处理，你可以在用户中心查看举报的处理进度', 1);
        }

        $report = new Report;

        $report->tid = $tid;
        $report->reason = $request->get('reason');
        $report->uploader = Texture::find($report->tid)->uploader;
        $report->reporter = $reporter;
        $report->status = REPORT_STATUS_PENDING;
        $report->report_at = Utils::getTimeFormatted();

        $report->save();

        return json('举报已提交，请等待管理员处理', 0);
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
            return json('不存在该举报记录');
        }

        switch ($request->get('operation')) {
            case 'ban':
                User::find($report->uploader)->setPermission(User::BANNED);
                $report->update(['status' => REPORT_STATUS_RESOLVED]);

                return json('被举报的上传者已被封禁', 0);
                break;

            case 'delete':
                Texture::find($report->tid)->delete();
                $report->update(['status' => REPORT_STATUS_RESOLVED]);

                return json('被举报的材质已被删除', 0);
                break;

            case 'reject':
                $report->update(['status' => REPORT_STATUS_REJECTED]);

                return json('已拒绝该举报', 0);
                break;

            default:
                # code...
                break;
        }
    }
}
