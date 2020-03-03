import Clipboard from 'clipboard'

const clipboard = new Clipboard('#dnd-button')
clipboard.on('success', () => alert('已复制！'))
clipboard.on('error', () => alert('无法访问剪贴板，请手动复制。'))

document.body.addEventListener('dragstart', e => {
  if (e.target.id === 'dnd-button') {
    const uri = 'authlib-injector:yggdrasil-server:' + e.target.dataset.clipboardText

    e.dataTransfer.setData('text/plain', uri)
    e.dataTransfer.dropEffect = 'copy'
  }
})
