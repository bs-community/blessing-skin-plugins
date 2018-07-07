@extends('admin.master')

@section('title', '生成邀请码')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      生成邀请码
    </h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-4">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">目前可用的邀请码</h3>
          </div><!-- /.box-header -->
          <div class="box-body table-responsive">
            <div class="callout callout-info">
              <p>如需删除或修改邀请码，请前往 <code>invitation_codes</code> 数据表手动操作。</p>
            </div>
            <table class="table table-hover">
                <thead>
                  <tr>
                    <th>邀请码</th>
                    <th>生成时间</th>
                  </tr>
                </thead>

                <tbody>
                  @forelse ($available as $entry)
                  <tr id="code-{{ $entry->id }}">
                    <td>{{ $entry->code }}</td>
                    <td>{{ $entry->generated_at }}</td>
                  </tr>
                  @empty
                  <tr><td>没有可用的邀请码</td></tr>
                  @endforelse
                </tbody>
              </table>
          </div>
          <div class="box-footer">
            <form method="post">
              <button type="submit" class="btn btn-primary pull-right">生成邀请码</button>
              <div class="input-group">
                <span class="input-group-addon">数量：</span>
                <input type="text" class="form-control" name="amount" style="width: 50%;" placeholder="要生成的邀请码数量">
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-6">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">已被使用的邀请码</h3>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
              <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>邀请码</th>
                      <th>生成时间</th>
                      <th>使用者 UID</th>
                      <th>使用时间</th>
                    </tr>
                  </thead>

                  <tbody>
                    @forelse ($used as $entry)
                    <tr id="code-{{ $entry->id }}">
                      <td>{{ $entry->code }}</td>
                      <td>{{ $entry->generated_at }}</td>
                      <td>{{ $entry->used_by }}</td>
                      <td>{{ $entry->used_at }}</td>
                    </tr>
                    @empty
                    <tr><td>没有已被使用的邀请码</td></tr>
                    @endforelse
                  </tbody>
                </table>
            </div>
          </div>
        </div>
    </div>
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
