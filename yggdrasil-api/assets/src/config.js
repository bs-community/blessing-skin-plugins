'use strict';

$('[name=generate-key]').click(async () => {
  try {
    const { errno, msg, key } = await fetch({
      type: 'POST',
      url: url('admin/plugins/config/yggdrasil-api/generate')
    });

    if (errno === 0) {
      toastr.success('成功生成了一个新的 4096 bit OpenSSL RSA 私钥');

      $('td.value textarea').val(key);
      $('input[value=keypair]').parent().submit();
    } else {
      swal({ type: 'warning', html: msg });
    }
  } catch (error) {
    showAjaxError(error);
  }
});

if (trans('vendor.fileinput') !== 'vendor.fileinput') {
  $.fn.fileinputLocales[blessing.locale] = trans('vendor.fileinput');
}

$('#usercache-json-file').fileinput({
  showUpload: true,
  showPreview: false,
  language: 'zh_CN',
  browseClass: 'btn btn-default',
  uploadClass: 'btn btn-primary',
  allowedFileExtensions: ['json']
});

$('body').on('click', '.fileinput-upload-button', async () => {
  const form = new FormData();
  form.append('file', $('#usercache-json-file').prop('files')[0]);

  try {
    const { errno, msg } = await fetch({
        type: 'POST',
        url: url('admin/plugins/config/yggdrasil-api/import'),
        contentType: false,
        dataType: 'json',
        data: form,
        processData: false
    });

    if (errno === 0) {
      await swal({ type: 'success', html: msg });
      location.reload();
    } else {
      swal({ type: 'warning', html: msg });
    }
  } catch (error) {
    showAjaxError(error);
  }
});
