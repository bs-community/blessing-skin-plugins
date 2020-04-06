document
  .querySelector('[name=generate-key]')!
  .addEventListener('click', async () => {
    type Ok = { code: 0; key: string }
    type Err = { code: 1; message: string }

    const response: Ok | Err = await blessing.fetch.post(
      '/admin/plugins/config/yggdrasil-api/generate',
    )

    if (response.code === 0) {
      blessing.notify.toast.success(
        '成功生成了一个新的 4096 bit OpenSSL RSA 私钥',
      )

      document.querySelector<HTMLTextAreaElement>('td.value textarea')!.value =
        response.key
      const form = document.querySelector('input[value=keypair]')!
        .parentElement as HTMLFormElement
      form.submit()
    } else {
      blessing.notify.toast.error(response.message)
    }
  })
