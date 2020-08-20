@extends('admin.master')

@section('title', '七牛云存储配置')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      七牛云存储配置
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <div class="box">
          <div class="box-header with-border">
            <h3 class="box-title">七牛云存储连接配置</h3>
          </div><!-- /.box-header -->
          <div class="box-body table-responsive">
            @php
              try {
                Storage::disk('textures')->put('connectivity_test', 'test');
                Storage::disk('textures')->delete('connectivity_test');

                echo '<div class="callout callout-success">成功连接至七牛云存储</div>';
              } catch (Exception $e) {
                echo '<div class="callout callout-danger">无法连接至七牛云存储，请检查你的配置。<br>错误信息：'.$e->getMessage().'</div>';
              }
            @endphp
          </div>
        </div>
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
