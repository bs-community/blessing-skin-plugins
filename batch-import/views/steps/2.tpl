@extends('Blessing\BatchImport::import')

@section('step-content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">导入选项</h3>
            </div><!-- /.box-header -->

            <div class="box-body">
                <p>所选目录：<code>{{ session('import-dir') }}</code></p>

                <p>目录下有效文件（长宽比为 2 或 1）数：
                    <code> {{
                        $file_num = Blessing\BatchImport\Utils::prepareImportSource(session('import-dir'))
                    }} </code>
                </p>

                <p>导入该目录下的材质为：
                    <select class="form-control" id="type" style="display: inline-block;width: initial;">
                        <option value="steve">皮肤 (Steve)</option>
                        <option value="alex" >皮肤 (Alex)</option>
                        <option value="cape" >披风</option>
                    </select>
                </p>

                <p>导入后的材质上传者 UID：
                    <input style="display: inline-block;width: initial;" type="text" value="1" id="uploader" class="form-control">
                </p>

                <div class="callout callout-warning">
                    <p>提示：一次性批量导入过多可能达到 PHP 脚本最大执行时间，还请注意。</p>
                </div>

            </div><!-- /.box-body -->
            <div class="box-footer">
                @if ($file_num > 0)
                <button type="button" class="btn btn-primary" id="start-import"
                        data-toggle="modal"
                        data-target="#modal-start-import"
                        data-backdrop="static"
                        data-keyboard="false">
                    开始导入
                </button>
                @else
                <button type="button" class="btn btn-primary" disabled="disabled">目录下无有效文件</button>
                @endif
            </div>

        </div>
    </div>
</div>

<div id="modal-start-import" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">正在导入（总数 {{ $file_num }}）</h4>
            </div>
            <div class="modal-body">
                <p>已导入：<span id="imported-num">0</span></p>
                <br>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                        <span id="imported-progress">0</span>%
                    </div>
                </div>

                <div class="callout callout-info">
                    <p>如果一直卡着不动，请刷新试试</p>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection
