;(async () => {
  try {
    const resp = await fetch('https://v1.hitokoto.cn');
    const hitokoto = document.createElement('div');
    hitokoto.style.marginTop = '3px';
    hitokoto.id = 'hitokoto'
    var obj = JSON.parse(await resp.text())

    const container = document.querySelector('.breadcrumb')
    if (container) {
      container.appendChild(hitokoto)
    } else {
      const container = document.createElement('div')
      container.className = 'breadcrumb'
      container.appendChild(hitokoto)
      document.querySelector('.content-header')?.appendChild(container)
    }
    const container_a = document.getElementById('hitokoto');
    const link = document.createElement('a');
    link.textContent = obj.hitokoto;
    link.target = '_blank';
    link.href = 'https://hitokoto.cn/?uuid=' + obj.uuid
    container_a.appendChild(link);
  } catch (_) {
    //
  }
})()
