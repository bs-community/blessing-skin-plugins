import { fetch, notify } from 'blessing-skin'

document.querySelector('#update-uuid')?.addEventListener('click', async () => {
  const { code, message }: { code: null; message: string } = await fetch.post(
    '/mojang/update-uuid',
  )
  const { toast } = notify
  if (code === 0) {
    toast.success(message)
  } else {
    toast.error(message)
  }
})
