blessing.event.on('mounted', () => {
  const failed = location.search.includes('mojang=failed')
    ? '<div class="callout callout-danger">' + trans('mojangVerification.verifyFailed') + '</div>'
    : ''
  document.querySelector('.col-md-7').innerHTML += `
    <form class="card card-primary card-outline" method="post" action="/mojang/verify">
      <input
        type="hidden"
        name="_token"
        value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}"
      >
      <div class="card-header">
        <h3 class="card-title">${trans('mojangVerification.bind.title')}</h3>
      </div>
      <div class="card-body">
        ${failed}
        <p>${trans('mojangVerification.bind.text.line1')}</p>
        <p>${trans('mojangVerification.bind.text.line2')}</p>
        <p>${trans('mojangVerification.bind.text.line3')}</p>
        <label class="form-group">
          <input class="form-control" type="password" name="password">
        </label>
      </div>
      <div class="card-footer">
        <button type="submit" class="btn bg-primary">${trans('general.submit')}</button>
      </div>
    </form>
  `

  blessing.fetch.get('/mojang/verify').then(response => {
    if (response.code === 0) {
      document.querySelector('#m-score').textContent = response.data.score
    }
  })
})
