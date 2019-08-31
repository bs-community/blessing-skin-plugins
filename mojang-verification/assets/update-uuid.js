blessing.event.on('mounted', ({ el }) => {
  const mount = document.querySelector(`${el} > .row > .col-md-6:nth-child(2)`)
  const div = document.createElement('div')
  div.innerHTML = `
    <div class="box box-primary">
      <div class="box-header"><h3 class="box-title">${trans('mojang_verification.update_uuid.title')}</h3></div>
      <div class="box-body">
        <p>${trans('mojang_verification.update_uuid.text.line_1')}</p>
        <div class="callout callout-info"><p>${trans('mojang_verification.update_uuid.text.line_2')}</p></div>
        <div class="callout callout-success"><p>${trans('mojang_verification.update_uuid.text.line_3')}</p></div>
      </div>
      <div class="box-footer">
        <button id="update-uuid" class="el-button el-button--primary">${trans('mojang_verification.update_uuid.button')}</button>
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
