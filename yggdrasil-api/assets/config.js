// eslint-disable-next-line
'use strict';function _asyncToGenerator(a){return function(){var b=a.apply(this,arguments);return new Promise(function(c,d){function e(f,g){try{var h=b[f](g),i=h.value}catch(j){return void d(j)}return h.done?void c(i):Promise.resolve(i).then(function(j){e('next',j)},function(j){e('throw',j)})}return e('next')})}}$('[name=generate-key]').click(_asyncToGenerator(regeneratorRuntime.mark(function a(){var b,c,d,e;return regeneratorRuntime.wrap(function(g){for(;;)switch(g.prev=g.next){case 0:return g.prev=0,g.next=3,fetch({type:'POST',url:url('admin/plugins/config/yggdrasil-api/generate')});case 3:b=g.sent,c=b.errno,d=b.msg,e=b.key,0===c?(toastr.success('\u6210\u529F\u751F\u6210\u4E86\u4E00\u4E2A\u65B0\u7684 4096 bit OpenSSL RSA \u79C1\u94A5'),$('td.value textarea').val(e),$('input[value=keypair]').parent().submit()):swal({type:'warning',html:d}),g.next=13;break;case 10:g.prev=10,g.t0=g['catch'](0),showAjaxError(g.t0);case 13:case'end':return g.stop();}},a,void 0,[[0,10]])}))),'vendor.fileinput'!==trans('vendor.fileinput')&&($.fn.fileinputLocales[blessing.locale]=trans('vendor.fileinput')),$('#usercache-json-file').fileinput({showUpload:!0,showPreview:!1,language:'zh_CN',browseClass:'btn btn-default',uploadClass:'btn btn-primary',allowedFileExtensions:['json']}),$('body').on('click','.fileinput-upload-button',_asyncToGenerator(regeneratorRuntime.mark(function a(){var b,c,d,e;return regeneratorRuntime.wrap(function(g){for(;;)switch(g.prev=g.next){case 0:return b=new FormData,b.append('file',$('#usercache-json-file').prop('files')[0]),g.prev=2,g.next=5,fetch({type:'POST',url:url('admin/plugins/config/yggdrasil-api/import'),contentType:!1,dataType:'json',data:b,processData:!1});case 5:if(c=g.sent,d=c.errno,e=c.msg,0!==d){g.next=14;break}return g.next=11,swal({type:'success',html:e});case 11:location.reload(),g.next=15;break;case 14:swal({type:'warning',html:e});case 15:g.next=20;break;case 17:g.prev=17,g.t0=g['catch'](2),showAjaxError(g.t0);case 20:case'end':return g.stop();}},a,void 0,[[2,17]])})));

// $('[name=generate-key]').click(async () => {
//   try {
//     const { errno, msg, key } = await fetch({
//       type: 'POST',
//       url: url('admin/plugins/config/yggdrasil-api/generate')
//     });
//
//     if (errno === 0) {
//       toastr.success('成功生成了一个新的 4096 bit OpenSSL RSA 私钥');
//
//       $('td.value textarea').val(key);
//       $('input[value=keypair]').parent().submit();
//     } else {
//       swal({ type: 'warning', html: msg });
//     }
//   } catch (error) {
//     showAjaxError(error);
//   }
// });
//
// if (trans('vendor.fileinput') !== 'vendor.fileinput') {
//   $.fn.fileinputLocales[blessing.locale] = trans('vendor.fileinput');
// }
//
// $('#usercache-json-file').fileinput({
//   showUpload: true,
//   showPreview: false,
//   language: 'zh_CN',
//   browseClass: 'btn btn-default',
//   uploadClass: 'btn btn-primary',
//   allowedFileExtensions: ['json']
// });
//
// $('body').on('click', '.fileinput-upload-button', async () => {
//   const form = new FormData();
//   form.append('file', $('#usercache-json-file').prop('files')[0]);
//
//   try {
//     const { errno, msg } = await fetch({
//         type: 'POST',
//         url: url('admin/plugins/config/yggdrasil-api/import'),
//         contentType: false,
//         dataType: 'json',
//         data: form,
//         processData: false
//     });
//
//     if (errno === 0) {
//       await swal({ type: 'success', html: msg });
//       location.reload();
//     } else {
//       swal({ type: 'warning', html: msg });
//     }
//   } catch (error) {
//     showAjaxError(error);
//   }
// });
