# 使用 LittleSkin 登录
当您想使用 LittleSkin 登录皮肤站时，可以使用本插件能让现有用户进行这样的~~骚~~操作。

## 使用方法

本插件没有配置页面，所有配置通过修改 `.env` 来进行。

1. 在 `https://mcskin.littleservice.cn/user/oauth/manage` 创建 OAuth 2 应用
2. 增加三条配置项，`LITTLESKIN_KEY`、 `LITTLESKIN_SECRET`、 `LITTLESKIN_REDIRECT_URI`
3. 将 `客户端 ID`、`客户端 Secret`、`回调 URL` 分别填入  `LITTLESKIN_KEY`、 `LITTLESKIN_SECRET`、 `LITTLESKIN_REDIRECT_URI`

## 示例

```
LITTLESKIN_KEY=1
LITTLESKIN_SECRET=khGoXUMPZvhT8qNQi3PECK8BDokyfNne
LITTLESKIN_REDIRECT_URI=https://skin.bs-community.dev/auth/login/littleskin/callback
```
