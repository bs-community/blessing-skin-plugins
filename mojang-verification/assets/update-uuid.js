blessing.event.on('mounted', ({ el }) => {
  const mount = document.querySelector(`${el} > .row > .col-md-6:nth-child(2)`)
  const div = document.createElement('div')
  div.innerHTML = `
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">${trans('mojangVerification.updateUuid.title')}</h3>
      </div>
      <div class="card-body">
        <p>${trans('mojangVerification.updateUuid.text.line1')}</p>
        <div class="callout callout-info">
          <p>${trans('mojangVerification.updateUuid.text.line2')}</p>
        </div>
        <div class="callout callout-success"
          <p>${trans('mojangVerification.updateUuid.text.line3')}</p>
        </div>
      </div>
      <div class="card-footer">
        <button id="update-uuid" class="btn bg-primary">
          ${trans('mojangVerification.updateUuid.button')}
        </button>
      </div>
    </div>
  `
  div.querySelector('#update-uuid').addEventListener('click', () => {
    blessing.fetch.post('/mojang/update-uuid').then(body => {
      if (body.code === 0) {
        blessing.ui.message.success(body.message)
      } else {
        blessing.ui.message.error(body.message)
      }
    })
  })
  mount.prepend(div)
})
