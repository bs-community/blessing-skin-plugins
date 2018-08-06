@extends('admin.master')

@section('title', trans('ReportTexture::config.title'))

@section('content')

<style>td a i { margin-left: 3px; }</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
       {{ trans('ReportTexture::config.title') }}
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="box">
      <div class="box-body table-bordered">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>{{trans('ReportTexture::config.texture_id')}}</th>
              <th>{{trans('ReportTexture::config.uploader')}}</th>
              <th>{{trans('ReportTexture::config.reporter')}}</th>
              <th>{{trans('ReportTexture::config.reason')}}</th>
              <th>{{trans('ReportTexture::config.status')}}</th>
              <th>{{trans('ReportTexture::config.time')}}</th>
              <th data-placement="left" title="{{trans('ReportTexture::config.details')}}">{{trans('ReportTexture::config.operations')}}</th>
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
                    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('ReportTexture::config.treatment') }}  <span class="caret"></span></button>
                    <ul class="dropdown-menu">
                      <li><a href="javascript:;" onclick="moderation.ban({{ $report->id }});">{{trans('ReportTexture::config.block')}}</a></li>
                      <li><a href="javascript:;" onclick="moderation.delete({{ $report->id }});">{{trans('ReportTexture::config.delete')}}</a></li>
                      <li><a href="javascript:;" onclick="moderation.reject({{ $report->id }});">{{trans('ReportTexture::config.reject')}}</a></li>
                    </ul>
                  </div>
                </td>
              </tr>
            @empty
              <p>{{trans('ReportTexture::config.none')}}</p>
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
<script src="{{ plugin_assets('report-texture', 'assets/dist/moderation.js') }}"></script>
@endsection
