/* global queue: true */

'use strict'

function getQueryString (key, defaultValue) {
  const result = window.location.search.match(new RegExp('[?&]' + key + '=([^&]+)', 'i'))

  if (result === null || result.length < 1) {
    return defaultValue
  } else {
    return result[1]
  }
}

$(document).ready(() => {
  if (queue.length > 50) {
    // 分区块导入
    importByChunk()
  } else {
    // 全部导入
    importAll()
  }
})

async function importAll () {
  // 导入队列中的全部项目
  const result = await sendChunkImportRequest(queue[0], queue[queue.length - 1])

  if (result.errno === 0) {
    // 清除队列
    queue = []
    renderImportResult(result)

    $('#import-status').removeClass('callout-info').addClass('callout-success').html(
      '<p>全部材质导入完成。</p>'
    )
  } else {
    $('#import-status').removeClass('callout-info').addClass('callout-warning').html(
      `<p>出现错误：${result.msg}</p>`
    )
  }
}

async function importByChunk () {
  if (queue.length > 50) {
    // 导入队列中的前 50 项
    const result = await sendChunkImportRequest(queue[0], queue[49])
    renderImportResult(result)

    // 清除队列
    queue = queue.slice(50)

    // 递归
    return importByChunk()
  } else {
    // 少于 50 项就全部导入
    return importAll()
  }
}

function renderImportResult (result) {
  for (let index in result) {
    let elem = $(`tr#entry-${index} > #status`)

    if (result[index] === '导入成功') {
      elem.html('<i class="fa fa-check-square-o"></i> 导入成功').addClass('text-green')
    } else {
      elem.html('<i class="fa fa-times-circle-o"></i> ' + result[index]).addClass('text-danger')
    }
  }
}

async function sendChunkImportRequest (begin, end) {
  for (let index = begin; index <= end; index++) {
    $(`tr#entry-${index} > #status`).html(`<i class="fa fa-spinner fa-spin"></i> 导入中`)
  }

  return blessing.fetch.post('/admin/batch-import/chunk-import', {
    begin,
    end,
    type: getQueryString('type'),
    uploader: getQueryString('uploader')
  })
}
