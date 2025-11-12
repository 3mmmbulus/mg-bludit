# Maigewan CMS 多站点功能使用说明

## 功能概述

Maigewan CMS 现已支持单实例多站点模式,一个系统可以同时服务多个不同的域名,每个域名拥有独立的内容、页面和上传文件。

## 核心特性

1. **域名识别**: 自动根据访问域名加载对应站点内容
2. **精确匹配**: `example.com` 访问时加载 `mgw-content/example.com/` 目录
3. **WWW 回退**: `www.example.com` 访问时,如无独立配置则自动回退到 `example.com`
4. **默认站点**: 未配置的域名自动显示 `mgw-content/_default/` 默认站点
5. **共享系统**: 所有站点共享用户系统和核心配置

## 目录结构

```
/www/wwwroot/103.181.135.146/
├── mgw-config/              # 系统配置(所有站点共享)
│   ├── system.php          # 系统配置(语言、时区等)
│   └── users.php           # 用户系统(共享)
│
├── mgw-content/            # 多站点内容根目录
│   ├── _default/           # 默认站点(未配置域名显示)
│   │   ├── databases/
│   │   │   ├── site.php   # 站点配置
│   │   │   ├── pages.php  # 页面数据
│   │   │   └── ...
│   │   ├── pages/         # 页面内容
│   │   ├── uploads/       # 上传文件
│   │   └── ...
│   │
│   ├── example.com/        # example.com 域名的内容
│   │   ├── databases/
│   │   ├── pages/
│   │   ├── uploads/
│   │   └── ...
│   │
│   └── another.com/        # another.com 域名的内容
│       └── ...
│
├── mgw-kernel/             # 系统核心(所有站点共享)
├── mgw-themes/             # 主题(所有站点共享)
└── mgw-plugins/            # 插件(所有站点共享)
```

## 站点识别机制

系统在 `mgw-kernel/boot/init.php` 中实现了自动域名识别:

```php
// 1. 获取访问域名
$host = $_SERVER['HTTP_HOST'];  // 如: example.com

// 2. 尝试精确匹配
if (is_dir(PATH_ROOT . 'mgw-content/' . $host)) {
    // 找到: mgw-content/example.com/
    $siteIdentifier = $host;
}

// 3. WWW 前缀回退
else if (strpos($host, 'www.') === 0) {
    $hostWithoutWww = substr($host, 4);  // www.example.com -> example.com
    if (is_dir(PATH_ROOT . 'mgw-content/' . $hostWithoutWww)) {
        $siteIdentifier = $hostWithoutWww;
    }
}

// 4. 默认站点
else {
    $siteIdentifier = '_default';
}

// 5. 动态设置路径
define('PATH_CONTENT', PATH_ROOT . 'mgw-content/' . $siteIdentifier . '/');
```

## 创建新站点

### 步骤 1: 创建站点目录

```bash
cd /www/wwwroot/103.181.135.146/mgw-content
mkdir -p yoursite.com/databases
mkdir -p yoursite.com/pages
mkdir -p yoursite.com/uploads
mkdir -p yoursite.com/tmp
mkdir -p yoursite.com/workspaces
```

### 步骤 2: 创建站点配置

创建 `mgw-content/yoursite.com/databases/site.php`:

```php
<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>
{
    "title": "您的站点标题",
    "slogan": "您的站点口号",
    "description": "您的站点描述",
    "footer": "Copyright © 2025",
    "itemsPerPage": 6,
    "theme": "blogx",
    "url": "http://yoursite.com",
    "uriFilters": "page",
    "orderBy": "date",
    "extremeFriendly": true,
    "markdownParser": true
}
```

### 步骤 3: 创建必要的数据库文件

```bash
# 分类
echo '<?php defined("MAIGEWAN") or die("Maigewan CMS."); ?>
{"uncategorized":{"name":"Uncategorized","key":"uncategorized"}}' > yoursite.com/databases/categories.php

# 标签
echo '<?php defined("MAIGEWAN") or die("Maigewan CMS."); ?>
{}' > yoursite.com/databases/tags.php

# 安全
echo '<?php defined("MAIGEWAN") or die("Maigewan CMS."); ?>
{}' > yoursite.com/databases/security.php

# 系统日志
echo '<?php defined("MAIGEWAN") or die("Maigewan CMS."); ?>
{}' > yoursite.com/databases/syslog.php
```

### 步骤 4: 创建页面内容

创建 `mgw-content/yoursite.com/databases/pages.php`:

```php
<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>
{
    "welcome": {
        "title": "欢迎",
        "content": "欢迎访问我的网站!",
        "username": "admin",
        "type": "published",
        "date": "2025-11-12 12:00:00",
        "key": "welcome"
    }
}
```

### 步骤 5: 配置域名解析

将域名 `yoursite.com` 解析到服务器 IP,nginx/apache 配置指向同一个 Maigewan 安装目录。

## 访问测试

- 访问 `http://example.com` → 显示 `mgw-content/example.com/` 内容
- 访问 `http://www.example.com` → 自动回退到 `mgw-content/example.com/` 内容
- 访问 `http://unknown.com` → 显示 `mgw-content/_default/` 默认站点

## 运行测试脚本

```bash
php /www/wwwroot/103.181.135.146/test-multisite.php
```

测试脚本会模拟不同域名访问,验证站点识别机制。

## 共享与独立

### 共享资源
- **用户系统**: 所有站点共享同一套用户,登录一次可管理所有站点
- **系统配置**: 语言、时区、管理后台主题等
- **核心文件**: kernel、themes、plugins 等
- **管理后台**: 统一入口,可切换不同站点管理

### 独立资源
- **站点配置**: 标题、口号、主题选择等
- **页面内容**: 每个站点独立的文章和页面
- **上传文件**: 图片、附件等独立存储
- **分类标签**: 独立的分类和标签体系

## 注意事项

1. **目录命名**: 站点目录名必须与域名完全一致(如 `example.com`)
2. **默认站点**: `_default` 目录保留,用于未配置域名的访问
3. **数据备份**: 多站点模式下,建议定期备份各站点的 `mgw-content/{domain}/` 目录
4. **性能考虑**: 过多站点可能影响性能,建议根据服务器配置合理规划

## 技术实现

- **识别点**: `mgw-kernel/boot/init.php` 开头的多站点识别代码
- **动态路径**: `PATH_CONTENT` 等常量根据识别结果动态设置
- **站点标识**: `SITE_IDENTIFIER` 常量存储当前站点标识符

## 示例站点

系统已包含两个示例站点:

1. **_default**: 默认站点,提示域名未配置
2. **example.com**: 完整示例站点,包含欢迎页面和说明文档

您可以参考这些示例创建自己的站点。

---

**Maigewan CMS** - 强大的多站点内容管理系统
