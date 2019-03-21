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
            $instruction = <<<EOT
**注意：本插件仅支持 COS v5，如你正在使用旧版本的 COS，请联系腾讯云客服升级版本。**

请在皮肤站的 `.env` 配置文件中添加并填写以下条目：

```
COS_APP_ID=
COS_SECRET_ID=
COS_SECRET_KEY=
COS_TIMEOUT=60
COS_CONNECT_TIMEOUT=60
COS_BUCKET=
COS_REGION=ap-shanghai
COS_CDN=
COS_READ_FROM_CDN=true
COS_SCHEME=https
```

其中可用地域 `COS_REGION` 的填写请参考 [这里](https://github.com/freyo/flysystem-qcloud-cos-v5#region)，
CDN 地址 `COS_CDN` 必须以 `http(s)://` 开头。

EOT;
        ?>
        <div class="box">
          <div class="box-header with-border">
            <h3 class="box-title">COS 连接配置</h3>
          </div><!-- /.box-header -->
          <div class="box-body table-responsive">
            @php
              try {
                Storage::disk('textures')->put('connectivity_test', 'test');
                Storage::disk('textures')->delete('connectivity_test');

                echo '<div class="callout callout-success">成功连接至腾讯云 COS</div>';
              } catch (Exception $e) {
                echo '<div class="callout callout-danger">无法连接至腾讯云 COS，请检查你的配置。<br>错误信息：'.$e->getMessage().'</div>';
              }

              echo app('parsedown')->text($instruction);
            @endphp
          </div>
        </div>
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
