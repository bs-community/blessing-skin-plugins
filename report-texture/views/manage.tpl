@extends('admin.master')

@section('title', trans('ReportTexture::general.menu.admin'))

@section('content')

<style>td a i { margin-left: 3px; }</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      {{ trans('ReportTexture::general.menu.admin') }}
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="box">
      <div class="box-body table-bordered">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>{{ trans('ReportTexture::general.thead.tid') }}</th>
              <th>{{ trans('ReportTexture::general.thead.uploader') }}</th>
              <th>{{ trans('ReportTexture::general.thead.reporter') }}</th>
              <th>{{ trans('ReportTexture::general.thead.reason') }}</th>
              <th>{{ trans('ReportTexture::general.thead.status') }}</th>
              <th>{{ trans('ReportTexture::general.thead.time') }}</th>
              <th>{{ trans('ReportTexture::general.thead.operations') }}
                <i class="fa fa-question-circle" title="{{ trans('ReportTexture::general.thead.more') }}" data-toggle="tooltip" data-placement="left"></i>
              </th>
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
                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('ReportTexture::general.operations.title') }}  <span class="caret"></span></button>
                    <ul class="dropdown-menu">
                      <li><a href="javascript:;" onclick="moderation.ban({{ $report->id }});">{{ trans('ReportTexture::general.operations.ban') }}</a></li>
                      <li><a href="javascript:;" onclick="moderation.private({{ $report->id }});">{{ trans('ReportTexture::general.operations.private') }}</a></li>
                      <li><a href="javascript:;" onclick="moderation.delete({{ $report->id }});">{{ trans('ReportTexture::general.operations.delete') }}</a></li>
                      <li><a href="javascript:;" onclick="moderation.reject({{ $report->id }});">{{ trans('ReportTexture::general.operations.reject') }}</a></li>
                    </ul>
                  </div>
                </td>
              </tr>
            @empty
              <p>{{ trans('ReportTexture::general.empty.admin') }}</p>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script src="{{ plugin_assets('report-texture', 'assets/dist/moderation.js') }}"></script>
@endsection
