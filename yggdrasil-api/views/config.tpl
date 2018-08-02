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
      $form->select('ygg_uuid_algorithm', 'UUID 生成算法')
        ->option('v3', 'Version 3: 与原盗版用户 UUID 一致【推荐】')
        ->option('v4', 'Version 4: 随机生成【想要同时兼容盗版登录的不要选】')
        ->hint('选择 Version 3 以获得对原盗版服务器的最佳兼容性。');
      $form->text('ygg_token_expire_1', '令牌暂时失效时间');
      $form->text('ygg_token_expire_2', '令牌完全失效时间')->description('分别指定 Token【暂时失效】与【完全失效】的过期时间（技术细节请参阅 http://t.cn/RHKshKe），单位为秒');
      $form->text('ygg_rate_limit', '登录/登出频率限制')->hint('两次操作之间的时间间隔（毫秒）');
      $form->text('ygg_skin_domain', '额外皮肤白名单域名')->description('只有在此列表中的材质才能被加载。【本站地址】和【当前访问地址】已经默认添加至白名单列表，需要添加的额外白名单域名请使用半角逗号 (,) 分隔');
      $form->text('ygg_search_profile_max', '批量查询角色数量限制')->hint('一次请求中最多能查询几个角色');
      $form->checkbox('ygg_show_config_section', '显示快速配置板块')->label('在用户中心首页显示「快速配置启动器」板块');
      $form->checkbox('ygg_show_activities_section', '显示最近活动板块')->label('在用户中心首页显示「最近活动」板块');
    })->handle();

    $keypairForm = Option::form('keypair', '密钥对配置', function($form) {
      $form->textarea('ygg_private_key', 'OpenSSL 私钥')->rows(10)->hint('只需填写 PEM 格式的私钥即可，公钥会根据私钥自动生成。');
    })->renderWithOutSubmitButton()->addButton([
      'style' => 'success',
      'name' => 'generate-key',
      'text' => '帮我生成一个私钥'
    ])->addButton([
      'style' => 'primary',
      'type' => 'submit',
      'name' => 'submit-key',
      'class' => 'pull-right',
      'text' => '保存私钥'
    ])->addMessage('使用下方的按钮来自动生成符合格式的私钥。<br>如需自定义用于签名的私钥，请参阅 <a href="https://github.com/yushijinhun/authlib-injector/wiki/%E7%AD%BE%E5%90%8D%E5%AF%86%E9%92%A5%E5%AF%B9">Wiki - 签名密钥对</a>。')->handle();

    if (! openssl_pkey_get_private(option('ygg_private_key'))) {
      $keypairForm->addMessage('无效的私钥，请检查后重新配置。', 'danger');
    } else {
      $keypairForm->addMessage('私钥有效。', 'success');
    }
  ?>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        {!! $commonForm->render() !!}

        <div class="box box-default">
          <div class="box-header with-border">
            <h3 class="box-title">导入服务器的【角色名 ⇆ UUID】映射表</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            <div class="callout callout-warning">
              一般情况下，你只需要将 UUID 生成算法设置为 <code>Version 3</code> 即可实现对盗版／登录插件的兼容。
              如果你知道你在做什么，那么请参考<a href="https://github.com/bs-community/yggdrasil-api/wiki/0x05-%E4%BB%8E%E7%99%BB%E5%BD%95%E6%8F%92%E4%BB%B6%E8%BF%81%E7%A7%BB%E8%87%B3%E6%9C%AC%E6%96%B9%E6%A1%88#-%E9%AB%98%E7%BA%A7%E5%8A%9F%E8%83%BD-%E4%B8%80%E9%94%AE%E5%AF%BC%E5%85%A5-uuid">「Wiki - 0x05 从登录插件迁移至本方案 - [高级功能] 一键导入 UUID」</a>谨慎使用。
            </div>

            <input id="usercache-json-file" type="file" accept="application/json" />
          </div><!-- /.box-body -->
        </div><!-- /.box -->
      </div>

      <div class="col-md-6">
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-star"></i> API Root</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            <p>本站的 Yggdrasil API 地址：<code>{!! url('api/yggdrasil') !!}</code></p>
            <p>请确认以上 URL 能够正常访问后再 <a href="https://github.com/bs-community/yggdrasil-api/wiki/0x03-%E9%85%8D%E5%90%88-authlib-injector-%E4%BD%BF%E7%94%A8">进行 authlib-injector 的配置</a>。</p>
          </div><!-- /.box-body -->
        </div><!-- /.box -->

        {!! $keypairForm->render() !!}

        <div class="box box-default">
          <div class="box-header with-border">
            <h3 class="box-title">本站已保存的映射表情况</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            <p>目前本站已存储了 {{ DB::table('uuid')->count() }} 条【角色名 ⇆ UUID】的映射，你可以去本站数据库中的 <code>{{ DB::getTablePrefix().'uuid' }}</code> 表进行管理。</p>
          </div><!-- /.box-body -->
        </div><!-- /.box -->
      </div>
    </div>
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script src="{{ plugin_assets('yggdrasil-api', 'assets/dist/config.js') }}"></script>
@endsection
