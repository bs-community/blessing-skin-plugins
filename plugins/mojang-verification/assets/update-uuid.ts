document.querySelector('#update-uuid')?.addEventListener('click', async () => {
  const { code, message }: { code: null; message: string } =
    await globalThis.blessing.fetch.post('/mojang/update-uuid')
  const { toast } = globalThis.blessing.notify
  if (code === 0) {
    toast.success(message)
  } else {
    toast.error(message)
  }
})
