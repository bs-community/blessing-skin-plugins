@extends('admin.master')

@section('title', trans('general.plugin-manage'))

@section('content')

<?php $forms = DataIntegration\Form::getAll(); ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      数据对接配置
      <small>Data Adapter Configuration</small>
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        {!! $forms['connection']->render() !!}
      </div>
      <div class="col-md-6">
        {!! $forms['config']->render() !!}

        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">如何填写？</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            <p>请查看：<a href="https://github.com/printempw/blessing-skin-server/wiki/%E5%A6%82%E4%BD%95%E5%A1%AB%E5%86%99%E6%95%B0%E6%8D%AE%E5%AF%B9%E6%8E%A5%E9%85%8D%E7%BD%AE">如何填写数据对接配置 @GitHub Wiki</a></p>
            <p>打不开 GitHub 的请自行解决。</p>
          </div><!-- /.box-body -->
        </div><!-- /.box -->
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

