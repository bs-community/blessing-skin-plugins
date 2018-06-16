@extends('user.master')

@section('title', trans('Blessing\Report::config.title'))

@section('content')

<style>td a i { margin-left: 3px; }</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{trans('Blessing\Report::config.user_reports')}}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-body table-bordered">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{trans('Blessing\Report::config.texture_id')}}</th>
                            <th>{{trans('Blessing\Report::config.reporter')}}</th>
                            <th>{{trans('Blessing\Report::config.reason')}}</th>
                            <th>{{trans('Blessing\Report::config.status')}}</th>
                            <th>{{trans('Blessing\Report::config.time')}}</th>
                            {{-- <th>trans('Blessing\Report::config.action')}}</th> --}}
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
                                    <a class="btn btn-sm btn-warning" href="javascript:cancelReport({{ $report->id }})">{{trans('Blessing\Report::config.user_cancel')}}</a>
                                </td> --}}
                            </tr>
                        @empty
                            <p>{{trans('Blessing\Report::config.user_non_report')}}</p>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
