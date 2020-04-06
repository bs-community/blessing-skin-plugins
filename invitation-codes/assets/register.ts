blessing.event.on('mounted', () => {
  const div = document.createElement('div')
  div.className = 'input-group mb-3'
  div.innerHTML = `
    <input
      type="text"
      required
      class="form-control"
      placeholder="邀请码"
      id="invitation-code"
    >
    <div class="input-group-append">
      <div class="input-group-text">
        <i class="fas fa-receipt"></i>
      </div>
    </div>
  `
  setTimeout(() => {
    document.querySelector('.input-group:nth-child(4)')?.after(div)
  }, 0)
})

// 插入邀请码的值
blessing.event.on(
  'beforeFetch',
  (request: { data: Record<string, string> }) => {
    request.data.invitationCode =
      document.querySelector<HTMLInputElement>('#invitation-code')?.value || ''
  },
)
