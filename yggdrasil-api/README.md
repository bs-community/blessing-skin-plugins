# Yggdrasil API for Blessing Skin

本插件完整实现了 [Yggdrasil API 规范](https://github.com/to2mbn/authlib-injector/wiki/Yggdrasil%E6%9C%8D%E5%8A%A1%E7%AB%AF%E6%8A%80%E6%9C%AF%E8%A7%84%E8%8C%83)，可与 [authlib-injector](https://github.com/to2mbn/authlib-injector) 等 authlib hook 配合使用实现外置登录系统。

## API 路由

```
routes.php

# Authentication
POST /api/yggdrasil/authserver/authenticate
POST /api/yggdrasil/authserver/refresh
POST /api/yggdrasil/authserver/validate
POST /api/yggdrasil/authserver/invalidate
POST /api/yggdrasil/authserver/signout

# Session
POST /api/yggdrasil/sessionserver/session/minecraft/join
GET  /api/yggdrasil/sessionserver/session/minecraft/hasJoined

# Profiles
GET  /api/yggdrasil/sessionserver/session/profile/{uuid}
POST /api/yggdrasil/api/profiles/minecraft
```

## 使用方法

请参阅本项目 [Wiki](https://github.com/printempw/yggdrasil-api/wiki)。

## 版本说明

本插件的更新日志可以在这里查看：[CHANGELOG](https://github.com/printempw/yggdrasil-api/blob/master/CHANGELOG.md)。

注意，v2.0.0 版本之后的插件不再支持 [authlib-agent](https://github.com/to2mbn/authlib-agent)。
