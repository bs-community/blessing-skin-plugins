@extends('auth.master')

@section('title', trans('auth.bind.title'))

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ option('site_name') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">哦，朋友，你似乎还没有验证你的注册邮箱</p>

        <form method="post" id="login-form">
            <div class="form-group has-feedback">
                <input name="validate_email" type="email" class="form-control" value="{{ $user->email }}" placeholder="{{ trans('auth.email') }}">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>

            <p>没有收到验证邮件？点击再发一封。</p>

            @if ($msg)
            <div id="msg" class="callout callout-warning">{{ $msg }}</div>
            @endif

            <div class="row">
                <div class="col-xs-6"></div>
                <div class="col-xs-6">
                    @if ($remain > 0)
                    <button type="button" class="btn btn-primary btn-block btn-flat" disabled="disabled">{{ $remain }} 秒后发送邮件</button>
                    @else
                    <button type="submit" class="btn btn-primary btn-block btn-flat">发送邮件</button>
                    @endif
                </div><!-- /.col -->
            </div>
        </form>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@endsection
