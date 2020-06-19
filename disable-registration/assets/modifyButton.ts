{
  const button = document.querySelector<HTMLAnchorElement>('.main-button')
  if (button) {
    button.textContent = trans('auth.login')
    const url = new URL(blessing.base_url)
    url.pathname = '/auth/login'
    button.href = url.toString()
  }
}
