@extends('admin.master')

@section('title', '分享奖励积分')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      分享皮肤、披风获得奖励积分
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        @php
        $form = Option::form('config', '配置', function ($form) {
          $form->group('score_award_per_texture', '每上传一个材质奖励')->text('score_award_per_texture')->addon('积分');
          $form->checkbox('take_back_scores_after_deletion', '回收积分')->label('删除上传的材质后收回奖励积分');
          $form->group('score_award_per_like', '材质每被收藏一次奖励上传者')->text('score_award_per_like')->addon('积分');
        })->handle();
        @endphp

        {!! $form->render() !!}
      </div>
    </div>
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
