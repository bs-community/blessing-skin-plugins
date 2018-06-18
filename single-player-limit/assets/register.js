// eslint-disable-next-line
'use strict';function _asyncToGenerator(a){return function(){var b=a.apply(this,arguments);return new Promise(function(c,d){function f(g,h){try{var i=b[g](h),j=i.value}catch(k){return void d(k)}return i.done?void c(j):Promise.resolve(j).then(function(k){f('next',k)},function(k){f('throw',k)})}return f('next')})}}$('#register-button-2').click(function(a){a.preventDefault();var b={email:$('#email').val(),password:$('#password').val(),playerName:$('#player-name').val(),captcha:$('#captcha').val()};(function(_ref,i){var d=_ref.email,f=_ref.password,g=_ref.playerName,h=_ref.captcha;''===d?(showMsg(trans('auth.emptyEmail')),$('#email').focus()):/\S+@\S+\.\S+/.test(d)?''===f?(showMsg(trans('auth.emptyPassword')),$('#password').focus()):8>f.length||32<f.length?(showMsg(trans('auth.invalidPassword'),'warning'),$('#password').focus()):''===$('#confirm-pwd').val()?(showMsg(trans('auth.emptyConfirmPwd')),$('#confirm-pwd').focus()):f===$('#confirm-pwd').val()?''===g?(showMsg('\u89D2\u8272\u540D\u4E0D\u80FD\u4E3A\u7A7A'),$('#player-name').focus()):''===h?(showMsg(trans('auth.emptyCaptcha')),$('#captcha').focus()):i():(showMsg(trans('auth.invalidConfirmPwd'),'warning'),$('#confirm-pwd').focus()):showMsg(trans('auth.invalidEmail'),'warning')})(b,_asyncToGenerator(regeneratorRuntime.mark(function c(){var d,f,g;return regeneratorRuntime.wrap(function(i){for(;;)switch(i.prev=i.next){case 0:return i.prev=0,i.next=3,fetch({type:'POST',url:url('auth/register'),dataType:'json',data:b,beforeSend:function beforeSend(){$('#register-button').html('<i class="fa fa-spinner fa-spin"></i> '+trans('auth.registering')).prop('disabled','disabled')}});case 3:d=i.sent,f=d.errno,g=d.msg,0===f?(swal({type:'success',html:g}),setTimeout(function(){window.location=url('user')},1e3)):(showMsg(g,'warning'),refreshCaptcha(),$('#register-button').html(trans('auth.register')).prop('disabled','')),i.next=13;break;case 9:i.prev=9,i.t0=i['catch'](0),showAjaxError(i.t0),$('#register-button').html(trans('auth.register')).prop('disabled','');case 13:case'end':return i.stop();}},c,void 0,[[0,9]])})))});

// $('#register-button-2').click(e => {
//   e.preventDefault();
//
//   const data = {
//     email: $('#email').val(),
//     password: $('#password').val(),
//     playerName: $('#player-name').val(),
//     captcha: $('#captcha').val(),
//   };
//
//   (function validate({ email, password, playerName, captcha }, callback) {
//     // Massive form validation
//     if (email === '') {
//       showMsg(trans('auth.emptyEmail'));
//       $('#email').focus();
//     } else if (!/\S+@\S+\.\S+/.test(email)) {
//       showMsg(trans('auth.invalidEmail'), 'warning');
//     } else if (password === '') {
//       showMsg(trans('auth.emptyPassword'));
//       $('#password').focus();
//     } else if (password.length < 8 || password.length > 32) {
//       showMsg(trans('auth.invalidPassword'), 'warning');
//       $('#password').focus();
//     } else if ($('#confirm-pwd').val() === '') {
//       showMsg(trans('auth.emptyConfirmPwd'));
//       $('#confirm-pwd').focus();
//     } else if (password !== $('#confirm-pwd').val()) {
//       showMsg(trans('auth.invalidConfirmPwd'), 'warning');
//       $('#confirm-pwd').focus();
//     } else if (playerName === '') {
//       showMsg('角色名不能为空');
//       $('#player-name').focus();
//     } else if (captcha === '') {
//       showMsg(trans('auth.emptyCaptcha'));
//       $('#captcha').focus();
//     } else {
//       callback();
//     }
//
//     return;
//   })(data, async () => {
//     try {
//       const { errno, msg } = await fetch({
//         type: 'POST',
//         url: url('auth/register'),
//         dataType: 'json',
//         data: data,
//         beforeSend: function () {
//           $('#register-button').html(
//             '<i class="fa fa-spinner fa-spin"></i> ' + trans('auth.registering')
//           ).prop('disabled', 'disabled');
//         }
//       });
//       if (errno === 0) {
//         swal({ type: 'success', html: msg });
//
//         setTimeout(() => {
//           window.location = url('user');
//         }, 1000);
//       } else {
//         showMsg(msg, 'warning');
//         refreshCaptcha();
//         $('#register-button').html(trans('auth.register')).prop('disabled', '');
//       }
//     } catch (error) {
//       showAjaxError(error);
//       $('#register-button').html(trans('auth.register')).prop('disabled', '');
//     }
//   });
// });
