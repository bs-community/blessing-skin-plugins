'use strict';

$('#bind-button').click(async function () {
  try {
    const { errno, msg } = await fetch({
      type: 'POST',
      url: url('user/bind-player-name'),
      dataType: 'json',
      data: { playerName: $('#player-name').val() },
      beforeSend: () => {
        $('#bind-button').html(
          '<i class="fa fa-spinner fa-spin"></i> 正在绑定'
        ).prop('disabled', 'disabled');
      }
    });

    if (errno === 0) {
      await swal({ type: 'success', html: msg });

      window.location = url('user');
    } else {
      showMsg(msg, 'warning');
      $('#bind-button').html('绑定').prop('disabled', '');
    }
  } catch (error) {
    showAjaxError(error);
    $('#bind-button').html('绑定').prop('disabled', '');
  }
});
