@extends('admin.master')

@section('title', 'Redis')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Redis 连接配置
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">README.md</h3>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
              <?php
                $config = config('database.redis.default');
                $server = array_get($config, 'scheme') == 'unix' ? "unix:{$config['path']}" : "tcp://{$config['host']}:{$config['port']}";

                try {
                  Predis::connection()->ping();

                  echo '<div class="callout callout-success">成功连接至 Redis 服务器：['.$server.']</div>';
                } catch (Exception $e) {
                  $msg = iconv('gbk', 'utf-8', $e->getMessage());
                  echo '<div class="callout callout-danger">无法连接至 Redis 服务器：['.$server.']<br>错误信息：'.$msg.'</div>';
                }

                $markdown = @file_get_contents(plugin('redis')->getPath().'/README.md');

                if (! $markdown) {
                  echo "<p>无法加载插件根目录下的 README.md</p>";
                } else {
                  echo app('parsedown')->text(mb_substr($markdown, 8));
                }
              ?>
            </div>
          </div>
        </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
