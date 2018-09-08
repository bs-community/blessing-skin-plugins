@extends('admin.master')

@section('title', '生成 Profile 文件缓存')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      生成 Profile 文件缓存
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">

    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">使用方法</h3>
      </div><!-- /.box-header -->
      <div class="box-body table-responsive">
        <?php
          $path = plugin('insane-profile-cache')->getPath()."/README.md";
          $markdown = @file_get_contents($path);

          if (!$markdown) {
            echo "<p>无法加载 README.md</p>";
          } else {
            echo app('parsedown')->text($markdown);
          }
        ?>
      </div>
      <div class="box-footer">
        <a href="?continue" class="btn btn-primary">生成文件缓存</a>
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
