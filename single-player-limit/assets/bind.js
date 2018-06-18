// eslint-disable-next-line
'use strict';function _asyncToGenerator(a){return function(){var b=a.apply(this,arguments);return new Promise(function(c,d){function e(f,g){try{var h=b[f](g),i=h.value}catch(j){return void d(j)}return h.done?void c(i):Promise.resolve(i).then(function(j){e('next',j)},function(j){e('throw',j)})}return e('next')})}}$('#bind-button').click(_asyncToGenerator(regeneratorRuntime.mark(function a(){var b,c,d;return regeneratorRuntime.wrap(function(f){for(;;)switch(f.prev=f.next){case 0:return f.prev=0,f.next=3,fetch({type:'POST',url:url('user/bind-player-name'),dataType:'json',data:{playerName:$('#player-name').val()},beforeSend:function beforeSend(){$('#bind-button').html('<i class="fa fa-spinner fa-spin"></i> \u6B63\u5728\u7ED1\u5B9A').prop('disabled','disabled')}});case 3:if(b=f.sent,c=b.errno,d=b.msg,0!==c){f.next=12;break}return f.next=9,swal({type:'success',html:d});case 9:window.location=url('user'),f.next=14;break;case 12:showMsg(d,'warning'),$('#bind-button').html('\u7ED1\u5B9A').prop('disabled','');case 14:f.next=20;break;case 16:f.prev=16,f.t0=f['catch'](0),showAjaxError(f.t0),$('#bind-button').html('\u7ED1\u5B9A').prop('disabled','');case 20:case'end':return f.stop();}},a,this,[[0,16]])})));

// $('#bind-button').click(async function () {
//   try {
//     const { errno, msg } = await fetch({
//       type: 'POST',
//       url: url('user/bind-player-name'),
//       dataType: 'json',
//       data: { playerName: $('#player-name').val() },
//       beforeSend: () => {
//         $('#bind-button').html(
//           '<i class="fa fa-spinner fa-spin"></i> 正在绑定'
//         ).prop('disabled', 'disabled');
//       }
//     });
//
//     if (errno === 0) {
//       await swal({ type: 'success', html: msg });
//
//       window.location = url('user');
//     } else {
//       showMsg(msg, 'warning');
//       $('#bind-button').html('绑定').prop('disabled', '');
//     }
//   } catch (error) {
//     showAjaxError(error);
//     $('#bind-button').html('绑定').prop('disabled', '');
//   }
// });
