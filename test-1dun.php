<?php
// 测试 1dun.co 站点
$rootPath = '/www/wwwroot/103.181.135.146/';

echo "=== 测试 1dun.co 站点 ===\n\n";

// 检查站点目录
$siteDir = $rootPath . 'mgw-content/1dun.co';
if (is_dir($siteDir)) {
    echo "✓ 站点目录存在: $siteDir\n";
    
    // 检查配置文件
    $configFile = $siteDir . '/databases/site.php';
    if (file_exists($configFile)) {
        echo "✓ 配置文件存在\n";
        
        $content = file_get_contents($configFile);
        if (preg_match('/"title":\s*"([^"]+)"/', $content, $matches)) {
            echo "  站点标题: {$matches[1]}\n";
        }
        if (preg_match('/"slogan":\s*"([^"]+)"/', $content, $matches)) {
            echo "  站点口号: {$matches[1]}\n";
        }
    }
    
    // 检查页面文件
    $pagesFile = $siteDir . '/databases/pages.php';
    if (file_exists($pagesFile)) {
        echo "✓ 页面文件存在\n";
        $content = file_get_contents($pagesFile);
        $pages = preg_match_all('/"key":\s*"([^"]+)"/', $content, $matches);
        echo "  页面数量: " . count($matches[1]) . "\n";
        echo "  页面列表: " . implode(', ', $matches[1]) . "\n";
    }
    
    // 检查其他文件
    $files = ['categories.php', 'tags.php', 'security.php', 'syslog.php'];
    foreach ($files as $file) {
        if (file_exists($siteDir . '/databases/' . $file)) {
            echo "✓ $file 存在\n";
        }
    }
} else {
    echo "✗ 站点目录不存在\n";
}

echo "\n=== 测试完成 ===\n";
