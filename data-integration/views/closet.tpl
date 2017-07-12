@extends('user.master')

@section('title', trans('general.my-closet'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.my-closet') }}
        </h1>
        <div class="breadcrumb">
            <a href="{{ url('skinlib/upload') }}"><i class="fa fa-upload"></i> {{ trans('user.closet.upload') }}</a>
            <a href="{{ url('skinlib') }}"><i class="fa fa-search"></i> {{ trans('user.closet.search') }}</a>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- Left col -->
            <div class="col-md-8">
                <!-- Custom tabs -->
                <div class="nav-tabs-custom">
                    <!-- Tabs within a box -->
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#skin-category" class="category-switch" data-toggle="tab">{{ trans('general.skin') }}</a></li>
                        <li><a href="#cape-category" class="category-switch" data-toggle="tab">{{ trans('general.cape') }}</a></li>

                        <li class="pull-right" style="padding: 7px;">
                            <div class="has-feedback pull-right">
                                <div class="user-search-form">
                                    <input type="text" name="q" class="form-control input-sm" placeholder="{{ trans('user.closet.type-to-search') }}">
                                    <span class="glyphicon glyphicon-search form-control-feedback"></span>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="tab-content no-padding">
                        <div class="tab-pane active box-body" id="skin-category"></div>
                        <div class="tab-pane box-body" id="cape-category"></div>
                    </div>
                    <div class="box-footer">
                        <div class="pull-right" id="closet-paginator" last-skin-page="1" last-cape-page="1"></div>
                    </div>
                </div><!-- /.nav-tabs-custom -->

            </div>

            <!-- Right col -->
            <div class="col-md-4">

                <div class="box box-default">
                    @include('common.texture-preview')

                    <div class="box-footer">
                        <button class="btn btn-primary" onclick="javascript:setTextureOfUniquePlayer()">{{ trans('user.closet.use-as.button') }}</button>
                    </div><!-- /.box-footer -->
                </div>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script>
    function setTextureOfUniquePlayer() {
        if (selectedTextures['skin'] == undefined && selectedTextures['cape'] == undefined) {
            toastr.info(trans('user.emptySelectedTexture'));
        } else {
            $.ajax({
                type: "POST",
                url: url('user/player/set'),
                dataType: "json",
                data: {
                    'pid': {{ $player->pid }},
                    'tid[skin]': selectedTextures['skin'],
                    'tid[cape]': selectedTextures['cape']
                },
                success: function (json) {
                    if (json.errno == 0) {
                        swal({
                            type: 'success',
                            html: json.msg
                        });
                        $('#modal-use-as').modal('hide');
                    } else {
                        toastr.warning(json.msg);
                    }
                },
                error: showAjaxError
            });
        }
    }

    $(document).ready(TexturePreview.init3dPreview);
    // Auto resize canvas to fit responsive design
    $(window).resize(TexturePreview.init3dPreview);
</script>
@endsection
