const { notify, t } = globalThis.blessing

document
  .querySelector('[name=generate-key]')!
  .addEventListener('click', async () => {
    type Ok = { code: 0; key: string }
    type Err = { code: 1; message: string }

    const response: Ok | Err = await globalThis.blessing.fetch.post(
      '/admin/plugins/config/yggdrasil-connect/generate',
    )

    if (response.code === 0) {
      notify.toast.success(t('yggdrasil-connect.key-generated'))

      document.querySelector<HTMLTextAreaElement>('td.value textarea')!.value =
        response.key
      const form = document.querySelector('input[value=keypair]')!
        .parentElement as HTMLFormElement
      form.submit()
    } else {
      notify.toast.error(response.message)
    }
  })
