document.querySelector('#update-uuid')?.addEventListener('click', async () => {
  const {
    code,
    message,
  }: { code: null; message: string } = await blessing.fetch.post(
    '/mojang/update-uuid',
  )
  const { toast } = blessing.notify
  if (code === 0) {
    toast.success(message)
  } else {
    toast.error(message)
  }
})
