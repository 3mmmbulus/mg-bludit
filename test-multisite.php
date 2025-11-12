<?php
// 测试多站点识别功能

// 模拟不同的 HTTP_HOST 来测试站点识别
$testHosts = [
    'example.com',
    'www.example.com',
    'unknown.com',
    'localhost'
];

echo "=== Maigewan CMS 多站点识别测试 ===\n\n";

foreach ($testHosts as $host) {
    echo "测试域名: $host\n";
    
    // 模拟站点识别逻辑
    $siteIdentifier = '_default';
    $rootPath = '/www/wwwroot/103.181.135.146/';
    
    // 移除端口号
    $cleanHost = preg_replace('/:\d+$/', '', $host);
    
    // 尝试精确匹配
    $siteDir = $rootPath . 'mgw-content/' . $cleanHost;
    if (is_dir($siteDir)) {
        $siteIdentifier = $cleanHost;
        echo "  ✓ 精确匹配: $siteIdentifier\n";
    } else {
        // 尝试去掉 www 前缀
        if (strpos($cleanHost, 'www.') === 0) {
            $hostWithoutWww = substr($cleanHost, 4);
            $siteDir = $rootPath . 'mgw-content/' . $hostWithoutWww;
            if (is_dir($siteDir)) {
                $siteIdentifier = $hostWithoutWww;
                echo "  ✓ WWW回退匹配: $siteIdentifier\n";
            } else {
                echo "  ✓ 使用默认站点: $siteIdentifier\n";
            }
        } else {
            echo "  ✓ 使用默认站点: $siteIdentifier\n";
        }
    }
    
    // 显示对应的内容路径
    $contentPath = $rootPath . 'mgw-content/' . $siteIdentifier . '/';
    echo "  内容路径: $contentPath\n";
    
    // 检查站点配置文件
    $siteConfigFile = $contentPath . 'databases/site.php';
    if (file_exists($siteConfigFile)) {
        echo "  配置文件: ✓ 存在\n";
        
        // 读取站点标题
        $content = file_get_contents($siteConfigFile);
        if (preg_match('/"title":\s*"([^"]+)"/', $content, $matches)) {
            echo "  站点标题: {$matches[1]}\n";
        }
    } else {
        echo "  配置文件: ✗ 不存在\n";
    }
    
    echo "\n";
}

echo "=== 测试完成 ===\n";
echo "\n目录结构:\n";
system('ls -la /www/wwwroot/103.181.135.146/mgw-content/');
