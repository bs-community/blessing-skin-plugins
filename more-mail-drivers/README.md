## 邮件驱动增强

本插件提供了对 Mailgun、Mandrill、Amazon SES、SparkPost、PHP 的 `mail()` 函数及 `sendmail` 的驱动支持。
启用本插件并正确配置后，Blessing Skin 即可通过这些支持的服务发送邮件（SMTP 已原生支持）。

请根据以下示例修改你的 `.env` 配置文件。

> 注意：如果你正在使用 BS v3.4.0 及以下的版本，
> 请务必保证在使用任何邮件驱动时 `MAIL_HOST` 都不为空（随便填点什么就行，否则邮件功能会被禁用）。
> BS v3.4.0 以上的版本无此限制（仅当 `MAIL_DRIVER` 设置为空时才禁用邮件功能）。

### Mailgun

```
MAIL_DRIVER = mailgun
MAIL_USERNAME = test@example.com
MAILGUN_DOMAIN = example.com
MAILGUN_SECRET = api-key
```

### SMTP
```
MAIL_DRIVER = smtp
MAIL_HOST = smtp.example.com
MAIL_PORT = 465
MAIL_USERNAME = test@example.com
MAIL_PASSWORD = secret
MAIL_ENCRYPTION = tls
```

### Amazon SES

如需使用 SES，请在插件根目录下运行 `composer require aws/aws-sdk-php`。

```
MAIL_DRIVER = ses
MAIL_USERNAME =
SES_KEY =
SES_SECRET =
SES_REGION = us-east-1
```

### Sendmail

```
MAIL_DRIVER = sendmail
SENDMAIL_COMMAND = '/usr/sbin/sendmail -bs' #注意用引号包起来
```

其他支持的邮件驱动配置方法请参阅 [邮件 - Laravel 文档](https://laravel-china.org/docs/laravel/5.2/mail/1126)。

如果有支持其他邮件发送服务（e.g. SendCloud）的需求可以联系我添加。
