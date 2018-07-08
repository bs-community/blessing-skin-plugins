// eslint-disable-next-line
'use strict';var dom='<div class="box box-success">\n    <div class="box-header with-border">\n      <h3 class="box-title">\u5FEB\u901F\u914D\u7F6E\u542F\u52A8\u5668</h3>\n    </div><!-- /.box-header -->\n    <div class="box-body">\n      <p>\u672C\u7AD9\u7684 Yggdrasil API \u8BA4\u8BC1\u670D\u52A1\u5668\u5730\u5740\uFF1A<code>'+url('api/yggdrasil')+'</code></p>\n      <p>\u70B9\u51FB\u4E0B\u65B9\u6309\u94AE\u590D\u5236 API \u5730\u5740\uFF0C\u6216\u8005\u5C06\u6309\u94AE\u62D6\u52A8\u81F3\u542F\u52A8\u5668\u7684\u4EFB\u610F\u754C\u9762\u5373\u53EF\u5FEB\u901F\u6DFB\u52A0\u8BA4\u8BC1\u670D\u52A1\u5668\uFF08\u76EE\u524D\u4EC5\u652F\u6301 HMCL 3.1.74 \u53CA\u4EE5\u4E0A\u7248\u672C\uFF09\u3002</p>\n    </div><!-- /.box-body -->\n    <div class="box-footer">\n      <button id="dnd-button"\n        class="btn btn-primary"\n        draggable="true"\n        ondragstart="dndButtonHandler(event);"\n        data-clipboard-text="'+url('api/yggdrasil')+'"\n      >\u5C06\u6B64\u6309\u94AE\u62D6\u52A8\u81F3\u542F\u52A8\u5668</button>\n      <a class="btn" target="_blank" href="http://t.cn/RrEcYfk">\u542F\u52A8\u5668\u914D\u7F6E\u6559\u7A0B</a>\n    </div>\n  </div><!-- /.box -->\n';$('#sign-button').closest('.col-md-8').append(dom);var clipboard=new ClipboardJS('#dnd-button');clipboard.on('success',function(){$('#dnd-button').attr('title','\u5DF2\u590D\u5236\uFF01').tooltip('show'),setTimeout(function(){return $('#dnd-button').tooltip('destroy')},1e3)}),clipboard.on('error',function(){$('#dnd-button').attr('title','\u65E0\u6CD5\u8BBF\u95EE\u526A\u8D34\u677F\uFF0C\u8BF7\u624B\u52A8\u590D\u5236\u3002').tooltip('show')});function dndButtonHandler(a){var b=url('api/yggdrasil'),c='authlib-injector:yggdrasil-server:'+encodeURIComponent(b);a.dataTransfer.setData('text/plain',c),a.dataTransfer.dropEffect='copy'}

// let dom = `
//   <div class="box box-success">
//     <div class="box-header with-border">
//       <h3 class="box-title">快速配置启动器</h3>
//     </div><!-- /.box-header -->
//     <div class="box-body">
//       <p>本站的 Yggdrasil API 认证服务器地址：<code>${ url('api/yggdrasil') }</code></p>
//       <p>点击下方按钮复制 API 地址，或者将按钮拖动至启动器的任意界面即可快速添加认证服务器（目前仅支持 HMCL 3.1.74 及以上版本）。</p>
//     </div><!-- /.box-body -->
//     <div class="box-footer">
//       <button id="dnd-button"
//         class="btn btn-primary"
//         draggable="true"
//         ondragstart="dndButtonHandler(event);"
//         data-clipboard-text="${ url('api/yggdrasil') }"
//       >将此按钮拖动至启动器</button>
//       <a class="btn" target="_blank" href="http://t.cn/RrEcYfk">启动器配置教程</a>
//     </div>
//   </div><!-- /.box -->
// `;

// $('#sign-button').closest('.col-md-8').append(dom);

// let clipboard = new ClipboardJS('#dnd-button');

// clipboard.on('success', e => {
//   $('#dnd-button').attr('title', '已复制！').tooltip('show');

//   setTimeout(() => $('#dnd-button').tooltip('destroy'), 1000);
// });

// clipboard.on('error', e => {
//   $('#dnd-button').attr('title', '无法访问剪贴板，请手动复制。').tooltip('show');
// });

// function dndButtonHandler(event) {
//   var yggdrasilApiRoot = url('api/yggdrasil');
//   var uri = 'authlib-injector:yggdrasil-server:' + encodeURIComponent(yggdrasilApiRoot);
//   event.dataTransfer.setData('text/plain', uri);
//   event.dataTransfer.dropEffect = 'copy';
// }
