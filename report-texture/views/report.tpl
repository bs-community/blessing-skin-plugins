@extends('user.master')

@section('title', trans('ReportTexture::general.menu.user'))

@section('content')

<style>td a i { margin-left: 3px; }</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      {{trans('ReportTexture::general.menu.user')}}
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="box">
      <div class="box-body table-bordered">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>{{trans('ReportTexture::general.thead.texture_id')}}</th>
              <th>{{trans('ReportTexture::general.thead.reporter')}}</th>
              <th>{{trans('ReportTexture::general.thead.reason')}}</th>
              <th>{{trans('ReportTexture::general.thead.status')}}</th>
              <th>{{trans('ReportTexture::general.thead.time')}}</th>
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
              </tr>
            @empty
              <p>{{trans('ReportTexture::general.empty.user')}}</p>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
