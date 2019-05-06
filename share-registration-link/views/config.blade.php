@extends('admin.master')

@section('title', '注册链接分享配置')

@section('content')

@php
  $form = Option::form('reg_links_share', '积分配置', function ($form) {

    $form->text('reg_link_sharer_score', '邀请者可获得的积分')->placeholder('默认为 50');
    $form->text('reg_link_sharee_score', '被邀请者可获得的积分')->placeholder('默认为 0');

  })->handle();
@endphp

<div class="content-wrapper">
  <section class="content-header">
    <h1>注册链接分享配置</h1>
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
