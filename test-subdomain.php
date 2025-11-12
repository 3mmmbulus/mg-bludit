<?php
// 测试子域名识别功能

$testHosts = [
    '1dun.co',
    'www.1dun.co',
    'download.1dun.co',
    'api.1dun.co',
    'fslkajfkljds.1dun.co',
    'sub.download.1dun.co',
    'example.com',
    'www.example.com',
    'unknown.com',
    'test.unknown.com'
];

echo "=== 多站点子域名识别测试 ===\n\n";

foreach ($testHosts as $host) {
    echo "访问域名: $host\n";
    
    $siteIdentifier = '_default';
    $rootPath = '/www/wwwroot/103.181.135.146/';
    
    // 移除端口号
    $cleanHost = preg_replace('/:\d+$/', '', $host);
    
    // 1. 尝试精确匹配
    $siteDir = $rootPath . 'mgw-content/' . $cleanHost;
    if (is_dir($siteDir)) {
        $siteIdentifier = $cleanHost;
        echo "  ✓ 精确匹配: $siteIdentifier\n";
    } else {
        // 2. 尝试匹配主域名
        $parts = explode('.', $cleanHost);
        $partsCount = count($parts);
        
        if ($partsCount >= 3) {
            // 取最后两部分作为主域名
            $mainDomain = $parts[$partsCount - 2] . '.' . $parts[$partsCount - 1];
            $siteDir = $rootPath . 'mgw-content/' . $mainDomain;
            if (is_dir($siteDir)) {
                $siteIdentifier = $mainDomain;
                echo "  ✓ 主域名匹配: $siteIdentifier (子域名: {$parts[0]})\n";
            } else {
                echo "  ✓ 使用默认站点: $siteIdentifier (主域名 $mainDomain 不存在)\n";
            }
        }
        // 如果是 www 开头
        elseif (strpos($cleanHost, 'www.') === 0) {
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
    
    // 显示对应的路径
    $contentPath = $rootPath . 'mgw-content/' . $siteIdentifier . '/';
    $pluginPath = $rootPath . 'mgw-config/plugins/' . $siteIdentifier . '/';
    echo "  内容路径: $contentPath\n";
    echo "  插件配置: $pluginPath\n";
    
    echo "\n";
}

echo "=== 测试完成 ===\n";
