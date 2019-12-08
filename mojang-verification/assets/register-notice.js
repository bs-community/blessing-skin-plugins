blessing.event.on('mounted', () => {
  const notice = blessing.locale === 'zh_CN'
    ? 'Mojang 正版用户可直接登录，无需注册。'
    : 'You could log in using your Mojang account if you have paid for Minecraft.'

  document.querySelector('.login-card-body').innerHTML += `
    <div class="callout callout-info">${notice}</div>
  `
})
