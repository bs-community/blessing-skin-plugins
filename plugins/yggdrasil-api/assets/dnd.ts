import { t } from 'blessing-skin'

document.body.addEventListener('dragstart', (event) => {
  if (!event.target) {
    return
  }

  const target = event.target as HTMLElement

  if (target.id === 'ygg-dnd-button') {
    const uri =
      'authlib-injector:yggdrasil-server:' +
      encodeURIComponent(target.dataset.clipboardText!)

    if (event.dataTransfer) {
      event.dataTransfer.setData('text/plain', uri)
      event.dataTransfer.dropEffect = 'copy'
    }
  }
})

document
  .querySelector<HTMLButtonElement>('#ygg-dnd-button')
  ?.addEventListener('click', (event) => {
    const target = event.target as HTMLButtonElement
    const content = target.dataset.clipboardText!

    const input = document.createElement('input')
    input.style.visibility = 'none'
    input.value = content
    document.body.appendChild(input)
    input.select()
    document.execCommand('copy')

    input.remove()
    const originalContent = target.textContent
    target.disabled = true
    target.innerHTML = `<i class="fas fa-check mr-1"></i>${t(
      'yggdrasil-api.copied',
    )}`

    setTimeout(() => {
      target.textContent = originalContent
      target.disabled = false
    }, 1000)
  })
