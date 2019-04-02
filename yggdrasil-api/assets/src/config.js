'use strict'

$('[name=generate-key]').click(async () => {
  const { errno, msg, key } = await blessing.fetch.post(
    '/admin/plugins/config/yggdrasil-api/generate'
  )

  if (errno === 0) {
    alert('成功生成了一个新的 4096 bit OpenSSL RSA 私钥')

    $('td.value textarea').val(key)
    $('input[value=keypair]').parent().submit()
  } else {
    alert(msg)
  }
})
