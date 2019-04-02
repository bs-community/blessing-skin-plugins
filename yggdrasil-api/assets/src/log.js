'use strict'

$.extend(true, $.fn.dataTable.defaults, {
  language: {
    sProcessing: '处理中...',
    sLengthMenu: '显示 _MENU_ 项结果',
    sZeroRecords: '没有匹配结果',
    sInfo: '显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项',
    sInfoEmpty: '显示第 0 至 0 项结果，共 0 项',
    sInfoFiltered: '(由 _MAX_ 项结果过滤)',
    sInfoPostFix: '',
    sSearch: '搜索:',
    sUrl: '',
    sEmptyTable: '表中数据为空',
    sLoadingRecords: '载入中...',
    sInfoThousands: ',',
    oPaginate: {
      sFirst: '首页',
      sPrevious: '上页',
      sNext: '下页',
      sLast: '末页'
    },
    oAria: {
      sSortAscending: ': 以升序排列此列',
      sSortDescending: ': 以降序排列此列'
    }
  },
  serverSide: true
});

{
  function url (path) {
    return `${blessing.base_url}/${path}`
  }

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
      render: (data, type, row) => `${data} <a href="${url('admin/users?uid=') + row.user_id}" class="label label-primary">UID: ${row.user_id}</a>`
    },
    {
      targets: 3,
      data: 'player_name',
      name: 'players.player_name',
      render: (data, type, row) => data ? `${data} <a href="${url('admin/players?uid=') + row.user_id}" class="label bg-green">PID: ${row.player_id}</a>` : 'N/A'
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
  ]

  $.yggLogTable = $('#ygg-log-table').DataTable({
    ajax: blessing.base_url + '/admin/yggdrasil-log/data',
    scrollY: ($('.content-wrapper').height() - $('.content-header').outerHeight()) * 0.6,
    columnDefs: logTableColumnDefs
  }).on('click', 'a.show', function () {
    const data = $.yggLogTable.row($(this).parents('tr')).data()
    alert('附加参数：\n' + data.parameters)
  })
}
