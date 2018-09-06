@extends('admin.master')

@section('title', 'SEO Meta Tags - '.trans('general.plugin-manage'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      SEO Meta 标签配置
      <small>SEO Meta Tags Configuration</small>
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <?php

        $form = Option::form('meta', 'META 标签', function($form)
        {
          $form->text('meta_keywords', '关键词')->hint('使用半角逗号分隔');
          $form->text('meta_description', '描述')->hint('留空以使用 站点配置 中的站点描述');

          $form->textarea('meta_extras', '自定义 META 标签')->rows(6);

        })->handle();

        ?>

        {!! $form->render() !!}
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

