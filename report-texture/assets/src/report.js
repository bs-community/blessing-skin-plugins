'use strict';

window.bsEmitter.on('mounted', () => {
    $('.col-md-4 .box-primary .box-header').append(`
      <div class="box-tools pull-right" style="position: initial; cursor: pointer;">
        <span id="report-texture" class="label label-warning">
          <i class="fas fa-flag" aria-hidden="true"></i> ${ trans('report.reportThisTexture') }
        </span>
      </div>
    `);
});

$('body').on('click', '#report-texture', () => {
  const tid = location.pathname.match(/skinlib\/show\/(\d*)/)[1];

  if (! tid) {
    return toastr.warning(trans('report.invalidTid'));
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

  if (typeof showContentPolicy !== 'undefined') {
    dom += `
      <div class="callout callout-warning">
        <p>
          ${ trans('report.contentPolicyNotice', {
            link: `<a href="javascript:;" onclick="showContentPolicy()">${ trans('report.contentPolicy') }</a>`
          }) }
        </p>
      </div>
    `;
  }

  showModal(dom, `${trans('report.tid')}: ${tid}`, 'default', {
    callback: `reportTexture(${ tid })`
  });
});

async function reportTexture(tid) {
  const reason = $('#report-form input').val();

  $('.modal-footer button').html(
      `<i class="fas fa-spinner fa-spin"></i> ${ trans('report.submitting') }`
  ).prop('disabled', true);

  const { errno, msg } = await bsAjax.post('/skinlib/report', { tid, reason });
  $('.modal').modal('hide');
  swal({ type: errno === 0 ? 'success' : 'warning', text: msg });
}
