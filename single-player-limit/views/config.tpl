@extends('admin.master')

@section('title', '单角色限制')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      单角色限制
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <?php
        $form = Option::form('config', '配置', function ($form) {
          $form->checkbox('allow_change_player_name', '绑定角色名')
            ->label('允许用户自行修改绑定的角色名')
            ->description('如果禁止用户自行修改角色名，请使用下方的表单修改用户的绑定角色名。');
        })->handle(); ?>

        {!! $form->render() !!}
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="box box-default">
          <div class="box-header with-border">
            <h3 class="box-title">查询用户绑定的角色名 / 反查</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            <table class="table">
              <tbody>
                <tr>
                  <td class="key">
                    通过 UID 查询绑定的角色名
                  </td>
                  <td class="value">
                    <div class="input-group">
                      <input type="text" class="form-control" id="query-uid" placeholder="用户的 UID 可在【用户管理】页面查询">
                      <span class="input-group-btn">
                        <button class="btn btn-default form-control" type="button" onclick="queryByUid()">查询</button>
                      </span>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="key">
                    通过绑定的角色名反查用户
                  </td>
                  <td class="value">
                    <div class="input-group">
                      <input type="text" class="form-control" id="query-player-name" placeholder="要查询的角色名">
                      <span class="input-group-btn">
                        <button class="btn btn-default form-control" type="button" onclick="queryByPlayerName()">查询</button>
                      </span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div><!-- /.box-body -->
        </div><!-- /.box -->
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="box box-default">
          <div class="box-header with-border">
            <h3 class="box-title">修改用户的绑定角色名</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            <table class="table">
              <tbody>
                <tr>
                  <td class="key">
                    要操作的用户 UID
                  </td>
                  <td class="value">
                      <input type="text" class="form-control" id="user-uid" placeholder="用户的 UID 可在【用户管理】页面查询">
                  </td>
                </tr>

                <tr>
                  <td class="key">
                    新绑定的角色名
                  </td>
                  <td class="value">
                      <input type="text" class="form-control" id="new-player-name" placeholder="角色名规则遵循本站设置">
                  </td>
                </tr>
              </tbody>
            </table><br>

            <div class="callout callout-warning">
              注意：这个表单很强力，就算你填的新角色名已经被人占用了，提交后也会强制把该角色让渡给上面所指定的用户。
            </div>
          </div><!-- /.box-body -->
          <div class="box-footer">
            <button class="btn btn-primary pull-left" onclick="changeUserBindPlayerName()">提交修改</button>
          </div><!-- /.box-footer -->
        </div><!-- /.box -->
      </div>
    </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script src="{{ plugin_assets('single-player-limit', 'assets/dist/config.js') }}"></script>
@endsection
