# refresh-huawei-cdn

这个插件可以在用户更新其角色信息时，自动发送华为云CDN刷新缓存的API。

## 配置

你需要在 .env 中配置这些参数

- `HUAWEI_CLOUD_USERNAME` - IAM用户名
- `HUAWEI_CLOUD_PASSWORD` - IAM用户密码
- `HUAWEI_CLOUD_DOMAIN_NAME` - IAM用户所属账号名
- `HUAWEI_CLOUD_PROJECT_NAME` - 项目。在 “我的凭证” - “API凭证”页面可以看到，任选。
- `HUAWEI_CLOUD_IAM_BASE_URL` - 终端节点地址。在 https://console-intl.huaweicloud.com/apiexplorer/#/endpoint/IAM 查看您所选择的项目对应的节点地址，项目和区域同名。
- `HUAWEI_CLOUD_CDN_BASE_URL` - 网站地址，例如 https://example.com，一定**不要**在末尾加斜杠

## 注意
请先判断当前账号是华为账号还是华为云账号，可以根据https://support.huaweicloud.com/account_faq/faq_id_0009.html判断。华为账号获取Token需要创建IAM账户，授予该用户必要的权限，可以查看https://support.huaweicloud.com/api-iam/iam_30_0001.html。华为云账号获取Token无特殊要求。
