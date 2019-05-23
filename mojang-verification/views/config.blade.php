@extends('admin.master')

@section('title', '正版验证配置')

@section('content')

@php
$form = Option::form('mojang_verification', '积分配置', function ($form) {

    $form->text('mojang_verification_score_award', '积分奖励')
        ->placeholder('默认为 0')
        ->description('通过正版验证后可获得的积分');

})->handle();
@endphp

<div class="content-wrapper">
  <section class="content-header">
    <h1>正版验证配置</h1>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        {!! $form->render() !!}
      </div>
    </div>
  </section>
</div>

@endsection
