@extends('admin.master')

@section('title', '举报管理')

@section('content')

<style>td a i { margin-left: 3px; }</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            举报管理
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-body table-bordered">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>材质 ID</th>
                            <th>上传者</th>
                            <th>举报人</th>
                            <th>原因</th>
                            <th>状态</th>
                            <th>举报时间</th>
                            <th data-placement="left" title="更多操作请进入用户管理或皮肤库中进行">操作</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($reports as $report)
                            <tr id="report-{{ $report->id }}">
                                <td>
                                    {{ $report->tid }}
                                    <a href="{{ url('skinlib/show/'.$report->tid) }}">
                                        <i class="fa fa-share-square-o" aria-hidden="true"></i>
                                    </a>
                                </td>
                                <td>
                                    {{ report_uid_to_nickname($report->uploader) }}
                                    (UID: {{ $report->uploader }})
                                    <a href="{{ url('admin/users?uid='.$report->uploader) }}">
                                        <i class="fa fa-share-square-o" aria-hidden="true"></i>
                                    </a>
                                </td>
                                <td>
                                    {{ report_uid_to_nickname($report->reporter) }}
                                    (UID: {{ $report->reporter }})
                                    <a href="{{ url('admin/users?uid='.$report->reporter) }}">
                                        <i class="fa fa-share-square-o" aria-hidden="true"></i>
                                    </a>
                                </td>
                                <td>{{ $report->reason }}</td>
                                <td id="status">{{ report_status($report->status) }}</td>
                                <td>{{ $report->report_at }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">处理方式  <span class="caret"></span></button>
                                        <ul class="dropdown-menu">
                                            <li><a href="javascript:report.ban({{ $report->id }});">封禁上传者</a></li>
                                            <li><a href="javascript:report.delete({{ $report->id }});">删除该材质</a></li>
                                            <li><a href="javascript:report.reject({{ $report->id }});">拒绝该举报</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <p>未收到任何举报</p>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
    <script>$('thead tr th:last-child').tooltip('show');</script>
@endsection
