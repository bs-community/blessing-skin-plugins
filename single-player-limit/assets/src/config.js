'use strict';

async function queryByUid() {
  try {
    const { errno, msg } = await fetch({
      type: 'POST',
      url: url('admin/query-player-name-by-uid'),
      dataType: 'json',
      data: { uid: $('#query-uid').val() }
    });

    swal({ type: (errno === 0 ? 'info' : 'warning'), html: msg });
  } catch (err) {
    showAjaxError(err);
  }
}

async function queryByPlayerName() {
  try {
    const { errno, msg } = await fetch({
      type: 'POST',
      url: url('admin/query-uid-by-player-name'),
      dataType: 'json',
      data: { playerName: $('#query-player-name').val() }
    });

    swal({ type: (errno === 0 ? 'info' : 'warning'), html: msg });
  } catch (err) {
    showAjaxError(err);
  }
}

async function changeUserBindPlayerName() {
  try {
    const { errno, msg } = await fetch({
      type: 'POST',
      url: url('admin/change-user-bind-player-name'),
      dataType: 'json',
      data: { uid: $('#user-uid').val(), newPlayerName: $('#new-player-name').val() }
    });

    swal({ type: (errno === 0 ? 'success' : 'warning'), html: msg });
  } catch (err) {
    showAjaxError(err);
  }
}
