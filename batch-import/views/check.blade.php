@extends('BatchImport::master')

@section('step-content')
<div class="row">
  <div class="col-md-6">
    <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">选择目录</h3>
        </div><!-- /.box-header -->
        <div class="box-body">
          <p>请输入要导入的材质文件所在的目录：</p>
          <p>
            <input type="text" class="form-control" id="dir" value="{{ Cache::get('import-source-dir') }}" placeholder="填写服务器上的绝对路径">
          </p>

          <div class="callout callout-info">
            <p>如果你使用的是 Windows 服务器，那么文件名中的中文字符在导入后可能会变成乱码。</p>
            <p>请在下一步的预览中查看导入后的文件名，如果有乱码，请返回本页勾选下方的选项后重试。</p>
          </div>

          <label for="gbk">
            <input type="checkbox" id="gbk"> 尝试消除乱码
          </label>
        </div><!-- /.box-body -->
        <div class="box-footer">
          <button id="next" class="btn btn-primary">下一步</button>
        </div><!-- /.box-footer -->
      </div>
  </div>
</div>
@endsection

@section('script')
<script src="{{ plugin_assets('batch-import', 'assets/dist/check.js') }}"></script>
@stack('scripts')
@endsection
