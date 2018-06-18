// eslint-disable-next-line
'use strict';var setTextureOfUniquePlayer=function(){var a=_asyncToGenerator(regeneratorRuntime.mark(function b(){var c,d,e,f,g,h;return regeneratorRuntime.wrap(function(j){for(;;)switch(j.prev=j.next){case 0:if(c=$('#textures-indicator'),d=c.data('skin'),e=c.data('cape'),d||e){j.next=6;break}toastr.info(trans('user.emptySelectedTexture')),j.next=18;break;case 6:return j.prev=6,j.next=9,fetch({type:'POST',url:url('user/player/set'),dataType:'json',data:{pid:blessing.userBoundPid,'tid[skin]':d,'tid[cape]':e}});case 9:f=j.sent,g=f.errno,h=f.msg,0===g?(swal({type:'success',html:h}),$('#modal-use-as').modal('hide')):toastr.warning(h),j.next=18;break;case 15:j.prev=15,j.t0=j['catch'](6),showAjaxError(j.t0);case 18:case'end':return j.stop();}},b,this,[[6,15]])}));return function(){return a.apply(this,arguments)}}();function _asyncToGenerator(a){return function(){var b=a.apply(this,arguments);return new Promise(function(c,d){function e(f,g){try{var h=b[f](g),i=h.value}catch(j){return void d(j)}return h.done?void c(i):Promise.resolve(i).then(function(j){e('next',j)},function(j){e('throw',j)})}return e('next')})}}

// async function setTextureOfUniquePlayer() {
//   const $indicator = $('#textures-indicator');
//   const skin = $indicator.data('skin'),
//       cape = $indicator.data('cape');
//
//   if (!skin && !cape) {
//     toastr.info(trans('user.emptySelectedTexture'));
//   } else {
//     try {
//       const { errno, msg } = await fetch({
//         type: 'POST',
//         url: url('user/player/set'),
//         dataType: 'json',
//         data: {
//           'pid': blessing.userBoundPid,
//           'tid[skin]': skin,
//           'tid[cape]': cape
//         }
//       });
//
//       if (errno === 0) {
//         swal({ type: 'success', html: msg });
//         $('#modal-use-as').modal('hide');
//       } else {
//         toastr.warning(msg);
//       }
//     } catch (error) {
//       showAjaxError(error);
//     }
//   }
// }
