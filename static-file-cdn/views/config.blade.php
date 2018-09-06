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
        @php

        $form = Option::form('cdn', 'CDN 配置', function ($form) {
          $form->text('cdn_address', 'CDN 地址')->hint('不要以 / 结尾')->description(
            '填写的 CDN 地址必须是 <code>/public</code> 目录的镜像，此目录下的所有文件都将会从 CDN 加载。<br>'.
            '<b>测试方法</b>：检查 <code>{你应该填写的地址}/index.js</code> 是否能够访问。设置无法访问的 CDN 造成的问题本插件<b>概不负责</b>（如果真的手贱了在 plugins 目录下删除本插件即可）。<br>'.
            '你可以在 <a href="https://github.com/printempw/blessing-skin-server/wiki/%E7%9A%AE%E8%82%A4%E7%AB%99-CDN-%E9%85%8D%E7%BD%AE">这里</a> 查看可用的公共 CDN。'
          );
        })->handle(function () {
            if (substr($_POST['cdn_address'], -1) == "/")
                $_POST['cdn_address'] = substr($_POST['cdn_address'], 0, -1);
        });

        @endphp

        {!! $form->render() !!}
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
