'use strict';

window.bsEmitter.on('mounted', () => {
    $('.box-primary > .box-footer > .btn-primary').after(`
      <span onclick="showContentPolicy()" class="label bg-yellow" style="cursor: pointer; margin-left: 14px;">
        <i class="fas fa-exclamation-circle" aria-hidden="true"></i> ${ trans('report.contentPolicy') }
      </span>
    `);
});

async function showContentPolicy() {
  const { text } = await bsAjax.get('/skinlib/content-policy');
  showModal(text, trans('report.contentPolicy'));
}
