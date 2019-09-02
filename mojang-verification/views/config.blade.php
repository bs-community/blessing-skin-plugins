@extends('admin.master')

@section('title', trans('GPlane\Mojang::mojang-verification.config.title'))

@section('content')

@php
$form = Option::form('mojang_verification', trans('GPlane\Mojang::mojang-verification.config.score_config'), function ($form) {

    $form->text('mojang_verification_score_award', trans('GPlane\Mojang::mojang-verification.config.score_award'))
        ->placeholder(trans('GPlane\Mojang::mojang-verification.config.default'))
        ->description(trans('GPlane\Mojang::mojang-verification.config.description'));

})->handle();
@endphp

<div class="content-wrapper">
  <section class="content-header">
    <h1>{!! trans('GPlane\Mojang::mojang-verification.config.title') !!}</h1>
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
