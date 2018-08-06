'use strict';

$('.col-md-4 .box-primary .box-header').append(`
  <div class="box-tools pull-right" style="position: initial; cursor: pointer;">
    <span id="report-texture" class="label label-warning">
      <i class="fa fa-flag" aria-hidden="true"></i> ${ trans('report.reportThisTexture') }
    </span>
  </div>
`);

$('body').on('click', '#report-texture', () => {
  const tid = location.pathname.match(/skinlib\/show\/(\d*)/)[1];

  if (! tid) {
    return alert(trans('report.invalidTid'));
  }

  $('.modal').each(function () {
    if ($(this).css('display') == 'none') $(this).remove();
  });

  let dom = `
    <div class="form-group" id="report-form">
      <label for="tid">${ trans('report.reason') }</label>
      <input id="tid" class="form-control" type="text" placeholder="${ trans('report.reasonPlaceholder') }">
    </div>
  `;

  const score = blessing.reporterScoreModification;

  if (score !== 0) {
    const notice = score > 0 ?
      trans('report.notice.positive', { score }) :
      trans('report.notice.negative', { score: -score });

    dom += `<div class="callout callout-info"><p>${ notice }</p></div>`;
  }

  showModal(dom, `${trans('report.tid')}: ${tid}`, 'default', {
    callback: `reportTexture(${ tid })`
  });
});

async function reportTexture(tid) {
  const reason = $('#report-form input').val();

  try {
    const { errno, msg } = await fetch({
      type: 'POST',
      url: url('skinlib/report'),
      dataType: 'json',
      data: { tid, reason },
      beforeSend: () => {
        $('.modal-footer button').html(
          `<i class="fa fa-spinner fa-spin"></i> ${ trans('report.submitting') }`
        ).prop('disabled', true);
      }
    });

    $('.modal').modal('hide');

    swal({
      type: (errno === 0 ? 'success' : 'warning'),
      html: msg
    });
  } catch (error) {
    showAjaxError(error);
    $('.modal-footer button').html('OK').prop('disabled', false);
  }
}
