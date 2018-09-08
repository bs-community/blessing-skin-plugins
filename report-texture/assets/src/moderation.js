'use strict';

const moderation = {
  ban: id => moderate(id, 'ban'),
  delete: id => moderate(id, 'delete'),
  private: id => moderate(id, 'private'),
  reject: id => moderate(id, 'reject')
};

async function moderate(id, operation) {
  const statusText = trans(
    'report.status.' + (operation === 'reject' ? 'rejected' : 'resolved')
  );

  const { errno, msg } = await bsAjax.post('/admin/reports', { id, operation });
  if (errno === 0) {
      $(`#report-${id} #status`).text(statusText);
      toastr.success(msg);
  } else {
      toastr.warning(msg);
  }
}
