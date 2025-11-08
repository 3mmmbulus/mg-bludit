# Bootstrap 5 升级完成报告

## ✅ 升级概况

**升级时间**: 2025-11-08  
**升级范围**: Maigewan CMS 后台管理系统 (Booty 主题)  
**Bootstrap 版本**: 4.6.2 → 5.3.3

---

## 📦 文件变更

### 核心文件升级

| 文件 | 旧版本 | 新版本 | 大小变化 |
|------|--------|--------|----------|
| `css/bootstrap.min.css` | 4.6.2 (159KB) | 5.3.3 (228KB) | +43% |
| `js/bootstrap.bundle.min.js` | 4.6.2 (82KB) | 5.3.3 (79KB) | -4% |

### 新增文件

```
✅ css/bootstrap-icons/bootstrap-icons.css (86KB)
✅ css/bootstrap-icons/fonts/bootstrap-icons.woff2 (127KB)
✅ css/select2-bootstrap5.min.css (31KB)
✅ css/bootstrap5-compat.css (兼容性补丁)
✅ js/bootstrap5-compat.js (自动转换 data-* 属性)
✅ admin/themes/booty/css/bootstrap5-compat.css
```

### 删除文件

```
❌ css/bootstrap-v4.6.2.bak.css (备份已删除)
❌ js/bootstrap-v4.6.2.bak.js (备份已删除)
❌ css/select2-bootstrap4.min.css (旧主题)
```

---

## 🔄 语法变更

### HTML 属性更新

| Bootstrap 4 | Bootstrap 5 | 影响文件 |
|-------------|-------------|----------|
| `data-toggle` | `data-bs-toggle` | 所有视图文件 |
| `data-target` | `data-bs-target` | 模态框、下拉菜单 |
| `data-dismiss` | `data-bs-dismiss` | 模态框关闭按钮 |
| `data-placement` | `data-bs-placement` | 工具提示、弹出框 |

### CSS 类名更新

| Bootstrap 4 | Bootstrap 5 | 说明 |
|-------------|-------------|------|
| `.form-group` | `.mb-3` | 表单间距 |
| `.custom-select` | `.form-select` | 下拉选择框 |
| `.custom-file` | `.form-control[type=file]` | 文件上传 |
| `.custom-file-input` | `.form-control` | 文件输入框 |
| `.custom-file-label` | `.form-label` | 文件标签 |

---

## 📝 修改文件列表

### 主题文件 (3 个)

```
✅ mgw-kernel/admin/themes/booty/index.php
   - 更新 Bootstrap CSS/JS 引用
   - 添加 Bootstrap Icons
   - 添加兼容性补丁引用

✅ mgw-kernel/admin/themes/booty/login.php
   - 更新 Bootstrap CSS/JS 引用
   - 添加 Bootstrap Icons
   - 添加兼容性补丁引用

✅ mgw-kernel/admin/themes/booty/init.php
   - 更新 modal() 方法: data-dismiss → data-bs-dismiss
   - 更新 formSelect(): custom-select → form-select
   - 更新 formSelectBlock(): custom-select → form-select
   - 更新 formInputFile(): custom-file → form-control
   - 更新 form-group → mb-3
```

### 视图文件 (20 个)

所有 `mgw-kernel/admin/views/*.php` 文件已批量更新：

```
✅ data-toggle → data-bs-toggle
✅ data-target → data-bs-target
✅ data-dismiss → data-bs-dismiss
✅ form-group → mb-3
✅ custom-file → form-control (edit-user.php)
```

---

## 🎨 新特性

### Bootstrap Icons

现已集成 Bootstrap Icons 1.11.3：

```html
<!-- 旧图标 (Font Awesome) -->
<i class="fa fa-home"></i>

<!-- 新图标 (Bootstrap Icons，可选) -->
<i class="bi bi-house"></i>
```

**图标数量**: 2000+ 个图标可用

### 改进的表单组件

```html
<!-- 文件上传 (Bootstrap 5) -->
<div class="mb-3">
  <label for="file" class="form-label">上传文件</label>
  <input type="file" class="form-control" id="file">
</div>

<!-- 下拉选择 (Bootstrap 5) -->
<select class="form-select">
  <option>选择...</option>
</select>
```

---

## 🔧 兼容性处理

### 保留 jQuery

虽然 Bootstrap 5 不再依赖 jQuery，但以下插件仍需要：

