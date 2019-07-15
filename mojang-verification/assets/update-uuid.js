blessing.event.on('mounted', ({ el }) => {
  const mount = document.querySelector(`${el} > .row > .col-md-6:nth-child(2)`)
  const div = document.createElement('div')
  div.innerHTML = `
    <div class="box box-primary">
      <div class="box-header"><h3 class="box-title">更新 UUID</h3></div>
      <div class="box-body">
        <p>如果希望您的账号以正版的方式进入部署了 Yggdrasil API 的服务器，则可以点击下面的按钮对 UUID 进行更新。</p>
        <p>提示：如果您的正版账号对应在皮肤站的同名角色有设置了皮肤，则在游戏中以皮肤站中设置的皮肤为优先；
        如果没有，则会自动使用您已在 Mojang 上设置好的皮肤。</p>
        <p>注意：只需要更新一次就可以，无需重复操作。</p>
      </div>
      <div class="box-footer">
        <button id="update-uuid" class="el-button el-button--primary">更新 UUID</button>
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
