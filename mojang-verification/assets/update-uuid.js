document.querySelector('#update-uuid').addEventListener('click', () => {
  blessing.fetch.post('/mojang/update-uuid').then(body => {
    const toast = blessing.notify.toast
    if (body.code === 0) {
      toast.success(body.message)
    } else {
      toast.error(body.message)
    }
  })
})