- ✅ Select2 (下拉框增强)
- ✅ DateTimePicker (日期选择器)
- ✅ Sortable (拖拽排序)

**jQuery 版本**: 保留原版本

### 自动兼容脚本

`bootstrap5-compat.js` 会自动转换：

```javascript
// 页面加载时自动执行
data-toggle → data-bs-toggle
data-target → data-bs-target
data-dismiss → data-bs-dismiss
```

### 向后兼容 CSS

`bootstrap5-compat.css` 提供：

```css
/* 保留 Bootstrap 4 类名支持 */
.form-group { margin-bottom: 1rem; }
.custom-select { /* 模拟 form-select */ }
.custom-file { /* 模拟新文件上传 */ }
```

---

## ✅ 测试清单

### 已验证功能

- [x] 登录页面显示正常
- [x] 后台首页布局正常
- [x] 侧边栏菜单展开/收起
- [x] 模态框打开/关闭 (data-bs-dismiss)
- [x] 标签页切换 (data-bs-toggle="tab")
- [x] 下拉菜单 (form-select)
- [x] 文件上传 (form-control[type=file])
- [x] 表单样式 (mb-3)
- [x] 按钮样式
- [x] 表格样式

### 待测试功能

- [ ] 内容编辑器
- [ ] 图片上传
- [ ] 插件配置
- [ ] 主题切换
- [ ] 响应式布局 (移动端)

---

## 🚀 性能提升

### JavaScript 体积

```
Bootstrap 4.6.2: 82KB
Bootstrap 5.3.3: 79KB
减少: 3KB (-4%)
```

### 不再依赖 Popper.js 单独引入

Bootstrap 5 的 `bootstrap.bundle.min.js` 已包含 Popper.js，无需单独加载。

---

## 📚 升级优势

### 1. 现代化
- ✅ 最新的 CSS Grid 和 Flexbox 支持
- ✅ 改进的表单组件
- ✅ 更好的可访问性

### 2. 性能
- ✅ 更小的 JavaScript 体积
- ✅ 不依赖 jQuery (核心功能)
- ✅ 更快的渲染速度

### 3. 可维护性
- ✅ 简化的 HTML 结构
- ✅ 统一的命名规范 (data-bs-*)
- ✅ 更好的文档支持

---

## ⚠️ 注意事项

### jQuery 保留原因

虽然 Bootstrap 5 不需要 jQuery，但以下功能仍依赖：

1. **Select2** - 高级下拉框
2. **DateTimePicker** - 日期时间选择
3. **Sortable** - 拖拽排序
4. **旧代码** - dashboard.php, plugins.php 等文件中的 jQuery 代码

**未来计划**: 逐步迁移到原生 JavaScript 或寻找无依赖的替代品。

### 兼容性脚本

`bootstrap5-compat.js` 会在页面加载时自动转换旧属性，如果发现某些功能不工作：

1. 检查浏览器控制台是否有错误
2. 确认脚本已正确加载
3. 手动添加 `data-bs-*` 属性

---

## 🔗 相关资源

- [Bootstrap 5 官方文档](https://getbootstrap.com/docs/5.3/getting-started/introduction/)
- [Bootstrap 5 迁移指南](https://getbootstrap.com/docs/5.3/migration/)
- [Bootstrap Icons](https://icons.getbootstrap.com/)
- [Select2 Bootstrap 5 主题](https://select2.github.io/select2-bootstrap-theme/)

---

## 📞 回滚方案

如需回滚到 Bootstrap 4：

```bash
cd /www/wwwroot/103.181.135.146/mgw-kernel

# 恢复 Bootstrap 4 文件
# (备份已删除，需要重新下载)
curl -L https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css -o css/bootstrap.min.css
curl -L https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js -o js/bootstrap.bundle.min.js

# 恢复旧的 select2 主题
curl -L https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css -o css/select2-bootstrap4.min.css

# 删除兼容性文件
rm css/bootstrap5-compat.css js/bootstrap5-compat.js
rm admin/themes/booty/css/bootstrap5-compat.css

# 使用 Git 回滚代码修改
git checkout -- admin/
```

---

## ✅ 升级完成

**状态**: ✅ 成功  
**版本**: Bootstrap 5.3.3  
**日期**: 2025-11-08  
**下一步**: 测试所有功能并优化界面
