# refresh-ali-cdn

这个插件可以在用户更新其角色信息时，自动通过阿里云 API 发出缓存刷新请求。

## 配置

本插件无配置页面。所有的配置在 `.env` 文件中完成。有以下 3 个配置项：

- `ALICDN_SITE_BASE_URL` - 您的阿里云 CDN 的基础 URL，**不能** 以斜杠结尾。
- `ALICDN_ACCESSKEY_ID` - 您的阿里云账户的 AccessKeyId。
- `ALICDN_ACCESSKEY_SECRET` - 您的阿里云账户的 AccessKeySecret。

**若使用子账号 请注意RAM鉴权。**
