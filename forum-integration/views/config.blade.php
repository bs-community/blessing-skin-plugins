@extends('admin.master')

@section('title', '论坛数据对接配置')

@section('content')

<?php
  $targetDbConfigForm = Option::form('connection', '数据库连接配置', function ($form) {

    $form->text('forum_db_host',     '目标数据库地址')->hint('跨数据库主机进行对接可能会有延迟，敬请知悉。');
    $form->text('forum_db_port',     '端口');
    $form->text('forum_db_database', '数据库名');
    $form->text('forum_db_username', '用户名');
    $form->text('forum_db_password', '密码');
    $form->text('forum_db_table',    '用户数据表名');

    $form->select('forum_duplicated_prefer', '重复处理')
      ->option('remote', '用论坛程序上的用户数据覆盖皮肤站')
      ->option('local',  '用皮肤站上的用户数据覆盖论坛程序')
      ->description('此项选择后，在用户数据（如用户名相同、用户名密码不同）冲突的情况下将以你选择的那一方为准，另一方的用户数据将被覆盖');

  })->handle()->always(function ($form) {
    $config = [
        'host' => option('forum_db_host', '127.0.0.1'),
        'port' => option('forum_db_port', 3306),
        'database' => option('forum_db_database', 'forum'),
        'username' => option('forum_db_username', 'default'),
        'password' => option('forum_db_password', 'secret'),
        'table' => option('forum_db_table', 'users'),
    ];

    config(['database.connections.remote' => array_merge(
      forum_get_default_db_config(), $config
    )]);

    try {
      DB::connection('remote')->getPdo();

      if (Schema::connection('remote')->hasTable($config['table'])) {
        $form->addMessage('目标数据库连接正常。', 'success');
      } else {
        $form->addMessage("成功连接至目标数据库，但是指定的数据表 [{$config['table']}] 不存在。", 'warning');
      }
    } catch (Exception $e) {
      $form->addMessage('无法连接至 MySQL 服务器，请检查你的配置。<br>错误信息：'.$e->getMessage(), 'danger');
    }
  });
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      论坛数据对接配置
    </h1>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        {!! $targetDbConfigForm->render() !!}
      </div>
      <div class="col-md-6">
        <div class="box">
          <div class="box-header with-border">
            <h3 class="box-title">说明</h3>
          </div><!-- /.box-header -->
          <div class="box-body table-responsive">
            {!! (new Parsedown())->text($instruction) !!}
          </div>
        </div>
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

