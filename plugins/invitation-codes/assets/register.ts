import CodeField from './CodeField.svelte'

globalThis.blessing.event.on('mounted', () => {
  const div = document.createElement('div')
  div.className = 'input-group mb-3'
  new CodeField({ target: div })

  setTimeout(() => {
    document.querySelector('.input-group:nth-child(4)')?.after(div)
  }, 0)
})
