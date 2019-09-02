blessing.event.on('i18nLoaded', i18n => {
  i18n.auth['login-link'] += blessing.locale === 'zh_CN'
    ? '。Mojang 正版用户可直接登录，无需注册。'
    : ' You could log in using your Mojang account if you have paid for Minecraft.'
})
