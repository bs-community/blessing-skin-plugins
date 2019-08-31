blessing.event.on('mounted', () => {
  const failed = location.search.includes('mojang=failed')
    ? '<div class="callout callout-danger">' + trans('mojang_verification.verify_failed') + '</div>'
    : ''
  document.querySelector('.col-md-5').innerHTML += `
    <form class="box box-primary" method="post" action="/mojang/verify">
      <input
        type="hidden"
        name="_token"
        value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}"
      >
      <div class="box-header with-border">
        <h3 class="box-title">${trans('mojang_verification.bind.title')}</h3>
      </div>
      <div class="box-body">
        ${failed}
        <p>${trans('mojang_verification.bind.text.line_1')}</p>
        <p>${trans('mojang_verification.bind.text.line_2')}</p>
        <p>${trans('mojang_verification.bind.text.line_3')}</p>
        <div class="el-input el-input--suffix">
          <input class="el-input__inner" type="password" name="password">
        </div>
      </div>
      <div class="box-footer">
        <button type="submit" class="el-button el-button--primary">${trans('mojang_verification.bind.submit')}</button>
      </div>
    </form>
  `

  blessing.fetch.get('/mojang/verify').then(response => {
    if (response.code === 0) {
      document.querySelector('#m-score').textContent = response.data.score
    }
  })
})
