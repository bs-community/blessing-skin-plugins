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
      </div>

      <div class="col-md-6">
        {!! $keypairForm->render() !!}
      </div>
    </div>
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script type="text/javascript">
  'use strict';function _asyncToGenerator(a){return function(){var b=a.apply(this,arguments);return new Promise(function(c,d){function e(f,g){try{var h=b[f](g),i=h.value}catch(j){return void d(j)}return h.done?void c(i):Promise.resolve(i).then(function(j){e('next',j)},function(j){e('throw',j)})}return e('next')})}}$('[name=generate-key]').click(_asyncToGenerator(regeneratorRuntime.mark(function a(){var b,c,d,e;return regeneratorRuntime.wrap(function(g){for(;;)switch(g.prev=g.next){case 0:return g.prev=0,g.next=3,fetch({type:'POST',url:url('admin/plugins/config/yggdrasil-api/generate')});case 3:b=g.sent,c=b.errno,d=b.msg,e=b.key,0===c?(toastr.success('\u6210\u529F\u751F\u6210\u4E86\u4E00\u4E2A\u65B0\u7684 4096 bit OpenSSL RSA \u79C1\u94A5'),$('td.value textarea').val(e),$('input[value=keypair]').parent().submit()):swal({type:'warning',html:d}),g.next=13;break;case 10:g.prev=10,g.t0=g['catch'](0),showAjaxError(g.t0);case 13:case'end':return g.stop();}},a,this,[[0,10]])})));

  // $('[name=generate-key]').click(async function () {
  //   try {
  //     const { errno, msg, key } = await fetch({
  //       type: 'POST',
  //       url: url('admin/plugins/config/yggdrasil-api/generate')
  //     });
  //
  //     if (errno === 0) {
  //       toastr.success('成功生成了一个新的 4096 bit OpenSSL RSA 私钥');
  //
  //       $('td.value textarea').val(key);
  //       $('input[value=keypair]').parent().submit();
  //     } else {
  //       swal({ type: 'warning', html: msg });
  //     }
  //   } catch (error) {
  //     showAjaxError(error);
  //   }
  // });
</script>
@endsection
