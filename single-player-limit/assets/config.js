// eslint-disable-next-line
'use strict';var queryByUid=function(){var a=_asyncToGenerator(regeneratorRuntime.mark(function b(){var c,d,e;return regeneratorRuntime.wrap(function(g){for(;;)switch(g.prev=g.next){case 0:return g.prev=0,g.next=3,fetch({type:'POST',url:url('admin/query-player-name-by-uid'),dataType:'json',data:{uid:$('#query-uid').val()}});case 3:c=g.sent,d=c.errno,e=c.msg,swal({type:0===d?'info':'warning',html:e}),g.next=12;break;case 9:g.prev=9,g.t0=g['catch'](0),showAjaxError(g.t0);case 12:case'end':return g.stop();}},b,this,[[0,9]])}));return function(){return a.apply(this,arguments)}}(),queryByPlayerName=function(){var a=_asyncToGenerator(regeneratorRuntime.mark(function b(){var c,d,e;return regeneratorRuntime.wrap(function(g){for(;;)switch(g.prev=g.next){case 0:return g.prev=0,g.next=3,fetch({type:'POST',url:url('admin/query-uid-by-player-name'),dataType:'json',data:{playerName:$('#query-player-name').val()}});case 3:c=g.sent,d=c.errno,e=c.msg,swal({type:0===d?'info':'warning',html:e}),g.next=12;break;case 9:g.prev=9,g.t0=g['catch'](0),showAjaxError(g.t0);case 12:case'end':return g.stop();}},b,this,[[0,9]])}));return function(){return a.apply(this,arguments)}}(),changeUserBindPlayerName=function(){var a=_asyncToGenerator(regeneratorRuntime.mark(function b(){var c,d,e;return regeneratorRuntime.wrap(function(g){for(;;)switch(g.prev=g.next){case 0:return g.prev=0,g.next=3,fetch({type:'POST',url:url('admin/change-user-bind-player-name'),dataType:'json',data:{uid:$('#user-uid').val(),newPlayerName:$('#new-player-name').val()}});case 3:c=g.sent,d=c.errno,e=c.msg,swal({type:0===d?'success':'warning',html:e}),g.next=12;break;case 9:g.prev=9,g.t0=g['catch'](0),showAjaxError(g.t0);case 12:case'end':return g.stop();}},b,this,[[0,9]])}));return function(){return a.apply(this,arguments)}}();function _asyncToGenerator(a){return function(){var b=a.apply(this,arguments);return new Promise(function(c,d){function e(f,g){try{var h=b[f](g),i=h.value}catch(j){return void d(j)}return h.done?void c(i):Promise.resolve(i).then(function(j){e('next',j)},function(j){e('throw',j)})}return e('next')})}}

// async function queryByUid() {
//   try {
//     const { errno, msg } = await fetch({
//       type: 'POST',
//       url: url('admin/query-player-name-by-uid'),
//       dataType: 'json',
//       data: { uid: $('#query-uid').val() }
//     });
//
//     swal({ type: (errno === 0 ? 'info' : 'warning'), html: msg });
//   } catch (err) {
//     showAjaxError(err);
//   }
// }
//
// async function queryByPlayerName() {
//   try {
//     const { errno, msg } = await fetch({
//       type: 'POST',
//       url: url('admin/query-uid-by-player-name'),
//       dataType: 'json',
//       data: { playerName: $('#query-player-name').val() }
//     });
//
//     swal({ type: (errno === 0 ? 'info' : 'warning'), html: msg });
//   } catch (err) {
//     showAjaxError(err);
//   }
// }
//
// async function changeUserBindPlayerName() {
//   try {
//     const { errno, msg } = await fetch({
//       type: 'POST',
//       url: url('admin/change-user-bind-player-name'),
//       dataType: 'json',
//       data: { uid: $('#user-uid').val(), newPlayerName: $('#new-player-name').val() }
//     });
//
//     swal({ type: (errno === 0 ? 'success' : 'warning'), html: msg });
//   } catch (err) {
//     showAjaxError(err);
//   }
// }
