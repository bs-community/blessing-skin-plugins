## Blessing Skin Server 插件开发简易文档

大部分的用法都在 `/plugins/example-plugin/bootstrap.php` 的注释里了，这里说一下简单说明一下插件的目录结构。

一个插件的目录结构类似这样：

```
➜  example-plugin git:(master)$ tree
.
├── bootstrap.php
├── package.json
├── src
└── views
    └── config.tpl
```

### 插件基本信息的定义

首先，对于一个插件，`package.json` 这个文件是**必要**的。其中定义的内容是区分各个不同插件的关键，而插件所在的文件夹的名称可以随便取。

`package.json` 的内容是这样的：


```
{
  "name": "example-plugin",
  "version": "1.0.0",
  "title": "示例插件",
  "description": "可以直接创建此插件的副本并在其基础上开发新的插件，其代码也兼具插件开发文档功能（其实就是我懒得写文档）",
  "author": "printempw",
  "namespace": "Blessing\\ExamplePlugin",
  "config": "config.tpl"
}
```

其中 `name` 是插件的唯一标识符，不能重复。推荐只使用 **小写字母、数字、连字符** 进行命名。

`version` 是插件目前的版本号。

`title` 是插件在「插件管理」页上显示的名称，可以使用任意 UTF-8 字符集支持的字符。

`description` 是插件的描述。

`author` 是插件的作者信息。

`url` 插件网址。

`namespace` 是插件的命名空间，不能重复。插件 `src` 目录下的所有类都会被加载到这个命名空间下。调用插件中定义的视图时也必须加上此命名空间。

`config` 是插件的配置视图，必须是存在于插件 `views` 目录下的有效的视图文件。

### 命名空间

各个插件 `src` 目录下的类以及视图（`views` 目录下）、语言文件（`lang` 目录下）都会被加载到不同的命名空间（在 `package.json` 中定义）下。

命名空间的命名请遵循 PHP 的规则，可使用多级命名空间（譬如 `What\The\Fuck`）。

#### 使用带命名空间的视图、语言文件

只要在原来的 `key` 之前加上命名空间即可：

```
return View::make('Blessing\ExamplePlugin::config');

echo trans('Blessing\ExamplePlugin::user.notice');
```
