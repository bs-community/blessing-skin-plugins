'use strict';

upload = function () {
  const form = new FormData();
  const file = $('#file').prop('files')[0];

  form.append('name',   $('#name').val());
  form.append('file',   file);
  form.append('public', ! $('#private').prop('checked'));

  if ($('#type-skin').prop('checked')) {
    form.append('type', $('#skin-type').val());
  } else if ($('#type-cape').prop('checked')) {
    form.append('type', 'cape');
  } else {
    return toastr.info(trans('skinlib.emptyTextureType'));
  }

  (function validate(form, file, callback) {
    if (file === undefined) {
      toastr.info(trans('skinlib.emptyUploadFile'));
      $('#file').focus();
    } else if ($('#name').val() === '') {
      toastr.info(trans('skinlib.emptyTextureName'));
      $('#name').focus();
    } else if (file.type !== 'image/png') {
      toastr.warning(trans('skinlib.fileExtError'));
      $('#file').focus();
    } else {
      callback();
    }
  })(form, file, async () => {
    try {
      let applyToPlayer;

      try {
        await swal({
          text: '要把此皮肤/披风自动应用至你绑定的角色上吗？（你也可以稍后访问【我的衣柜】页面手动设置皮肤）',
          type: 'question',
          showCancelButton: true,
          confirmButtonText: '是',
          cancelButtonText: '否'
      }).then(() => {
          applyToPlayer = true;
        });
      } catch (e) {
        applyToPlayer = false;
      }

      const { errno, msg, tid } = await fetch({
        type: 'POST',
        url: url('skinlib/upload'),
        contentType: false,
        dataType: 'json',
        data: form,
        processData: false,
        beforeSend: () => {
          $('#upload-button').html(
            '<i class="fa fa-spinner fa-spin"></i> ' + trans('skinlib.uploading')
          ).prop('disabled', 'disabled');
        }
      });

      if (errno === 0) {
        if (applyToPlayer) {
          const data = {
            pid: blessing.userBoundPid
          };

          if ($('#type-cape').prop('checked')) {
            data['tid[cape]'] = tid;
          } else {
            data['tid[skin]'] = tid;
          }

          await fetch({
            type: 'POST',
            url: url('user/player/set'),
            dataType: 'json',
            data: data
          });
        }

        const redirect = function () {
          toastr.info(trans('skinlib.redirecting'));

          setTimeout(() => {
            window.location = url(`skinlib/show/${tid}`);
          }, 1000);
        };

        // Always redirect
        swal({
          type: 'success',
          html: msg + (applyToPlayer ? '，并已自动应用至你绑定的角色' : '')
        }).then(redirect, redirect);
      } else {
        await swal({
          type: 'warning',
          html: msg
        });
        $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
      }
    } catch (error) {
      showAjaxError(error);
      $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
    }
  });
};
