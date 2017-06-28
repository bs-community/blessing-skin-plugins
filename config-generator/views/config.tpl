@extends('user.master')

@section('title', trans('Blessing\ConfigGenerator::config.generate-config'))

@section('style')
<link rel="stylesheet" href="{{ plugin_assets('config-generator', 'assets/highlight/styles/arduino-light.css') }}">
<style> pre { border: 0; } td[class='key'], td[class='value'] { border-top: 0 !important; } </style>
@endsection

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('Blessing\ConfigGenerator::config.generate-config') }}
            <small>Configuration Generator</small>
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

                                <tr>
                                    <td class="key">{{ trans('Blessing\ConfigGenerator::config.version') }}</td>
                                    <td class="value">
                                       <select class="form-control" id="version-select">
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

<pre id="config-13_1-upper">
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

<pre id="config-13_1-lower" class="hljs ini" style="display: none;">
# skinurls.txt
<?php echo option('site_url'); ?>/skin/*.png
http://skins.minecraft.net/MinecraftSkins/*.png

# capeurls.txt
<?php echo option('site_url'); ?>/cape/*.png
</pre>

<pre id="config-1_4-upper" style="display: none;">
{
    "rootURIs": [
        "<?php echo option('site_url'); ?>/usm",
        "http://www.skinme.cc/uniskin"
    ],
    "legacySkinURIs": [],
    "legacyCapeURIs": []
}
</pre>

<pre id="config-1_2-1_3" class="hljs ini" style="display: none;">
# <?php echo option('site_name')."\n"; ?>
Root: <?php echo option('site_url'); ?>/usm
</pre>

<pre id="config-1_2-lower" class="hljs ini" style="display: none;">
# <?php echo option('site_name')."\n"; ?>
Skin: <?php echo option('site_url'); ?>/skin/%s.png
Cape: <?php echo option('site_url'); ?>/cape/%s.png
# Mojang
Skin: http://skins.minecraft.net/MinecraftSkins/%s.png
Cape: http://skins.minecraft.net/MinecraftCloaks/%s.png
</pre>

                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script type="text/javascript" src="{{ plugin_assets('config-generator', 'assets/highlight/highlight.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('pre').each(function(i, block) {
            hljs.highlightBlock(block);
        });
    });

    function freshVersionSelect(element) {
        $('#version-select').children().each(function() { $(this).remove(); });

        if ($(element).val() == "csl") {
            $('#version-select').append('<option value="13_1-upper">'+trans('configGenerator.csl13_1Upper')+'</option>');
            $('#version-select').append('<option value="13_1-lower">'+trans('configGenerator.csl13_1Lower')+'</option>');
        } else if ($(element).val() == "usm") {
            $('#version-select').append('<option value="1_4-upper">'+trans('configGenerator.usm1_4Upper')+'</option>');
            $('#version-select').append('<option value="1_2-1_3">'+trans('configGenerator.usm1_2To1_3')+'</option>');
            $('#version-select').append('<option value="1_2-lower">'+trans('configGenerator.usm1_2Lower')+'</option>');
        }

        showConfig();
    }

    function showConfig() {
        $('#config-13_1-upper').hide();
        $('#config-13_1-lower').hide();
        $('#config-1_4-upper').hide();
        $('#config-1_2-1_3').hide();
        $('#config-1_2-lower').hide();
        $('#config-'+$('#version-select').val()).show();
    }

    /**
     * 创建并下载文件
     * @param  {String} fileName 文件名
     * @param  {String} content  文件内容
     */
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

    $('#mod-select').change(function () {
        freshVersionSelect(this);
    });
    $('#version-select').change(showConfig);

    freshVersionSelect('#mod-select');
</script>

@endsection
