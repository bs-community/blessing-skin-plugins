# Yggdrasil Connect for Blessing Skin

本插件实现了 [Yggdrasil 服务端技术规范](https://github.com/yushijinhun/authlib-injector/wiki/Yggdrasil%20%E6%9C%8D%E5%8A%A1%E7%AB%AF%E6%8A%80%E6%9C%AF%E8%A7%84%E8%8C%83)，可与 [authlib-injector](https://github.com/yushijinhun/authlib-injector) 及支持的启动器配合实现 Minecraft 外置登录身份验证，并可与 [Janus](https://github.com/bs-community/janus) 项目配合实现基于 [Yggdrasil Connect 协议](https://github.com/yushijinhun/authlib-injector/issues/268) 的 OAuth 身份验证。要了解更多关于 Yggdrasil Connect 和 Janus 的信息，请阅读下面的 [关于 Yggdrasil Connect](#关于-yggdrasil-connect) 部分。

本插件由原版 Yggdrasil API 插件重构而来，使用 Laravel Passport 的个人访问令牌（Personal Access Token）作为访问令牌，通过在 JWT Payload 中添加角色 UUID 并重新签名实现访问令牌与角色的绑定。首次启用本插件时，请务必按照下方的 [插件使用方法](#插件使用方法) 部分中的说明执行操作，否则本插件可能无法正常工作。

本插件不需要也不能与原版 Yggdrasil API 插件同时启用，但插件数据可与原版 Yggdrasil Connect 插件通用。如需从原版 Yggdrasil API 插件迁移至本插件，请务必按照下方的 [插件使用方法](#插件使用方法) 部分的第三步处理 `uuid` 表，否则本插件无法正常工作。

本插件在一定程度上修复了原版 Yggdrasil API 插件的多个 Bug。要了解具体细节，请阅读下面的 [关于 Bug 修复](#关于-bug-修复) 部分。

## 插件使用方法

1. 通过 Blessing Skin 插件市场安装并启用本插件。
2. 进入终端，在 Blessing Skin Server 根目录下执行 `php artisan yggc:create-personal-access-client` 命令，创建个人访问客户端（Personal Access Client）。
    - 创建完成后，请在 .env 中新建一条配置 `PERSONAL_ACCESS_CLIENT_ID`，并将其值设为命令返回的个人访问客户端的 Client ID。
3. 如果你是从原版 Yggdrasil API 插件迁移而来，请在终端中执行 `php artisan yggc:fix-uuid-table` 命令，以清除原版 Yggdrasil API 插件的 UUID 表中可能存在的异常数据，并修改数据表结构。
    - **该指令会直接删除 `uuid` 表中的部分记录，因此在执行该指令前，请务必备份原先的 `uuid` 表！！！**
        - 要了解该指令对你的 `uuid` 表都做了什么，请阅读下面的 [关于 Bug 修复](#关于-bug-修复) 部分。
    - 如果你没有安装过原版 Yggdrasil API 而直接安装了本插件，则无需执行这条命令。
4. 如需启用 Yggdrasil Connect，请在部署好 Janus 后，在本插件的配置页面填写你的 Janus 实例的 OpenID 提供者标识符。
    - 要了解 Yggdrasil Connect 和 Janus 是什么，请阅读下面的 [关于 Yggdrasil Connect](#关于-yggdrasil-connect) 部分。
    - 要了解如何部署 Janus，请查看 [Janus 项目的代码仓库](https://github.com/bs-community/janus)。

## 已知问题

- 在部分情况下，用户在通过传统 Auth Server 登录时，可能会遇到 HTTP 500 错误；或在请求 OAuth 授权时，在请求了正确的 scope 的情况下，仍遇到 `invalid_scopes` 错误。
    - 要了解导致该问题的原因，请参阅 [bs-community/blessing-skin-server#661 (comment)](https://github.com/bs-community/blessing-skin-server/pull/661#issuecomment-3008486580)。
    - 在插件管理中重启（禁用再启用）本插件，或将 Blessing Skin Server 升级至最新开发版即可解决该问题。

## 关于 Yggdrasil Connect

Yggdrasil Connect 是基于 OAuth 2.0 和 OpenID Connect 协议的 Minecraft 外置登录身份验证协议，其核心目标是取代 Yggdrasil API 中的 Auth Server 部分，从而改善 authlib-injector 外置登录方案的安全性和用户体验。

在 Yggdrasil API 的 Auth Server 部分中，用户需要将自己的用户名和密码直接暴露给第三方应用，才能通过第三方应用访问 Yggdrasil API，这增加了用户账户关键信息泄露的风险；且该部分的 API 在设计上几乎没有考虑到二步验证，无法良好地保障用户账户的安全。并且，由于各个第三方应用在该部分的实现的差异，用户在不同应用之间的登录体验存在较大割裂，时常出现应用实现不完整导致用户无法正常登录的问题。

Yggdrasil Connect 的出现正是为了解决这些问题。通过 OAuth 2.0 和 OpenID Connect 协议，Yggdrasil Connect 可以实现用户在不同应用间的登录体验的统一，同时方便各验证服务器自行设计用户身份认证方式，以保障用户账户的安全。

对于 Blessing Skin Server 来说，Yggdrasil Connect 还解决了「通过社交网站 OAuth 注册的用户默认没有密码，无法在启动器中登录」的问题：通过 Yggdrasil Connect，用户可以通过社交网站账户登录皮肤站并授予启动器权限，而无需输入密码。 

要了解更多关于 Yggdrasil Connect 协议的信息，请阅读 [Yggdrasil Connect 协议规范](https://github.com/yushijinhun/authlib-injector/issues/268)。

### 为 Blessing Skin Server 启用 Yggdrasil Connect

要为 Blessing Skin Server 启用 Yggdrasil Connect，则必须部署 Janus。

Janus 是一个独立于 Blessing Skin Server 运行、但与 Blessing Skin Server 使用同一个数据库的 Yggdrasil Connect 服务端。由于 Laravel 框架缺乏合适的 OpenID Connect 服务端扩展包，故采取这种外挂 OpenID Connect 服务端的方式实现 Yggdrasil Connect。

要了解如何部署 Janus，请查看 [Janus 项目的代码仓库](https://github.com/bs-community/janus)。

## 关于 Bug 修复

本插件在一定程度上修复了原版 Yggdrasil API 插件的多个影响用户体验乃至导致用户无法正常登录 Minecraft 游戏服务器的 Bug：

- **使用角色名登录时，并不会自动选择角色**（[#123](https://github.com/bs-community/blessing-skin-plugins/issues/123)）：
    - 本插件在用户使用角色名登录时，会自动将 Access Token 绑定至角色名对应的角色，同时在 API 响应中添加 `selectedProfile` 字段。
    - 同时，对于签发 Access Token 时能确定 Access Token 绑定到的角色的场景（使用角色名登录、令牌刷新），API 响应中的 `availableProfiles` 字段中将仅包含 Access Token 绑定到的角色的信息，以避免客户端出现异常行为。
- **uuid 表中数据不一致**（[#151](https://github.com/bs-community/blessing-skin-plugins/issues/151)）：
    - 本插件重新设计了 `uuid` 表，将 UUID 记录与角色模型的关联字段从角色名（`name`）改为了 PID（`pid`）。同时，本插件为 `uuid` 表中 `pid`、`name` 和 `uuid` 字段添加了 UNIQUE 约束，确保不会出现两条拥有相同的 PID、角色名或 UUID 的记录。因此，在从原版 Yggdrasil API 插件迁移至本插件时，必须在终端中执行 `php artisan yggc:fix-uuid-table` 命令，以清除 `uuid` 表中异常的数据，并为 `uuid` 表添加 `pid` 字段及相关约束。
        - 但这项改动也会导致「正版验证」（`mojang-verification`）插件的「更新 UUID」功能无法正常工作，具体表现为该功能可能会在 `uuid` 表中插入一条 `pid` 为 `null` 的无效记录，该记录可能导致后续插入相同角色名的正确的 UUID 记录时失败并报错。考虑到该功能即使是在配合原版 Yggdrasil API 插件使用的情况下也可能会导致更大程度的数据错乱，建议直接将该功能禁用。
    - 为保证与原版 Yggdrasil API 插件的兼容性，UUID 表中的角色名字段（`name`）仍被保留，并监听了 `player.renamed` 事件，使得 UUID 表中记录的角色名可以在角色更名时被一同更新。
- **角色改名（新旧名字仅大小写不同）会导致丢失 uuid**（[#152](https://github.com/bs-community/blessing-skin-plugins/issues/152)）：
    - 当 UUID 算法为 v3 时，这是预期行为，无需修复。
    - 当 UUID 算法为 v4 时，本插件使用 PID 而非角色名作为 UUID 记录与角色模型的关联字段，PID 全局唯一且不可更改，不会出现该问题。
- **删除角色时不会删除 uuid 表中的对应的记录**（[#202](https://github.com/bs-community/blessing-skin-plugins/issues/202)）：
    - 本插件在 `uuid` 表中将 `pid` 字段定义为了外键，关联至 `players` 表中的 `pid` 字段，并添加了级联删除规则，确保用户删除角色时 `uuid` 表中的 UUID 记录会被一起删除。
        - 即使出现意外情况，导致 `uuid` 表中的记录未在用户删除角色时删除，考虑到本插件使用角色 PID 作为关联字段，而 PID 是全局唯一的，这些孤立的记录并不会与其他角色发生错误关联，从而避免了该 Bug 带来的数据错乱的问题。
- **通过刷新获得的 accessToken 在用户邮箱存在大写字母的情况下无法通过校验**（[#212](https://github.com/bs-community/blessing-skin-plugins/issues/212)）：
    - 本插件使用 Laravel Passport 的个人访问密钥（Personal Access Token）作为 Access Token，其使用用户的 UID 作为 Access Token 所有者的身份标识符，不会出现类似的问题。
        - 但这项改动也要求站点管理员在终端中执行 `php artisan yggc:create-personal-access-client` 命令创建个人访问客户端（Personal Access Client），并在 .env 中配置 `PERSONAL_ACCESS_CLIENT_ID` 的值为个人访问客户端的 Client ID 后，传统 Auth Server 才可正常运行。
- **角色改名后 Access Token 仍然有效**（[#231](https://github.com/bs-community/blessing-skin-plugins/issues/231)）：
    - 本插件监听了 `player.renamed` 事件，在角色更名后，本插件会在缓存中记录角色更名的时间。
    - 在验证 Access Token 时，本插件会检查 Access Token 的签发时间，如早于缓存中记载的角色更名时间，则视为 Access Token 暂时失效，返回错误。
        - 为确保 UserInfo Endpoint 与进服请求中的令牌验证结果一致（即，避免 UserInfo Endpoint 验证令牌有效，但进服请求验证令牌无效的情况出现），本插件对于 UserInfo Endpoint 也会执行该检查。这可能导致通过 Yggdrasil Connect 签发的 Access Token 被刷新的频率增加（因为 UserInfo Endpoint 中已包含有最新的角色信息，启动器本可直接请求 UserInfo Endpoint 获取最新角色信息，而无需通过刷新令牌的方式更新角色信息，但由于 Access Token 被认为是过期的，UserInfo Endpoint 会返回 `invalid_token` 错误），但只要启动器正确按照正常的令牌失效刷新流程处理这种情况，就不会产生影响用户体验的问题。
- **查询角色属性时返回的角色名大小写和实际角色名不符**（[#232](https://github.com/bs-community/blessing-skin-plugins/issues/232)）
    - 本插件在返回角色信息时会返回角色在数据库中记录的角色名，而非请求中的角色名。

## 版权

Copyright (c) 2025-present LittleSkin. All rights reserved. Open source under the MIT license.

_Disclaimer：你站产品经理自己写代码的原则就是代码和人有一个能跑就行，自然有些代码很粗糙很难看很低效。如果你看着哪里的代码不爽，欢迎直接重构并 PR。_