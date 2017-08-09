@extends('user.master')

@section('title', '我的举报')

@section('content')

<style>td a i { margin-left: 3px; }</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            我的举报
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
                            <th>原因</th>
                            <th>状态</th>
                            <th>举报时间</th>
                            {{-- <th>操作</th> --}}
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>
                                    {{ $report->tid }}
                                    <a href="{{ url('skinlib/show/'.$report->tid) }}">
                                        <i class="fa fa-share-square-o" aria-hidden="true"></i>
                                    </a>
                                </td>
                                <td>
                                    {{ report_uid_to_nickname($report->uploader) }}
                                    <a href="{{ url('skinlib?filter=skin&uploader='.$report->uploader) }}">
                                        <i class="fa fa-share-square-o" aria-hidden="true"></i>
                                    </a>
                                </td>
                                <td>{{ $report->reason }}</td>
                                <td>{{ report_status($report->status) }}</td>
                                <td>{{ $report->report_at }}</td>
                                {{-- <td>
                                    <a class="btn btn-sm btn-warning" href="javascript:cancelReport({{ $report->id }})">取消举报</a>
                                </td> --}}
                            </tr>
                        @empty
                            <p>你还没有提交任何举报哦</p>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
