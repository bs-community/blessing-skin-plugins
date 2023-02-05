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
  ?.addEventListener('click', async (event) => {
    const target = event.target as HTMLButtonElement
    await navigator.clipboard.writeText(target.dataset.clipboardText!)

    const originalContent = target.textContent
    target.disabled = true
    target.innerHTML = `<i class="fas fa-check mr-1"></i>${globalThis.blessing.t(
      'yggdrasil-api.copied',
    )}`

    setTimeout(() => {
      target.textContent = originalContent
      target.disabled = false
    }, 1000)
  })
