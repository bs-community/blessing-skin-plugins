;(async () => {
  try {
    const resp = await fetch('https://v1.hitokoto.cn?encode=text')
    const hitokoto = document.createElement('div')
    hitokoto.style.marginTop = '3px'
    hitokoto.textContent = await resp.text()

    const container = document.querySelector('.breadcrumb')
    if (container) {
      container.appendChild(hitokoto)
    } else {
      const container = document.createElement('div')
      container.className = 'breadcrumb'
      container.appendChild(hitokoto)
      document.querySelector('.content-header')?.appendChild(container)
    }
  } catch (_) {
    //
  }
})()
