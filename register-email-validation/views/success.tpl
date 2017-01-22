@extends('auth.master')

@section('title', trans('auth.bind.title'))

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ option('site_name') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">邮箱验证成功</p>

        <p style="text-align: center;font-size: 80px;margin-top: -10px;margin-bottom: 15px;color: #11a011;">
            <i class="fa fa-check" aria-hidden="true"></i>
        </p>

        <div class="row">
            <div class="col-xs-8">
                <p>你将在 <span id="sec">5</span> 秒后被重定向。</p>
            </div>
            <div class="col-xs-4">
                <a href="{{ url('/') }}" class="btn btn-primary btn-block btn-flat">返回首页</a>
            </div><!-- /.col -->
        </div>

        <script>
            var sec = 5;

            window.setInterval(function() {
                sec--;

                $('#sec').html(sec);

                if (sec <= 0) {
                    window.location = "../";
                }
            }, 1000);
        </script>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@endsection
