# 批量导入材质

此插件提供基于命令行的方式来导入材质。

## 用法

启用本插件，在 BS 的根目录下执行此命令：

```
php artisan texture:import {uploader} {directory}
```

其中 `uploader` 参数为指定材质的上传者，值为上传者的 UID；
`directory` 参数为材质文件所在目录。

此外，还可以指定 `--cape` 参数表示该目录下的 **所有** 材质文件为披风文件；
可指定 `--gbk` 参数来进行文件名编码转换，如果您的系统是 Windows 系统并遇到乱码问题，可以尝试这个选项。

# Batch Import

This plugin provides a command line program to import textures.

## Usage

Enable this plugin and enter the root of your site, then execute the command below:

```
php artisan texture:import {uploader} {directory}
```

The parameter `uploader` means UID of uploader.
The parameter `directory` means the path of the directory which contains textures.

Additionally, you can add `--cape` flag to treat all textures as capes.
And you can add `--gbk` flag to perform file name encoding conversion if you're using Windows and characters can't be detected correctly.
