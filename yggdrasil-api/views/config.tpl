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
      $form->select('uuid_algorithm', 'UUID 生成算法')
        ->option('v3', 'Version 3: 与原盗版用户 UUID 一致【推荐】')
        ->option('v4', 'Version 4: 随机生成【想要同时兼容盗版登录的不要选】')
        ->hint('选择 Version 3 以获得对原盗版服务器的最佳兼容性。');
      $form->text('ygg_token_expire_1', '令牌暂时失效时间');
      $form->text('ygg_token_expire_2', '令牌完全失效时间')->description('分别指定 Token【暂时失效】与【完全失效】的过期时间（技术细节请参阅 http://t.cn/RHKshKe），单位为秒');
      $form->text('ygg_rate_limit', '登录/登出频率限制')->hint('两次操作之间的时间间隔（毫秒）');
      $form->text('ygg_skin_domain', '额外皮肤白名单域名')->description('只有在此列表中的材质才能被加载。【本站地址】和【当前访问地址】已经默认添加至白名单列表，需要添加的额外白名单域名请使用半角逗号 (,) 分隔');
      $form->text('ygg_search_profile_max', '批量查询角色数量限制')->hint('一次请求中最多能查询几个角色');
      $form->checkbox('ygg_verbose_log', '日志记录')->label('记录详细的访问记录（仅供调试用）');
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
    ])->addMessage('使用下方的按钮来自动生成符合格式的私钥。<br>如需自定义用于签名的私钥，请参阅 <a href="http://t.cn/RHKr8aB">Wiki - 签名密钥对</a>。')->handle();

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
            <div class="callout callout-info">
              如果你正在将现有的 Minecraft 服务器迁移至使用本外置登录方案，请在这里导入你服务器的 `usercache.json` 文件，以防止出现 UUID 冲突的问题。
              如果你不知道这个怎么用，请仔细阅读 <a href="http://t.cn/RrAK4F8">「Wiki - 0x05 从登录插件迁移至本方案」</a>。
            </div>

            <input id="usercache-json-file" type="file" accept="application/json" />
          </div><!-- /.box-body -->
        </div><!-- /.box -->
      </div>

      <div class="col-md-6">
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
<script src="{{ plugin_assets('yggdrasil-api', 'assets/config.js') }}"></script>
@endsection
