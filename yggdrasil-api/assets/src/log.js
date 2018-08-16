'use strict';

const actions = {
  authenticate: '登录',
  refresh: '刷新令牌',
  validate: '验证令牌',
  signout: '登出',
  invalidate: '吊销令牌',
  join: '请求加入服务器',
  has_joined: '进入服务器'
}

const logTableColumnDefs = [
  {
    targets: 0,
    data: 'id',
    width: '1%',
    searchable: false
  },
  {
    targets: 1,
    data: 'action',
    render: data => `${actions[data]} ( ${data} )`
  },
  {
    targets: 2,
    data: 'email',
    name: 'users.email',
    render: (data, type, row) => `${data} <a href="${url('admin/users?uid=')+row.user_id}" class="label label-primary">UID: ${row.user_id}</a>`
  },
  {
    targets: 3,
    data: 'player_name',
    name: 'players.player_name',
    render: (data, type, row) => data ? `${data} <a href="${url('admin/players?uid=')+row.user_id}" class="label bg-green">PID: ${row.player_id}</a>` : 'N/A'
  },
  {
    targets: 4,
    data: 'parameters',
    searchable: false,
    orderable: false,
    render: () => `<a class="show" href="javascript:;">点击查看</a>`
  },
  {
    targets: 5,
    data: 'ip',
    render: $.fn.dataTable.render.text(),
    orderable: false
  },
  {
    targets: 6,
    data: 'time'
  }
];

$.yggLogTable = $('#ygg-log-table').DataTable({
  ajax: url('admin/yggdrasil-log/data'),
  scrollY: ($('.content-wrapper').height() - $('.content-header').outerHeight()) * 0.6,
  columnDefs: logTableColumnDefs
}).on('click', 'a.show', function () {
  let data = $.yggLogTable.row($(this).parents('tr')).data();
  swal({ type: 'info', text: data.parameters });
}).on('xhr.dt', handleDataTablesAjaxError);
