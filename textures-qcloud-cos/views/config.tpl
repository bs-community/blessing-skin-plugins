@extends('admin.master')

@section('title', '腾讯云 COS 配置')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      腾讯云 COS 配置
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        
        <?php
          $redis = Option::form('redis', 'Redis 连接配置', function($form) {
            $instruction = <<<EOT
请在皮肤站 .env 配置文件中添加以下条目：

COS_APP_ID=
COS_SECRET_ID=
COS_SECRET_KEY=
COS_TIMEOUT=60
COS_CONNECT_TIMEOUT=60
COS_BUCKET=
COS_REGION=ap-guangzhou
COS_CDN=
COS_SCHEME=https

各配置项的填写请参阅：
https://github.com/freyo/flysystem-qcloud-cos-v5#region
EOT;
            $form->textarea('example_plugin_textarea', '配置说明')->rows(15)->value($instruction)->disabled();
          })->always(function($form) {
            try {
              Storage::disk('textures')->put('connectivity_test', 'test');
              Storage::disk('textures')->delete('connectivity_test');

              $form->addMessage('成功连接至腾讯云 COS', 'success');
            } catch (Exception $e) {
              $msg = iconv('gbk', 'utf-8', $e->getMessage());
              $form->addMessage("无法连接至腾讯云 COS，请检查你的配置：{$msg}", 'danger');
            }
          })->renderWithOutSubmitButton();

        ?>
        {!! $redis->render() !!}
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
