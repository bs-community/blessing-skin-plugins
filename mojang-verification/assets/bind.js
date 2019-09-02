blessing.event.on('mounted', () => {
  const failed = location.search.includes('mojang=failed')
    ? '<div class="callout callout-danger">' + trans('mojangVerification.verifyFailed') + '</div>'
    : ''
  document.querySelector('.col-md-5').innerHTML += `
    <form class="box box-primary" method="post" action="/mojang/verify">
      <input
        type="hidden"
        name="_token"
        value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}"
      >
      <div class="box-header with-border">
        <h3 class="box-title">${trans('mojangVerification.bind.title')}</h3>
      </div>
      <div class="box-body">
        ${failed}
        <p>${trans('mojangVerification.bind.text.line1')}</p>
        <p>${trans('mojangVerification.bind.text.line2')}</p>
        <p>${trans('mojangVerification.bind.text.line3')}</p>
        <div class="el-input el-input--suffix">
          <input class="el-input__inner" type="password" name="password">
        </div>
      </div>
      <div class="box-footer">
        <button type="submit" class="el-button el-button--primary">${trans('general.submit')}</button>
      </div>
    </form>
  `

  blessing.fetch.get('/mojang/verify').then(response => {
    if (response.code === 0) {
      document.querySelector('#m-score').textContent = response.data.score
    }
  })
})
