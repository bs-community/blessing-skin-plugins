@extends('admin.master')

@section('title', 'Yggdrasil API 插件配置页')

@section('content')

<style> textarea { font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace; } </style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Yggdrasil API
    </h1>
  </section>

  <?php
    $commonForm = Option::form('common', '常规配置', function($form) {
      $form->text('ygg_token_expire_1', '令牌暂时失效时间');
      $form->text('ygg_token_expire_2', '令牌完全失效时间')->description('分别指定 Token【暂时失效】与【完全失效】的过期时间（技术细节请参阅 http://t.cn/RHKshKe），单位为秒');
      $form->text('ygg_rate_limit', '登录/登出频率限制')->hint('两次操作之间的时间间隔（秒）');
      $form->text('ygg_skin_domain', '额外皮肤白名单域名')->description('只有在此列表中的材质才能被加载。【本站地址】和【当前访问地址】已经默认添加至白名单列表，需要添加的额外白名单域名请使用半角逗号 (,) 分隔');
      $form->text('ygg_search_profile_max', '批量查询角色数量限制')->hint('一次请求中最多能查询几个角色');
      $form->checkbox('ygg_verbose_log', '日志记录')->label('记录详细的访问记录（仅供调试用）');
    })->handle();

    $keypairForm = Option::form('keypair', '密钥对配置', function($form) {
      $form->textarea('ygg_private_key', 'OpenSSL 私钥')->rows(10);
    })->addMessage('请填写 PEM 格式的私钥，公钥将会根据私钥自动生成。<br>如何生成 RSA 密钥对请参阅 http://t.cn/RHKr8aB，蟹蟹。')->handle();

    if (! openssl_pkey_get_private(option('ygg_private_key'))) {
      $keypairForm->addMessage('无效的 RSA 私钥，请检查后重新配置。', 'danger');
    } else {
      $keypairForm->addMessage('RSA 私钥有效。', 'success');
    }
  ?>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        {!! $commonForm->render() !!}
      </div>

      <div class="col-md-6">
        {!! $keypairForm->render() !!}
      </div>
    </div>
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
