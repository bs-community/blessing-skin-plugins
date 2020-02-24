function getQueryString(key, defaultValue) {
  const result = window.location.search.match(new RegExp('[?&]' + key + '=([^&]+)', 'i'));

  if (result === null || result.length < 1) {
    return defaultValue;
  } else {
    return result[1];
  }
}

if (getQueryString('step', '1') === '3') {
  document.addEventListener('DOMContentLoaded', event => {
    if (queue.length > 50) {
      // 分区块导入
      importByChunk();
    } else {
      // 全部导入
      importAll();
    }
  });
}

async function importAll() {
  // 导入队列中的全部项目
  const result = await sendChunkImportRequest(queue[0], queue[queue.length - 1]);
  const status = document.querySelector('#import-status');

  if (result.code === 0) {
    // 清除队列
    queue = [];
    renderImportResult(result);
    status.classList.replace('callout-info', 'callout-success');
    status.textContent = '全部材质导入完成。';
  } else {
    status.classList.replace('callout-info', 'callout-warning');
    status.textContent = `<p>出现错误：${result.message}</p>`;
  }
}

async function importByChunk() {
  if (queue.length > 50) {
    // 导入队列中的前 50 项
    const result = await sendChunkImportRequest(queue[0], queue[49]);
    renderImportResult(result); // 清除队列

    queue = queue.slice(50); // 递归

    return importByChunk();
  } else {
    // 少于 50 项就全部导入
    return importAll();
  }
}

function renderImportResult(result) {
  for (let index in result) {
    let elem = document.querySelector(`tr#entry-${index} > #status`);

    if (index === 'code') return;
    if (result[index] === '导入成功') {
      elem.innerHTML = '<i class="fa fa-check-square-o"></i> 导入成功';
      elem.classList.add('text-green');
    } else {
      elem.innerHTML = '<i class="fa fa-times-circle-o"></i> ' + result[index];
      elem.classList.add('text-danger');
    }
  }
}

async function sendChunkImportRequest(begin, end) {
  for (let index = begin; index <= end; index++) {
    document.querySelector(`tr#entry-${index} > #status`).innerHTML = '<i class="fa fa-spinner fa-spin"></i> 导入中';
  }

  return blessing.fetch.post('/admin/batch-import/chunk-import', {
    begin,
    end,
    type: getQueryString('type'),
    uploader: getQueryString('uploader')
  });
}