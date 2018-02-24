@extends('Blessing\BatchImport::import')

@section('step-content')
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">选择目录</h3>
    </div><!-- /.box-header -->
    <div class="box-body">
        <p>请输入要导入的材质文件所在的目录</p>
        <p>
            <input type="text" class="form-control" id="dir" value="{{ Cache::get('import-dir') }}" placeholder="填写服务器上的绝对路径">
        </p>

        <div class="callout callout-warning" style="display: none;">
            <p>非 Windows 服务器选了这个会导致原来正常的中文文件名乱码（笑）</p>
            <p>如果打开了这个【还是乱码】，那就请尝试关闭此选项再重新导入一次。</p>
        </div>
        <label for="gbk">
            <input type="checkbox" class="form-control" id="gbk"> 我要导入的文件中有中文文件名且我的服务器系统为 Windows（检测不一定准确）
        </label>
    </div><!-- /.box-body -->
    <div class="box-footer">
        <button id="next" class="btn btn-primary" onclick="check_dir()">下一步</button>
    </div><!-- /.box-footer -->

</div>
@endsection

@push('scripts')
<script>
    $('#gbk').on('ifToggled', function() {
        $(this).prop('checked') ? $('.callout').show() : $('.callout').hide();
    });
</script>
@endpush
