'use strict';

let dom = `
  <div class="box box-warning">
    <div class="box-header with-border">
      <h3 class="box-title">外置登录系统 - 最近活动</h3>
    </div><!-- /.box-header -->
    <div class="box-body">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>操作</th>
            <th>角色名</th>
            <th>IP</th>
            <th>时间</th>
          </tr>
        </thead>
        <tbody id="recent-activities">
        </tbody>
      </table>
    </div><!-- /.box-body -->
  </div>
`;

$('section.content > .row .col-md-4 .box').after(dom);

const actions = {
  authenticate: '登录',
  refresh: '刷新令牌',
  validate: '验证令牌',
  signout: '登出',
  invalidate: '吊销令牌',
  join: '请求加入服务器',
  has_joined: '进入服务器'
}

$(document).ready(async () => {
  try {
    const entries = await fetch({
        type: 'GET',
        url: url('user/get-recent-activities'),
        contentType: false,
        dataType: 'json'
    });

    entries.forEach(entry => {
      $('#recent-activities').append(`
        <tr>
          <td>${actions[entry.action]}</td>
          <td>${entry.player_name ? entry.player_name : 'N/A'}</td>
          <td>${entry.ip}</td>
          <td>${entry.time}</td>
        </tr>
      `);
    });

    if (entries.length === 0) {
      $('#recent-activities').append('<tr><td>无最近活动记录</td></tr>');
    }
  } catch (error) {
    showAjaxError(error);
  }
});
