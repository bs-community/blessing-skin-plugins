@extends('BatchImport::master')

@section('step-content')
<div class="row">
  <div class="col-md-6">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">导入选项</h3>
      </div><!-- /.box-header -->

      <div class="box-body">
        <p>所选目录：<code>{{ Cache::get('import-source-dir') }}</code></p>

        <p>目录下有效文件（长宽比为 2 或 1）数：
          <code> {{ count($files) }} </code>
        </p>

        <p>导入该目录下的<b>全部材质</b>为：
          <select class="form-control" id="type" style="display:inline-block;width:initial;">
            <option value="steve">皮肤 (Steve)</option>
            <option value="alex" >皮肤 (Alex)</option>
            <option value="cape" >披风</option>
          </select>
        </p>

        <p>导入后的材质上传者 UID：
          <input type="text" value="1" id="uploader" class="form-control" style="display:inline-block;width:initial;">
        </p>

        <div class="callout callout-warning">
          <p>提示：一次性导入过多材质可能会因为 PHP 执行时间超时而失败。</p>
        </div>

      </div><!-- /.box-body -->
      <div class="box-footer">
        @if (count($files) > 0)
        <button id="next" type="button" class="btn btn-primary">开始导入</button>
        @else
        <button type="button" class="btn btn-primary" disabled>没有可导入的文件</button>
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title">导入文件名预览（前 50 项）</h3>
      </div><!-- /.box-header -->
      <div class="box-body">
        <div class="callout callout-info">
          <p>如果中文文件名乱码了，请返回上一页勾选「尝试消除乱码」选项。</p>
          <p>如果勾选后乱码了，那就取消勾选试试。</p>
        </div>
        <textarea class="form-control" rows="20">{{ $preview }}</textarea>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
  $('#next').click(function () {
    location.href = '?step=3&type=' + $('#type').val() + '&uploader=' + $('#uploader').val();
  });
</script>
@stack('scripts')
@endsection
