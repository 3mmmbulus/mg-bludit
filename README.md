# Maigewan CMS

基于 Bludit 3.16.2 的内容管理系统，专为 PHP 8.0+ 优化。

## 🚀 特性

- ✅ **PHP 8.3 完全兼容** - 支持 PHP 8.0, 8.1, 8.2, 8.3
- 📝 **简单易用** - 基于文件的 CMS，无需数据库
- 🎨 **主题系统** - 多种主题可选
- 🔌 **插件扩展** - 丰富的插件生态
- 🌍 **多语言支持** - 支持 30+ 种语言
- 📱 **响应式设计** - 完美支持移动设备
- 🔒 **安全可靠** - 内置安全机制

## 📋 系统要求

- PHP 8.0 或更高版本
- Web 服务器 (Apache, Nginx, 等)
- PHP 扩展:
  - json
  - mbstring
  - gd
  - curl
  - zip (可选，用于插件)

## 🛠️ 安装

### 方法 1: 克隆仓库

```bash
git clone https://github.com/3mmmbulus/mg-bludit.git
cd mg-bludit
```

### 方法 2: 下载 ZIP

1. 下载最新版本
2. 解压到您的 Web 服务器目录
3. 访问 `http://your-domain.com/install.php`
4. 按照安装向导完成设置

## 🔧 PHP 8.0+ 兼容性修复

本版本包含以下 PHP 8.0+ 兼容性修复:

1. **语法修复** - 修复了 remote-content 插件的语法错误
2. **Null 安全访问** - 使用 null 合并运算符处理 `$_SERVER` 变量
3. **错误处理** - 优化了错误处理机制

详细信息请参阅 [PHP8_COMPATIBILITY_FIXES.md](PHP8_COMPATIBILITY_FIXES.md)

## 📖 使用说明

### 基本配置

1. 访问后台管理: `http://your-domain.com/admin/`
2. 默认用户名: `admin`
3. 使用安装时设置的密码登录

### 创建内容

1. 登录后台
2. 点击 "新建内容"
3. 编写您的文章
4. 点击 "发布"

### 主题和插件

- 主题位置: `bl-themes/`
- 插件位置: `bl-plugins/`
- 在后台可以直接启用/禁用

## 🧪 兼容性测试

运行测试脚本验证 PHP 兼容性:

```bash
php php83_compatibility_test.php
```

## 📁 目录结构

```
mg-bludit/
├── bl-content/          # 内容和数据库
│   ├── databases/       # JSON 数据库文件
│   ├── pages/           # 页面内容
│   ├── uploads/         # 上传的文件
│   └── workspaces/      # 工作空间
├── bl-kernel/           # 核心代码
├── bl-languages/        # 语言文件
├── bl-plugins/          # 插件
├── bl-themes/           # 主题
├── index.php            # 入口文件
└── install.php          # 安装程序
```

## 🔄 版本信息

- **Maigewan CMS 版本**: 1.0.0
- **基于**: Bludit 3.16.2 (Valencia)
- **发布日期**: 2025-11-07
- **PHP 兼容性**: 8.0+

## 🤝 贡献

欢迎贡献代码！请遵循以下步骤:

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

## 📝 更新日志

### v1.0.0 (2025-11-07)

- ✅ PHP 8.3 完全兼容
- ✅ 修复语法错误
- ✅ 优化错误处理
- ✅ 添加兼容性测试脚本

## 📄 许可证

基于原 Bludit 项目的 MIT 许可证。

## 🔗 相关链接

- [官方文档](https://docs.bludit.com)
- [问题反馈](https://github.com/3mmmbulus/mg-bludit/issues)
- [原始 Bludit 项目](https://github.com/bludit/bludit)

## 💡 支持

如有问题或建议，请提交 Issue 或联系我们。

---

**Maigewan CMS** - 简单、快速、安全的内容管理系统 🎯
