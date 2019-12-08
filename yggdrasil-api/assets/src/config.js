'use strict'

document.querySelector('[name=generate-key]')
  .addEventListener('click', async () => {
    const { code, message, key } = await blessing.fetch.post(
      '/admin/plugins/config/yggdrasil-api/generate'
    )

    if (code === 0) {
      blessing.notify.toast.success('成功生成了一个新的 4096 bit OpenSSL RSA 私钥')

      document.querySelector('td.value textarea').value = key
      document.querySelector('input[value=keypair]').parentElement.submit()
    } else {
      blessing.notify.toast.error(message)
    }
  })
