@extends('admin.master')

@section('title', '批量导入')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>批量导入</h1>
  </section>
  <!-- Main content -->
  <section class="content">
    @yield('step-content')
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
