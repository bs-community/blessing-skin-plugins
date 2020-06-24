import { notify, fetch } from 'blessing-skin'

document
  .querySelector('[name=generate-key]')!
  .addEventListener('click', async () => {
    type Ok = { code: 0; key: string }
    type Err = { code: 1; message: string }

    const response: Ok | Err = await fetch.post(
      '/admin/plugins/config/yggdrasil-api/generate',
    )

    if (response.code === 0) {
      notify.toast.success(trans('yggdrasil-api.key-generated'))

      document.querySelector<HTMLTextAreaElement>('td.value textarea')!.value =
        response.key
      const form = document.querySelector('input[value=keypair]')!
        .parentElement as HTMLFormElement
      form.submit()
    } else {
      notify.toast.error(response.message)
    }
  })
