@extends('admin.master')

@section('title', 'Redis')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Redis
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <?php
          $redis = Option::form('redis', 'Redis 连接配置', function($form) {
            $config = config('database.redis.default');
            $form->text('REDIS_HOST', 'REDIS_HOST')->value($config['host'])->disabled();
            $form->text('REDIS_PASSWORD', 'REDIS_PASSWORD')->value($config['password'])->disabled();
            $form->text('REDIS_PORT', 'REDIS_PORT')->value($config['port'])->disabled();
          })->addMessage('要启用 Redis 缓存，请在 <code>.env</code> 文件中配置好下面三项')
          ->always(function($form) {
            try {
              if (Predis::connection()->ping()) {
                $server = "tcp://".config('database.redis.default.host').":".config('database.redis.default.port');
                $form->addMessage("成功连接至 Redis 服务器：[$server]", 'success');
              }
            } catch (Exception $e) {
              $msg = iconv('gbk', 'utf-8', $e->getMessage());
              $form->addMessage("无法连接至 Redis 服务器：{$msg}", 'danger');
            }
          })->renderWithOutSubmitButton();

        ?>
        {!! $redis->render() !!}
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
