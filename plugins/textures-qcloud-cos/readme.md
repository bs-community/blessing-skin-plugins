**注意：本插件仅支持 COS v5，如你正在使用旧版本的 COS，请联系腾讯云客服升级版本。**

请在皮肤站的 `.env` 配置文件中添加并填写以下条目：

```
COS_APP_ID=
COS_SECRET_ID=
COS_SECRET_KEY=
COS_TIMEOUT=60
COS_CONNECT_TIMEOUT=60
COS_BUCKET=
COS_REGION=ap-shanghai
COS_CDN=
COS_READ_FROM_CDN=true
COS_SCHEME=https
```

其中可用地域 `COS_REGION` 的填写请参考 [这里](https://github.com/freyo/flysystem-qcloud-cos-v5#region)，
CDN 地址 `COS_CDN` 必须以 `http(s)://` 开头。
