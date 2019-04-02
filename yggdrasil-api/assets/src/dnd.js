'use strict'

{
  const yggdrasilApiRoot = `${blessing.base_url}/api/yggdrasil`
  const dom = `
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title">快速配置启动器</h3>
        </div><!-- /.box-header -->
        <div class="box-body">
          <p>本站的 Yggdrasil API 认证服务器地址：<code>${yggdrasilApiRoot}</code></p>
          <p>点击下方按钮复制 API 地址，或者将按钮拖动至启动器的任意界面即可快速添加认证服务器（目前仅支持 HMCL 3.1.74 及以上版本）。</p>
        </div><!-- /.box-body -->
        <div class="box-footer">
          <a id="dnd-button" class="btn btn-primary" draggable="true" data-clipboard-text="${yggdrasilApiRoot}">将此按钮拖动至启动器</a>
          <a class="btn" target="_blank" href="https://github.com/bs-community/yggdrasil-api/wiki/0x03-%E9%85%8D%E5%90%88-authlib-injector-%E4%BD%BF%E7%94%A8#%EF%B8%8F-%E9%85%8D%E7%BD%AE%E5%90%AF%E5%8A%A8%E5%99%A8">启动器配置教程</a>
        </div>
      </div><!-- /.box -->
    `
  const div = document.createElement('div')
  div.innerHTML = dom
  blessing.event.on('mounted', () =>
    document
      .querySelector('section.content > .row > .col-md-7')
      .appendChild(div)
  )

  const clipboard = new ClipboardJS('#dnd-button')
  clipboard.on('success', () => alert('已复制！'))
  clipboard.on('error', () => alert('无法访问剪贴板，请手动复制。'))

  document.body.addEventListener('dragstart', e => {
    if (e.target.id === 'dnd-button') {
      const uri =
        'authlib-injector:yggdrasil-server:' +
        encodeURIComponent(yggdrasilApiRoot)

      e.dataTransfer.setData('text/plain', uri)
      e.dataTransfer.dropEffect = 'copy'
    }
  })
}
