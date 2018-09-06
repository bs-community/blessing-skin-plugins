@extends('admin.master')

@section('title', '阿里云 OSS 配置')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      阿里云 OSS 配置
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        @php
            $instruction = <<<EOT
请在皮肤站的 `.env` 配置文件中添加并填写以下条目：

```
OSS_ACCESS_ID=
OSS_ACCESS_KEY=
OSS_BUCKET=
# 外网节点或自定义外部域名
OSS_ENDPOINT=oss-cn-hangzhou.aliyuncs.com
OSS_SSL=true

# 本插件对 OSS 自定义 CDN 域名支持仍不完善，不建议开启
# OSS_CDN_DOMAIN=cdn.example.com
# 是否使用自定义域名
# OSS_IS_CNAME=false
```
EOT;
        @endphp
        <div class="box">
          <div class="box-header with-border">
            <h3 class="box-title">OSS 连接配置</h3>
          </div><!-- /.box-header -->
          <div class="box-body table-responsive">
            @php
              try {
                Storage::disk('textures')->put('connectivity_test', 'test');
                Storage::disk('textures')->delete('connectivity_test');

                echo '<div class="callout callout-success">成功连接至阿里云 OSS</div>';
              } catch (Exception $e) {
                echo '<div class="callout callout-danger">无法连接至阿里云 OSS，请检查你的配置。<br>错误信息：'.$e->getMessage().'</div>';
              }

              echo (new Parsedown())->text($instruction);
            @endphp
          </div>
        </div>
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
