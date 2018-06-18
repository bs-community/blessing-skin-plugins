// eslint-disable-next-line
'use strict';function _asyncToGenerator(a){return function(){var b=a.apply(this,arguments);return new Promise(function(c,d){function f(g,h){try{var i=b[g](h),j=i.value}catch(k){return void d(k)}return i.done?void c(j):Promise.resolve(j).then(function(k){f('next',k)},function(k){f('throw',k)})}return f('next')})}}upload=function upload(){var c=this,a=new FormData,b=$('#file').prop('files')[0];if(a.append('name',$('#name').val()),a.append('file',b),a.append('public',!$('#private').prop('checked')),$('#type-skin').prop('checked'))a.append('type',$('#skin-type').val());else if($('#type-cape').prop('checked'))a.append('type','cape');else return toastr.info(trans('skinlib.emptyTextureType'));(function(f,g,h){void 0===g?(toastr.info(trans('skinlib.emptyUploadFile')),$('#file').focus()):''===$('#name').val()?(toastr.info(trans('skinlib.emptyTextureName')),$('#name').focus()):'image/png'===g.type?h():(toastr.warning(trans('skinlib.fileExtError')),$('#file').focus())})(a,b,_asyncToGenerator(regeneratorRuntime.mark(function d(){var f,g,h,i,j,k,l;return regeneratorRuntime.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return n.prev=0,f=void 0,n.prev=2,n.next=5,swal({text:'\u8981\u628A\u6B64\u76AE\u80A4/\u62AB\u98CE\u81EA\u52A8\u5E94\u7528\u81F3\u4F60\u7ED1\u5B9A\u7684\u89D2\u8272\u4E0A\u5417\uFF1F\uFF08\u4F60\u4E5F\u53EF\u4EE5\u7A0D\u540E\u8BBF\u95EE\u3010\u6211\u7684\u8863\u67DC\u3011\u9875\u9762\u624B\u52A8\u8BBE\u7F6E\u76AE\u80A4\uFF09',type:'question',showCancelButton:!0}).then(function(){f=!0});case 5:n.next=10;break;case 7:n.prev=7,n.t0=n['catch'](2),f=!1;case 10:return n.next=12,fetch({type:'POST',url:url('skinlib/upload'),contentType:!1,dataType:'json',data:a,processData:!1,beforeSend:function beforeSend(){$('#upload-button').html('<i class="fa fa-spinner fa-spin"></i> '+trans('skinlib.uploading')).prop('disabled','disabled')}});case 12:if(g=n.sent,h=g.errno,i=g.msg,j=g.tid,0!==h){n.next=26;break}if(!f){n.next=22;break}return k={pid:blessing.userBoundPid},$('#type-cape').prop('checked')?k['tid[cape]']=j:k['tid[skin]']=j,n.next=22,fetch({type:'POST',url:url('user/player/set'),dataType:'json',data:k});case 22:l=function(){toastr.info(trans('skinlib.redirecting')),setTimeout(function(){window.location=url('skinlib/show/'+j)},1e3)},swal({type:'success',html:i+(f?'\uFF0C\u5E76\u5DF2\u81EA\u52A8\u5E94\u7528\u81F3\u4F60\u7ED1\u5B9A\u7684\u89D2\u8272':'')}).then(l,l),n.next=29;break;case 26:return n.next=28,swal({type:'warning',html:i});case 28:$('#upload-button').html(trans('skinlib.upload')).prop('disabled','');case 29:n.next=35;break;case 31:n.prev=31,n.t1=n['catch'](0),showAjaxError(n.t1),$('#upload-button').html(trans('skinlib.upload')).prop('disabled','');case 35:case'end':return n.stop();}},d,c,[[0,31],[2,7]])})))};

// upload = function () {
//   const form = new FormData();
//   const file = $('#file').prop('files')[0];
//
//   form.append('name',   $('#name').val());
//   form.append('file',   file);
//   form.append('public', ! $('#private').prop('checked'));
//
//   if ($('#type-skin').prop('checked')) {
//     form.append('type', $('#skin-type').val());
//   } else if ($('#type-cape').prop('checked')) {
//     form.append('type', 'cape');
//   } else {
//     return toastr.info(trans('skinlib.emptyTextureType'));
//   }
//
//   (function validate(form, file, callback) {
//     if (file === undefined) {
//       toastr.info(trans('skinlib.emptyUploadFile'));
//       $('#file').focus();
//     } else if ($('#name').val() === '') {
//       toastr.info(trans('skinlib.emptyTextureName'));
//       $('#name').focus();
//     } else if (file.type !== 'image/png') {
//       toastr.warning(trans('skinlib.fileExtError'));
//       $('#file').focus();
//     } else {
//       callback();
//     }
//   })(form, file, async () => {
//     try {
//       let applyToPlayer;
//
//       try {
//         await swal({
//           text: '要把此皮肤/披风自动应用至你绑定的角色上吗？（你也可以稍后访问【我的衣柜】页面手动设置皮肤）',
//           type: 'question',
//           showCancelButton: true
//       }).then(() => {
//           applyToPlayer = true;
//         });
//       } catch (e) {
//         applyToPlayer = false;
//       }
//
//       const { errno, msg, tid } = await fetch({
//         type: 'POST',
//         url: url('skinlib/upload'),
//         contentType: false,
//         dataType: 'json',
//         data: form,
//         processData: false,
//         beforeSend: () => {
//           $('#upload-button').html(
//             '<i class="fa fa-spinner fa-spin"></i> ' + trans('skinlib.uploading')
//           ).prop('disabled', 'disabled');
//         }
//       });
//
//       if (errno === 0) {
//         if (applyToPlayer) {
//           const data = {
//             pid: blessing.userBoundPid
//           };
//
//           if ($('#type-cape').prop('checked')) {
//             data['tid[cape]'] = tid;
//           } else {
//             data['tid[skin]'] = tid;
//           }
//
//           await fetch({
//             type: 'POST',
//             url: url('user/player/set'),
//             dataType: 'json',
//             data: data
//           });
//         }
//
//         const redirect = function () {
//           toastr.info(trans('skinlib.redirecting'));
//
//           setTimeout(() => {
//             window.location = url(`skinlib/show/${tid}`);
//           }, 1000);
//         };
//
//         // Always redirect
//         swal({
//           type: 'success',
//           html: msg + (applyToPlayer ? '，并已自动应用至你绑定的角色' : '')
//         }).then(redirect, redirect);
//       } else {
//         await swal({
//           type: 'warning',
//           html: msg
//         });
//         $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
//       }
//     } catch (error) {
//       showAjaxError(error);
//       $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
//     }
//   });
// };
