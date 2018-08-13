## CrazyLogin 数据对接

通过本插件，CrazyLogin 可以使用皮肤站数据库的 `users` 表存储数据，玩家可以直接使用皮肤站上绑定的角色名与密码登录游戏。
可以让玩家统一从皮肤站注册账号，防止游戏内恶意注册。也可配合邀请码插件等一起使用。

其他数据对接插件：[Authme 数据对接](https://github.com/bs-community/blessing-skin-plugins/tree/master/authme-integration)，[论坛数据对接](https://github.com/bs-community/blessing-skin-plugins/tree/master/forum-integration)。

**注意：本插件依赖[「单角色限制」](https://github.com/bs-community/blessing-skin-plugins/tree/master/single-player-limit)插件，使用之前请务必启用该插件。**

### 配置皮肤站

打开 `.env` 配置文件，在最底部添加一行：

```ini
CRAZYLOGIN_ENCRYPTOR = CrazyCrypt1
```

把等号后面的修改为 CrazyLogin 插件 `config.yml` 配置文件中的 `encryptor` 的值（默认即为 `CrazyCrypt1`，无需修改）。

设置 `CRAZYLOGIN_ENCRYPTOR` 后会覆盖 `PWD_METHOD` 的设置。所以 `PWD_METHOD` 保持原样即可。

目前支持的密码 Hash 算法如下：

- `MD5`
- `SHA256` 👈 和 Authme 那个不一样，不能兼容
- `SHA512`
- `CrazyCrypt1` 👈 默认是这个

其他的懒得支持了，有需要的话可以联系我。

**注意：本插件仅支持 MySQL 数据库。推荐配合 Authme 5.4.0 及以上版本使用。**

### 配置 CrazyLogin

打开 CrazyLogin 插件配置文件 `config.yml`，修改以下几项：

```yaml
database:
  # 本插件仅支持 MySQL 数据库
  saveType: MYSQL
  MYSQL:
    # 皮肤站数据库的主机、端口、用户名、密码、数据库名
    connection:
      host: '127.0.0.1'
      port: '3306'
      dbname: 'blessing-skin'
      user: 'username'
      password: 'secret'
    # 皮肤站的 users 表名，如果设置了表前缀记得加上
    tableName: users
```

### 注意事项

如果游戏中 CrazyLogin 提示密码不正确，请尝试让玩家重新登录一次皮肤站以刷新密码 hash。

在 CrazyLogin 注册的用户也可以使用游戏角色名与游戏中的密码直接登录皮肤站。
