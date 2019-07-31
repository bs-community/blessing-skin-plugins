@extends('user.master')

@section('title', trans('Blessing\ConfigGenerator::config.generate-config'))

@section('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/highlight.js/styles/arduino-light.css">
<style> pre { border: 0; } td[class='key'], td[class='value'] { border-top: 0 !important; } </style>
@endsection

@section('content')

@php
    $user = auth()->user();
@endphp

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('Blessing\ConfigGenerator::config.generate-config') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('Blessing\ConfigGenerator::config.mod-requirement') }}</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        {!! trans('Blessing\ConfigGenerator::config.mod-intro') !!}
                    </div><!-- /.box-body -->
                </div><!-- /.box -->

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('Blessing\ConfigGenerator::config.generate-config') }}</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="key">MOD</td>
                                    <td class="value">
                                       <select class="form-control" id="mod-select">
                                            <option value="csl">Custom Skin Loader</option>
                                            <option value="usm">Universal Skin Mod</option>
                                       </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-star"></i> CustomSkinLoader ExtraList
                        </h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <p>{!! trans('Blessing\ConfigGenerator::config.extra-list-intro') !!}</p>

                        <button id="download-extralist" class="btn btn-primary">
                            {!! trans('Blessing\ConfigGenerator::config.extra-list-download') !!}
                        </button>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('Blessing\ConfigGenerator::config.config-file') }}</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">

<pre id="config-csl">
{
    "enable": true,
    "loadlist": [
        {
            "name": "<?php echo option('site_name'); ?>",
            "type": "CustomSkinAPI",
            "root": "<?php echo option('site_url')."/csl/"; ?>"
        },
        {
            "name": "Mojang",
            "type": "MojangAPI"
        }
    ]
}
</pre>

<pre id="config-usm" style="display: none;">
{
    "rootURIs": [
        "<?php echo option('site_url'); ?>/usm"
    ],
    "legacySkinURIs": [],
    "legacyCapeURIs": []
}
</pre>

                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/highlight.js@9.15.8/lib/highlight.js"></script>
<script>
    $(document).ready(function() {
        $('pre').each(function(i, block) {
            hljs.highlightBlock(block);
        });
    });

    function showConfig() {
        $('#config-csl').hide();
        $('#config-usm').hide();
        $('#config-'+$('#mod-select').val()).show();
    }

    function createAndDownloadFile(fileName, content) {
        var aTag = document.createElement('a');
        var blob = new Blob([content]);
        aTag.download = fileName;
        aTag.href = URL.createObjectURL(blob);
        aTag.click();
        URL.revokeObjectURL(blob);
    }

    $('#download-extralist').click(function () {
        createAndDownloadFile(blessing.site_name + '.json', JSON.stringify({
            "name": blessing.site_name,
            "type": "CustomSkinAPI",
            "root": blessing.base_url + "/csl/"
        }));
    });
    $('#mod-select').change(showConfig);
</script>

@endsection
