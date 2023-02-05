const button = document.querySelector<HTMLAnchorElement>('.main-button')
if (button && button.href.endsWith('/auth/register')) {
  button.textContent = globalThis.blessing.t('auth.login')
  const url = new URL(globalThis.blessing.base_url)
  url.pathname = '/auth/login'
  button.href = url.toString()
}
