@extends('auth.master')

@section('title', '绑定游戏角色名')

@section('content')

<div class="login-box">
  <div class="login-logo">
    <a href="{{ url('/') }}">{{ option('site_name') }}</a>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg">请绑定您的游戏角色名称</p>

    <div class="callout callout-info">
      <p>根据本站的设置，每个用户至多只能拥有一个角色。如果您之前添加了多个角色，请选择一个提交（您之前拥有的其他角色将被释放）。如果您之前没有添加角色，请选择一个未被他人使用的角色名。</p>
    </div>

    @if (! option('allow_change_player_name'))
    <div class="callout callout-warning">
      <p>注意：角色名设置后无法自行修改。如需修改请联系本站管理员。</p>
    </div>
    @endif

    <form method="post" id="login-form">
      <div class="form-group has-feedback">
        <input id="player-name" type="text" class="form-control" placeholder="角色名">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>

      <div id="msg" class="callout callout-warning hide"></div>

      <div class="row">
        <div class="col-xs-6"></div>
        <div class="col-xs-6">
          <button id="bind-button" type="button" class="btn btn-primary btn-block btn-flat">绑定</button>
        </div><!-- /.col -->
      </div>
    </form>

  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@endsection

@section('script')
<script src="{{ plugin_assets('single-player-limit', 'assets/dist/bind.js') }}"></script>
@endsection
