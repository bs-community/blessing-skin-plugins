@extends('admin.master')

@section('title', 'BS Super Cache - '.trans('general.plugin-manage'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      BS Super Cache 配置
      <small>BS Super Cache Configuration</small>
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <div class="box">
          <div class="box-header with-border">
            <h3 class="box-title">缓存配置</h3>
          </div><!-- /.box-header -->
          <div class="box-body table-responsive no-padding">
            <form method="post">
              <input type="hidden" name="option" value="config">
              {{ csrf_field() }}
              <div class="box-body">
                <?php
                if (isset($_GET['flush'])) {
                  Cache::flush();
                  echo '<div class="callout callout-success">缓存已清除。</div>';
                }

                $options = [
                  'enable_avatar_cache',
                  'enable_preview_cache',
                  'enable_json_cache',
                  'enable_notfound_cache'
                ];

                if (isset($_POST['option']) && ($_POST['option'] == "config")) {
                  foreach ($options as $key) {
                    if (isset($_POST[$key])) {
                      Option::set($key, 'true');
                    } else {
                      Option::set($key, 'false');
                    }
                  }

                  echo '<div class="callout callout-success">设置已保存。</div>';
                }

                echo '<div class="callout callout-info">当前缓存驱动为 <code>'.config('cache.default').'</code></div>';
                ?>
                <table class="table">
                  <tbody>
                    <tr>
                      <td class="value">
                        <label for="1">
                          <input {{ option('enable_avatar_cache') ? 'checked="true"' : '' }} type="checkbox" id="1" name="enable_avatar_cache" value="true">
                            启用头像缓存
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td class="value">
                        <label for="2">
                          <input {{ option('enable_preview_cache') ? 'checked="true"' : '' }} type="checkbox" id="2" name="enable_preview_cache" value="true">
                            启用材质预览缓存
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td class="value">
                        <label for="3">
                          <input {{ option('enable_json_cache') ? 'checked="true"' : '' }} type="checkbox" id="3" name="enable_json_cache" value="true">
                            启用 Json Profile 缓存
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td class="value">
                        <label for="4">
                          <input {{ option('enable_notfound_cache') ? 'checked="true"' : '' }} type="checkbox" id="4" name="enable_notfound_cache" value="true">
                            启用 404 缓存
                        </label>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div><!-- /.box-body -->
              <div class="box-footer">
                <button type="submit" name="submit" class="btn btn-primary">提交</button>
                <a href="?action=config&id=bs-super-cache&flush=1" class="pull-right btn btn-danger">清除所有缓存</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

