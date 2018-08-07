@extends('admin.master')

@section('title', trans('ReportTexture::general.title'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      {{ trans('ReportTexture::general.title') }}
    </h1>
  </section>

  <?php
    $trans = trans('ReportTexture::general.config');

    $form = Option::form('config', $trans['title'], function ($form) use ($trans) {
      // 提交举报所需积分
      $key1 = 'reporter_score_modification';
      $form->text($key1, $trans['options'][$key1]['title'])->description($trans['options'][$key1]['description']);
      // 举报通过后奖励积分
      $key2 = 'reporter_reward_score';
      $form->text($key2, $trans['options'][$key2]['title']);
      // 内容政策
      $key3 = 'content_policy';
      $form->textarea('content_policy', $trans['options'][$key3]['title'])->rows(10)->description($trans['options'][$key3]['description']);
    })->always(function ($form) use ($trans) {
      foreach (['reporter_score_modification', 'reporter_reward_score'] as $key) {
        if (filter_var(option($key), FILTER_VALIDATE_INT) === false) {
          $form->addMessage($trans['invalid-int'], 'warning');
        }
      }
    })->handle(function () {
      option(['content_policy_'.config('app.locale') => request('content_policy')]);
    });
  ?>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        {!! $form->render() !!}
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
{{-- <script src="{{ plugin_assets('single-player-limit', 'assets/dist/config.js') }}"></script> --}}
@endsection
