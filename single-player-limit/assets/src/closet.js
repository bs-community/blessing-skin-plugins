'use strict';

$('[data-target="#modal-use-as"]').attr('data-toggle', '').click(setTextureOfUniquePlayer);

async function setTextureOfUniquePlayer() {
  const $indicator = $('#textures-indicator');
  const skin = $indicator.data('skin'),
      cape = $indicator.data('cape');

  if (!skin && !cape) {
    toastr.info(trans('user.emptySelectedTexture'));
  } else {
    try {
      const { errno, msg } = await fetch({
        type: 'POST',
        url: url('user/player/set'),
        dataType: 'json',
        data: {
          'pid': blessing.userBoundPid,
          'tid[skin]': skin,
          'tid[cape]': cape
        }
      });

      if (errno === 0) {
        swal({ type: 'success', html: msg });
        $('#modal-use-as').modal('hide');
      } else {
        toastr.warning(msg);
      }
    } catch (error) {
      showAjaxError(error);
    }
  }
}
