# Yggdrasil API for Blessing Skin

This plugin implements [Yggdrasil API Spec](https://github.com/yushijinhun/authlib-injector/wiki/Yggdrasil%20%E6%9C%8D%E5%8A%A1%E7%AB%AF%E6%8A%80%E6%9C%AF%E8%A7%84%E8%8C%83). It can be used with [authlib-injector](https://github.com/yushijinhun/authlib-injector).

## API Routes

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
GET  /api/yggdrasil/sessionserver/session/minecraft/profile/{uuid}
POST /api/yggdrasil/api/profiles/minecraft
```

## Usage

Read [documentation](https://blessing.netlify.app/yggdrasil-api/).
