'use strict';

$('#upload-button').after(`
  <span onclick="showContentPolicy()" class="label bg-yellow" style="cursor: pointer; margin-left: 14px;">
    <i class="fa fa-exclamation-circle" aria-hidden="true"></i> ${ trans('report.contentPolicy') }
  </span>
`);

async function showContentPolicy() {
  const text = await fetch({
    type: 'GET',
    url: url('skinlib/content-policy')
  });

  showModal(text, trans('report.contentPolicy'));
}
