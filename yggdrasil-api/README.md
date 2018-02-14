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

1. 正确安装 [Blessing Skin](https://github.com/printempw/blessing-skin-server) 皮肤站本体以及本插件；
2. 访问后台「插件配置」页面，正确配置 RSA 私钥；
3. 从 [这里](https://ci.to2mbn.org/job/authlib-injector) 下载构建好了的 `authlib-injector.jar` 文件并放入你的 `.minecraft` 目录内；
4. 下载 HMCL 启动器的 `.jar` 版本，并使用如下命令打开启动器（如果是其他支持自定义 Yggdrasil API 地址的启动器的话就按照他们说的来）：

```bash
java -javaagent:.minecraft/authlib-injector.jar=@http://your.domain/api/yggdrasil -jar HMCL.jar
```

5. 启动器正常打开后，切换至 **游戏设置 > 高级设置** 选项卡，在「Java 虚拟机参数」中输入如下内容：

```text
-javaagent:authlib-injector.jar=@http://your.domain/api/yggdrasil
```

6. 选择「正版登录」并输入你的 **皮肤站用户邮箱和密码**，启动游戏，并且可以选择要使用的角色（如果你的皮肤站账号名下只有一个角色，那就会自动帮你选择该角色）；
7. 如果命运的齿轮没有出差错的话，游戏将会正常启动，你也可以在游戏中看到你设置的皮肤了。

## 服务器配置

注意，「启动器」、「游戏」与「服务端」都**必须**加载 `authlib-injector`，而且配置地址必须相同。

```bash
java -javaagent:/path/to/authlib-injector.jar -jar your-server.jar
```

## 版本说明

本插件的更新日志可以在这里查看：[CHANGELOG](https://github.com/printempw/yggdrasil-api/blob/master/CHANGELOG.md)。

注意，v2.0.0 版本之后的插件不再支持 [authlib-agent](https://github.com/to2mbn/authlib-agent)。
