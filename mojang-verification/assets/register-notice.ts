blessing.event.on('mounted', () => {
  const callout = document.createElement('div')
  callout.className = 'callout callout-info'
  callout.textContent =
    blessing.locale === 'zh_CN'
      ? 'Mojang 正版用户可直接登录，无需注册。'
      : 'You could log in using your Mojang account if you have paid for Minecraft.'

  document.querySelector('.login-card-body')?.appendChild(callout)
})
