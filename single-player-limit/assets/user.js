// eslint-disable-next-line
'use strict';function _asyncToGenerator(a){return function(){var b=a.apply(this,arguments);return new Promise(function(c,d){function e(f,g){try{var h=b[f](g),i=h.value}catch(j){return void d(j)}return h.done?void c(i):Promise.resolve(i).then(function(j){e('next',j)},function(j){e('throw',j)})}return e('next')})}}$('#change-player-name').click(_asyncToGenerator(regeneratorRuntime.mark(function a(){var b,c,d,e;return regeneratorRuntime.wrap(function(g){for(;;)switch(g.prev=g.next){case 0:if(b=$('#player-name').val(),blessing.allowChangePlayerName){g.next=3;break}return g.abrupt('return',swal({type:'error',html:'\u6839\u636E\u672C\u7AD9\u8BBE\u7F6E\uFF0C\u60A8\u65E0\u6CD5\u81EA\u884C\u4FEE\u6539\u7ED1\u5B9A\u7684\u89D2\u8272\u540D\u3002\u5982\u9700\u4FEE\u6539\u8BF7\u8054\u7CFB\u7AD9\u70B9\u7BA1\u7406\u5458\u3002'}));case 3:if(blessing.currentPlayerName!==b){g.next=5;break}return g.abrupt('return');case 5:return g.next=7,swal({text:'\u60A8\u786E\u5B9A\u8981\u5C06\u7ED1\u5B9A\u7684\u89D2\u8272\u540D\u4ECE ['+blessing.currentPlayerName+'] \u4FEE\u6539\u81F3 ['+b+'] \u5417\uFF1F',type:'warning',showCancelButton:!0});case 7:return g.prev=7,g.next=10,fetch({type:'POST',url:url('user/change-player-name'),dataType:'json',data:{newPlayerName:b}});case 10:c=g.sent,d=c.errno,e=c.msg,swal({type:0===d?'success':'warning',html:e}),g.next=19;break;case 16:g.prev=16,g.t0=g['catch'](7),showAjaxError(g.t0);case 19:case'end':return g.stop();}},a,this,[[7,16]])})));

// $('#change-player-name').click(async function () {
//   const newPlayerName = $('#player-name').val();
//
//   if (! blessing.allowChangePlayerName) {
//     return swal({ type: 'error', html: '根据本站设置，您无法自行修改绑定的角色名。如需修改请联系站点管理员。' });
//   }
//
//   if (blessing.currentPlayerName === newPlayerName) {
//     return;
//   }
//
//   await swal({
//     text: `您确定要将绑定的角色名从 [${blessing.currentPlayerName}] 修改至 [${newPlayerName}] 吗？`,
//     type: 'warning',
//     showCancelButton: true
//   });
//
//   try {
//     const { errno, msg } = await fetch({
//       type: 'POST',
//       url: url('user/change-player-name'),
//       dataType: 'json',
//       data: { newPlayerName }
//     });
//
//     swal({ type: (errno === 0 ? 'success' : 'warning'), html: msg });
//   } catch (err) {
//     showAjaxError(err);
//   }
// });
