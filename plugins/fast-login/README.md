# Fast Login

这个插件可以在用户通过正版验证时，自动向 FastLogin 增加一条记录。

## 配置

本插件无配置页面。所有的配置在 `.env` 文件中完成。有以下配置项：

- `FAST_LOGIN_DRIVER` - 数据库驱动，目前仅支持 `mysql`（含 MariaDB）和 `sqlite`。默认是 `mysql`。
- `FAST_LOGIN_HOST` - FastLogin 的数据库所在的主机，默认是 `localhost`
- `FAST_LOGIN_PORT` - FastLogin 的数据库端口，默认是 `3306`
- `FAST_LOGIN_USERNAME` - FastLogin 的数据库用户名
- `FAST_LOGIN_PASSWORD` - FastLogin 的数据库密码
- `FAST_LOGIN_DATABASE` - FastLogin 的数据表表名

## Configuration

All configurations must be done by editing `.env` file with items below:

- `FAST_LOGIN_DRIVER` - Database driver, which only supports `mysql` (including MariaDB) and `sqlite`. Default value is `mysql`.
- `FAST_LOGIN_HOST` - Host of FastLogin's database.
- `FAST_LOGIN_PORT` - Port of FastLogin's database.
- `FAST_LOGIN_USERNAME` - Username of FastLogin's database.
- `FAST_LOGIN_PASSWORD` - Password of FastLogin's database.
- `FAST_LOGIN_DATABASE` - Name of FastLogin's data table.
