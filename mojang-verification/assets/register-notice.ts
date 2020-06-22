blessing.event.on('mounted', () => {
  const callout = document.createElement('div')
  callout.className = 'callout callout-info'
  callout.textContent = trans('mojang-verification.registration.notice')

  document.querySelector('.login-card-body')?.appendChild(callout)
})
