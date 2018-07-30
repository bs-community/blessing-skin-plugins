'use strict';

$('button#next').click(checkImportDirAvailable);

async function checkImportDirAvailable() {
  try {
    const { errno, msg } = await fetch({
      type: 'POST',
      url: url('admin/batch-import/check-dir'),
      dataType: 'json',
      data: {
        dir: $('#dir').val(),
        gbk: $('#gbk').prop('checked')
      },
      beforeSend: () => {
        $('#next').html('<i class="fa fa-spinner fa-spin"></i> 检查目录权限中').prop('disabled', true);
      }
    });

    if (errno === 0) {
      location.href = '?step=2';
    } else {
      $('#next').html('下一步').prop('disabled', false);
      swal({ type: 'warning', html: msg });
    }
  } catch (error) {
    $('#next').html('下一步').prop('disabled', false);
    showAjaxError(error);
  }
}
