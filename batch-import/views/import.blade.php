@extends('BatchImport::master')

@section('step-content')
<div id="import-status" class="callout callout-info">
  <p>正在导入，请耐心等待。如果一直没有响应，可以尝试刷新页面。</p>
</div>
<div class="box box-primary table-responsive no-padding">
  <div class="box-body">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>材质名称</th>
          <th>类型</th>
          <th>上传者</th>
          <th>导入状态</th>
          <th>源文件</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($files as $id => $entry)
        <tr id="entry-{{ $id }}" class="queue-entry">
          <td>{{ str_replace('.png', '', basename($entry)) }}</td>
          <td>{{ request('type') }}</td>
          <td>{{ request('uploader') }}</td>
          <td id="status"><i class="fa fa-tasks"></i> 队列中</td>
          <td>{{ $entry }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div><!-- /.box-body -->
</div>
@endsection

@section('script')
<script>let queue = {{ json_encode(array_keys($files)) }};</script>
<script src="{{ plugin_assets('batch-import', 'assets/dist/import.js') }}"></script>
@stack('scripts')
@endsection
