'use strict'

$('button#next').click(checkImportDirAvailable)

async function checkImportDirAvailable () {
  $('#next').html('<i class="fa fa-spinner fa-spin"></i> 检查目录权限中').prop('disabled', true)

  const { errno, msg } = await blessing.fetch.post('/admin/batch-import/check-dir', {
    dir: $('#dir').val(),
    gbk: $('#gbk').prop('checked')
  })

  if (errno === 0) {
    window.location.href = '?step=2'
  } else {
    $('#next').text('下一步').prop('disabled', false)
    alert(msg)
  }
}
