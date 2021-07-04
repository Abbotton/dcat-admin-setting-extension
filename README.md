# 公告：因Dcat Admin自带的系统配置功能已足够好用，足够人性化，所以本仓库停止维护，如有需要请自行fork，感谢各位支持。

## :clap:Dcat Admin系统配置插件:kissing_heart::kissing_heart:

### 写在前面
[Dcat Admin](https://github.com/jqhph/dcat-admin) 是一个功能完善的、非常优雅的后台系统，发布这款插件呢，一是为了自用，二是为了给`Dcat Admin`生态贡献一点绵薄之力，本插件支持`Dcat Admin`中常用的表单类型配置，`tree`、`hasMany`、`table`这3个比较特殊的表单类型正在逐步完善中，非常欢迎大家提交Issues或者PR，如果有帮助到您，还请给个`Star`。

### 安装

#### 1、引入扩展包
```shell script
composer require abbotton/setting
```

#### 2、发布文件
```shell script
> php artisan vendor:publish

Which provider or tag's files would you like to publish?:
[0 ] Publish files from all providers and tags listed below
[1 ] Provider: Dcat\Admin\AdminServiceProvider
[2 ] Provider: Dcat\Admin\Extension\Setting\SettingServiceProvider
......

# 此处输入`Dcat\Admin\Extension\Setting\SettingServiceProvider`对应索引
>2
```

#### 3、执行数据迁移
```shell script
php artisan migrate
```

#### 4、登录后台，在`开发工具/扩展`下`启用`扩展，然后`导入`扩展。

#### 5、刷新页面应该可以看到增加了一个菜单`系统配置`,Enjoy。

### 使用

添加完相应配置后，直接使用`config('key')`即可取得相关配置参数。

### Change Log
* 2020年8月12日
    - v0.0.1发布
