# purge-azure-global-cdn

这个插件可以在用户更新其角色信息时，自动通过 Azure REST API 发出缓存刷新请求。

## 配置

本插件无配置页面。所有的配置在 `.env` 文件中完成。有以下 7 个配置项：

- `AZURE_AD_TENANT_ID` - Azure Active Directory 中的应用的目录（租户）ID；
- `AZURE_AD_CLIENT_ID` - Azure Active Directory 中的应用的应用程序（客户端）ID；
- `AZURE_AD_CLIENT_SECRET` - Azure Active Directory 中的应用的客户端密码；
- `AZURE_SUBSCRIPTION_ID` - Azure 订阅 ID；
- `AZURE_RESOURCE_GROUP` - CDN 域名组所在的资源组的名称；
- `AZURE_CDN_PROFILE` - CDN 域名组配置文件名称；
- `AZURE_CDN_ENDPOINT` - CDN 终结点名称。

## License

MIT License (c) 2020-present Honoka Tech LTD(GB)
