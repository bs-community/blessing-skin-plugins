'use strict';

const moderation = {
  ban: id => moderate(id, 'ban'),
  delete: id => moderate(id, 'delete'),
  private: id => moderate(id, 'private'),
  reject: id => moderate(id, 'reject')
}

async function moderate(id, operation) {
  const statusText = trans(
    'report.status.' + (operation === 'reject' ? 'rejected' : 'resolved')
  );

  try {
    const { errno, msg } = await fetch({
      type: 'POST',
      url: url('admin/reports'),
      dataType: 'json',
      data: { id, operation }
    });

    if (errno === 0) {
      $(`#report-${id} #status`).text(statusText);
      toastr.success(msg);
    } else {
      toastr.warning(msg);
    }
  } catch (error) {
    showAjaxError(error);
  }
}
