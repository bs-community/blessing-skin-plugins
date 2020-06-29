import { base_url, t } from 'blessing-skin'

const button = document.querySelector<HTMLAnchorElement>('.main-button')
if (button && button.href.endsWith('/auth/register')) {
  button.textContent = t('auth.login')
  const url = new URL(base_url)
  url.pathname = '/auth/login'
  button.href = url.toString()
}
