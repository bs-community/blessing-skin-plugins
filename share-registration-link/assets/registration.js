blessing.event.on('beforeFetch', request => {
  const match = /share=(\w+)/.exec(window.location.search)
  if (match) {
    request.data.share_code = match[1]
  }
})
