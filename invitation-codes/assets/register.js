'use strict';

$('input#captcha').closest('.row').before(
  '<div class="form-group has-feedback">'+
    '<input id="invitation-code" type="text" class="form-control" placeholder="邀请码">'+
    '<span class="glyphicon glyphicon-inbox form-control-feedback"></span>'+
  '</div>'
);

var originalFetchFunction = fetch;

// 对传入的参数动点手脚，插入邀请码的值
fetch = function (param) {
  param.data.invitationCode = $('#invitation-code').val();

  return originalFetchFunction(param);
}
