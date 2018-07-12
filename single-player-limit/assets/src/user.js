'use strict';

$('#change-player-name').click(async function () {
  const newPlayerName = $('#player-name').val();

  if (! blessing.allowChangePlayerName) {
    return swal({ type: 'error', html: '根据本站设置，您无法自行修改绑定的角色名。如需修改请联系站点管理员。' });
  }

  if (blessing.currentPlayerName === newPlayerName) {
    return;
  }

  await swal({
    text: `您确定要将绑定的角色名从 [${blessing.currentPlayerName}] 修改至 [${newPlayerName}] 吗？`,
    type: 'warning',
    showCancelButton: true
  });

  try {
    const { errno, msg } = await fetch({
      type: 'POST',
      url: url('user/change-player-name'),
      dataType: 'json',
      data: { newPlayerName }
    });

    swal({ type: (errno === 0 ? 'success' : 'warning'), html: msg });
  } catch (err) {
    showAjaxError(err);
  }
});
