'use strict';

blessing.event.on('mounted', () => {
    $('.box-primary > .box-footer > .btn-primary').after(`
      <span onclick="showContentPolicy()" class="label bg-yellow" style="cursor: pointer; margin-left: 14px;">
        <i class="fas fa-exclamation-circle" aria-hidden="true"></i> ${ trans('report.contentPolicy') }
      </span>
    `);
});

async function showContentPolicy() {
  const { text } = await blessing.fetch.get('/skinlib/content-policy');
  blessing.notify.showModal(text, trans('report.contentPolicy'));
}
