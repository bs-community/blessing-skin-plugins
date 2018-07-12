'use strict';

$('input#nickname').val('whatever').parent().hide();

$('input#nickname').parent().after(`
  <div class="form-group has-feedback" title="绑定您在游戏中的角色名" data-placement="top" data-toggle="tooltip">
    <input id="player-name" type="text" class="form-control" placeholder="角色名">
    <span class="glyphicon glyphicon-pencil form-control-feedback"></span>
  </div>
`);

var originalFetchFunctionSpl = fetch;

fetch = function (param) {
  param.data.playerName = $('#player-name').val();

  return originalFetchFunctionSpl(param);
}
