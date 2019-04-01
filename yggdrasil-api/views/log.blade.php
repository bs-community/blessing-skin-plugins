@extends('admin.master')

@section('title', 'Yggdrasil 日志')

@section('content')

<link href="https://cdn.bootcss.com/datatables/1.10.16/css/dataTables.bootstrap.min.css" rel="stylesheet">

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      外置登录系统 Yggdrasil API 日志
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="box">
      <div class="box-body table-bordered">
        <table id="ygg-log-table" class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>动作</th>
              <th>用户邮箱</th>
              <th>角色名</th>
              <th>附加参数</th>
              <th>IP</th>
              <th>时间</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script src="https://cdn.bootcss.com/datatables/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.bootcss.com/datatables/1.10.16/js/dataTables.bootstrap.min.js"></script>
<script src="{{ plugin_assets('yggdrasil-api', 'dist/log.js') }}"></script>
@endsection
