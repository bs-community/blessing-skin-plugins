blessing.event.on('mounted', () => {
  document.querySelector('.col-md-5').innerHTML += `
    <form class="box box-primary" method="post" action="/verify-mojang">
      <input
        type="hidden"
        name="_token"
        value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}"
      >
      <div class="box-header with-border">
        <h3 class="box-title">正版绑定</h3>
      </div>
      <div class="box-body">
        <p>如果您拥有正版 Minecraft 账号，可在下方输入密码进行验证并绑定。</p>
        <p>请放心，我们不会保存您的密码。如果不放心，可以临时修改您的正版账号密码，并在验证后改回来。</p>
        <p>如果验证成功，您将获得正版账号对应的角色，并可获得 <span id="m-score">0</span> 积分。</p>
        <div class="el-input el-input--suffix">
          <input class="el-input__inner" type="password" name="password">
        </div>
      </div>
      <div class="box-footer">
        <button type="submit" class="el-button el-button--primary">提交</button>
      </div>
    </form>
  `

  blessing.fetch.get('/verify-mojang').then(response => {
    if (response.code === 0) {
      document.querySelector('#m-score').textContent = response.data.score
    }
  })
})
