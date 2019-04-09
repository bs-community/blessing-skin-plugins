@extends('admin.master')

@section('title', 'Yggdrasil 日志')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      外置登录系统 Yggdrasil API 日志
    </h1>
  </section>

  <!-- Main content -->
  <section class="content"></section>
</div><!-- /.content-wrapper -->
@endsection

@section('script')
<script src="{{ plugin_assets('yggdrasil-api', 'dist/log.js') }}"></script>
@endsection
