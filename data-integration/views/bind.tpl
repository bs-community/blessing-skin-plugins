@extends('auth.master')

@section('title', '绑定游戏角色名')

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ option('site_name') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">哦，朋友，你似乎还没有绑定你的游戏角色名</p>

        <div class="callout callout-info">
            <p>本站启用了数据对接，一个用户只能对应一个角色。绑定后无法修改。</p>
        </div>

        <div class="callout callout-warning">
            <p>如果您之前添加了多个角色，请选择一个提交。如果您之前没有添加角色，请选择一个未被他人使用的角色名。</p>
        </div>

        <form method="post" id="login-form">
            <div class="form-group has-feedback">
                <input name="username" type="text" class="form-control" value="{{ $user->username }}" placeholder="{{ trans('general.player.player-name') }}">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>

            @if ($msg)
            <div id="msg" class="callout callout-warning">{{ $msg }}</div>
            @endif

            <div class="row">
                <div class="col-xs-6"></div>
                <div class="col-xs-6">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">绑定</button>
                </div><!-- /.col -->
            </div>
        </form>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@endsection
