@extends('admin.master')

@section('title', 'CDN 配置 - '.trans('general.plugin-manage'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>静态文件 CDN 配置</h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <?php

        $form = Option::form('cdn', 'CDN 配置', function($form)
        {
          $form->text('cdn_address', 'CDN 地址')->hint('不要以 / 结尾')->description('填写的 CDN 地址必须是 <code>/resources/assets</code> 目录的镜像，所有此目录下的文件都会从 CDN 加载。<br><b>测试方法：</b>检测 <code>http://your-domain.com/dist/app.min.js</code> 是否能够访问。设置无法访问的 CDN 造成的问题本插件<b>概不负责</b>（如果真的手贱了在 plugins 目录下删除本插件即可）。');
        })->handle(function () {
            if (substr($_POST['cdn_address'], -1) == "/")
                $_POST['cdn_address'] = substr($_POST['cdn_address'], 0, -1);
        });

        ?>

        {!! $form->render() !!}
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
