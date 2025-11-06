#!/bin/bash
# Maigewan Admin UI 自动部署脚本
# 用途：构建前端并部署到生产目录

set -e  # 遇到错误立即退出

echo "🚀 开始部署 Maigewan Admin UI..."

# 获取脚本所在目录
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo "📁 项目目录: $SCRIPT_DIR"
echo "📁 根目录: $PROJECT_ROOT"

# 进入前端项目目录
cd "$SCRIPT_DIR"

# 检查 pnpm 是否安装
if ! command -v pnpm &> /dev/null; then
    echo "❌ 错误: pnpm 未安装"
    echo "请运行: npm install -g pnpm"
    exit 1
fi

# 检查依赖是否安装
if [ ! -d "node_modules" ]; then
    echo "📦 安装依赖..."
    pnpm install
fi

# 构建生产版本
echo "🔨 构建生产版本..."
pnpm build

# 检查构建是否成功
if [ ! -d "dist" ]; then
    echo "❌ 构建失败: dist 目录不存在"
    exit 1
fi

# 目标部署目录
DEPLOY_DIR="$PROJECT_ROOT/mgw-kernel/admin-ui"

# 创建部署目录（如果不存在）
if [ ! -d "$DEPLOY_DIR" ]; then
    echo "📁 创建部署目录: $DEPLOY_DIR"
    mkdir -p "$DEPLOY_DIR"
fi

# 清理旧文件
echo "🧹 清理旧版本..."
rm -rf "$DEPLOY_DIR"/*

# 复制新文件
echo "📋 复制文件到部署目录..."
cp -r dist/* "$DEPLOY_DIR/"

# 创建版本信息
echo "📝 记录版本信息..."
cat > "$DEPLOY_DIR/version.json" << EOF
{
  "version": "1.0.0",
  "buildTime": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")",
  "buildTimestamp": $(date +%s),
  "deployPath": "$DEPLOY_DIR"
}
EOF

# 显示部署结果
echo ""
echo "✅ 部署完成！"
echo "📊 统计信息:"
echo "   - 文件数量: $(find "$DEPLOY_DIR" -type f | wc -l)"
echo "   - 总大小: $(du -sh "$DEPLOY_DIR" | cut -f1)"
echo "📁 部署位置: $DEPLOY_DIR"
echo ""
echo "🌐 访问地址: http://yoursite.com/admin"
echo ""
