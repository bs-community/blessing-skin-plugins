# 正版验证 (微软)

为拥有正版账号的用户提供验证、绑定。

## 使用方法

本插件部分配置通过修改 `.env` 来进行。

1. 在 `https://aka.ms/aad` 创建应用
2. 增加三条配置项，`MICROSOFT_KEY`、 `MICROSOFT_SECRET`、 `MICROSOFT_REDIRECT_URI`
3. 将 `客户端 ID`、`客户端 Secret`、`回调 URL` 分别填入 `MICROSOFT_KEY`、 `MICROSOFT_SECRET`、 `MICROSOFT_REDIRECT_URI`

## 示例

```
MICROSOFT_KEY=9fce0559-44b4-4c95-a144-d3ccf50ea62b
MICROSOFT_SECRET=secret@123
MICROSOFT_REDIRECT_URI=https://skin.bs-community.dev/mojang/callback
```
