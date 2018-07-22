## Redis

Blessing Skin 可以使用 Redis 作为缓存以及 Session 的后端驱动，一定程度上提升站点性能（配合 [BS Super Cache](https://github.com/bs-community/blessing-skin-plugins/tree/master/bs-super-cache) 插件使用效果更佳）。本插件可以指引你配置 Redis 的连接参数，为你检测连接状态，以及自动设置 BS 的缓存、Session 驱动。

### 配置连接参数

如果你使用 TCP/IP 协议连接 Redis，那么请在你皮肤站的 `.env` 里配置以下几个项目：

```
REDIS_HOST = 127.0.0.1
REDIS_PORT = 6379
REDIS_PASSWORD = null
```

如果你需要通过 UNIX domain socket 连接，请配置以下几项：

```
REDIS_SCHEME = unix
REDIS_SOCKET_PATH = /tmp/redis.sock
```

### 设置缓存、Session 驱动为 Redis

如果你启用了 BS 的 Redis 插件，那么以下步骤插件已经帮你自动完成了。

如果你不想使用本插件，那你也可以在 `.env` 中手动设置以下项目:

```
CACHE_DRIVER = redis
SESSION_DRIVER  = redis
```
