## Yggdrasil API [WIP]

本插件实现了 Yggdrasil API 规范，可与 authlib-agent 等登录系统配合使用。

插件实现了 authlib-agent 约定的所有 API（具体参见 `routes.php`）：

```
/api/yggdrasil/authenticate
/api/yggdrasil/refresh
/api/yggdrasil/validate
/api/yggdrasil/invalidate
/api/yggdrasil/signout
/api/yggdrasil/fillgameprofile
/api/yggdrasil/profiles/minecraft/{uuid}
/api/yggdrasil/joinserver
/api/yggdrasil/hasjoinserver
/api/yggdrasil/profilerepo
/api/yggdrasil/username2profile/{username}
```

编译 authlib-javaagent 时请将 `AGENT_API_ROOT` 修改为 `http://你的皮肤站地址.com/api/yggdrasil/`，并在材质白名单中加入你的皮肤站域名。

本项目极度缺乏测试。
