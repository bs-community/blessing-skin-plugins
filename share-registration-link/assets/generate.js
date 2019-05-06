{
  const html = `
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title">分享注册链接</h3>
    </div>
    <div class="box-body">
      <p>分享注册链接，当他人使用此链接时，您将获得积分。</p>
      <p>可用的链接：</p>
      <ul id="reg-links" style="word-wrap: break-word;"></ul>
    </div>
    <div class="box-footer">
      <button class="el-button el-button--primary" id="generate-reg-share">生成新链接</button>
    </div>
  </div>
  `

  const el = document.querySelector('.col-md-5')
  el.innerHTML += html

  const list = document.querySelector('#reg-links')
  blessing.fetch.get('/user/reg-links')
    .then(data => {
      const html = data
        .map(item => `<li>${item.url}&nbsp;<a data-code="${item.code}" href="#">删除</a></li>`)
        .join('')
      list.innerHTML += html

      list.addEventListener('click', e => {
        const code = e.target.getAttribute('data-code')
        blessing.fetch.post('/user/reg-links/remove', { code })
          .then(response => {
            if (response.code === 0) {
              e.target.parentElement.remove()
            } else {
              alert(response.message)
            }
          })
      })
    })

  const button = document.querySelector('#generate-reg-share')
  if (button) {
    button.addEventListener('click', () => {
      blessing.fetch.post('/user/reg-links')
        .then(response => {
          alert(response.message)
          if (response.code === 0) {
            const data = response.data
            list.innerHTML += `<li>${data.url}&nbsp;<a data-code="${data.code}" href="#">删除</a></li>`
          }
        })
    })
  }
}
