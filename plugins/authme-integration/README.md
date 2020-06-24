## Authme 数据对接

通过本插件，Authme 可以使用皮肤站数据库的 `users` 表存储数据，玩家可以直接使用皮肤站上绑定的角色名与密码登录游戏。
可以让玩家统一从皮肤站注册账号，防止游戏内恶意注册。

### 配置皮肤站

在安装皮肤站之前，请先将在 `.env` 文件中 [修改你的密码加密算法](https://github.com/bs-community/blessing-skin-server/wiki/%E5%A6%82%E4%BD%95%E5%A1%AB%E5%86%99-.env-%E9%85%8D%E7%BD%AE%E6%96%87%E4%BB%B6#-%E5%AE%89%E5%85%A8%E7%9B%B8%E5%85%B3)。

Authme `config.yml` 中 `passwordHash` 填的是什么，皮肤站 `.env` 里的 `PWD_METHOD` 就填什么。如果在皮肤站安装完成后再修改密码加密算法的话，之前已经注册的用户将会全部 **【无法登录】**（可以通过找回密码功能重置）。

目前支持的密码 Hash 算法如下：

- `BCRYPT` Blessing Skin 默认的算法，可以兼容 Flarum 论坛程序
- `SHA256` Authme 默认算法
- `SALTED2MD5` 可以兼容 Discuz!、Phpwind 论坛程序
- `SALTEDSHA512`

### 配置 Authme

打开 Authme 插件配置文件 `config.yml`，修改以下几项：

```yaml
DataSource:
  backend: 'MYSQL' # 或 SQLITE
  # 皮肤站数据库的主机、端口、用户名、密码、数据库名
  mySQLHost: '127.0.0.1'
  mySQLPort: '3306'
  mySQLUsername: 'username'
  mySQLPassword: 'secret'
  # 如果使用 SQLite，请填写数据库文件相对于 Authme 配置文件的相对路径，
  # 且确保数据库文件的后缀为 .db，但填写时配置时不要带上这个后缀。（Authme 自己会强行加上）
  mySQLDatabase: 'blessing-skin'
  # 皮肤站的 users 表名，如果设置了表前缀记得加上
  mySQLTablename: 'users'
  # 此项修改为 uid，其他 column 都保持默认值即可
  mySQLColumnId: 'uid'
```

如果你 Authme 的 `passwordHash` 使用的是 `SALTED2MD5` 或者 `SALTEDSHA512`，那么你还需要修改这几项：

```yaml
ExternalBoardOptions:
  # 修改为 salt
  mySQLColumnSalt: 'salt'
settings:
  security:
    # 修改为 6
    doubleMD5SaltLength: 6
```

### 注意事项

如果游戏中 Authme 提示密码不正确，请尝试让玩家重新登录一次皮肤站以刷新密码 hash。

在 Authme 注册的用户也可以使用游戏角色名与游戏中的密码直接登录皮肤站。
