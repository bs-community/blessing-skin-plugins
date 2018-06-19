@extends('admin.master')

@section('title', '上传奖励积分')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      上传材质奖励积分
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <?php
        $form = Option::form('config', '配置', function ($form) {
          $form->group('score_award_per_texture', '每上传一个材质奖励')->text('score_award_per_texture')->addon('积分');
          $form->checkbox('take_back_scores_after_deletion', '回收积分')->label('删除上传的材质后收回奖励积分');
        })->handle(); ?>

        {!! $form->render() !!}
      </div>
    </div>
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
