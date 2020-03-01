'use strict'

blessing.event.on('mounted', ({ el }) => {
   $(el).find('.input-group').first().before('<div class="input-group mb-3"><input class="form-control" id="invitation-code" required="required" type="text" placeholder="邀请码"> <div class="input-group-append"><div class="input-group-text"><span class="fas fa-id-card"></span></div></div></div>');
   	
})

// 插入邀请码的值
blessing.event.on('beforeFetch', request => {
  request.data.invitationCode = document.querySelector('#invitation-code').value
})
