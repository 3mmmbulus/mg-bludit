<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

$pageLangFile = PATH_LANGUAGES . 'pages/log-access/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$layout['title'] .= ' - ' . $L->g('log-access-title');

if (!function_exists('mgwLogAccessReadJsonWithGuard')) {
    function mgwLogAccessReadJsonWithGuard($filePath)
    {
        if (!file_exists($filePath)) {
            return array();
        }

        $lines = @file($filePath);
        if ($lines === false) {
            return array();
        }

        if (!empty($lines)) {
            $firstLine = trim((string)reset($lines));
            if (strpos($firstLine, '<?php') === 0) {
                array_shift($lines);
            }
        }

        $payload = trim(implode('', $lines));
        if ($payload === '') {
            return array();
        }

        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('mgwLogAccessFormatDateTime')) {
    function mgwLogAccessFormatDateTime($timestamp)
    {
        if (!is_numeric($timestamp) || $timestamp <= 0) {
            return '--';
        }
        return date('Y-m-d H:i:s', (int)$timestamp);
    }
}

if (!function_exists('mgwLogAccessFormatDuration')) {
    function mgwLogAccessFormatDuration($milliseconds)
    {
        if (!is_numeric($milliseconds)) {
            return '--';
        }
        $ms = (float)$milliseconds;
        if ($ms >= 1000) {
            return number_format($ms / 1000, 2) . ' s';
        }
        return number_format($ms, 2) . ' ms';
    }
}

if (!function_exists('mgwLogAccessFormatBytesLabel')) {
    function mgwLogAccessFormatBytesLabel($bytes)
    {
        if (!is_numeric($bytes)) {
            return '0 B';
        }
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $value = (float)$bytes;
        $index = 0;
        while ($value >= 1024 && $index < count($units) - 1) {
            $value /= 1024;
            $index++;
        }
        return number_format($value, $value >= 10 ? 1 : 2) . ' ' . $units[$index];
    }
}

if (!function_exists('mgwLogAccessStatusBadge')) {
    function mgwLogAccessStatusBadge($status)
    {
        if (!is_numeric($status)) {
            return 'secondary';
        }
        $status = (int)$status;
        if ($status >= 500) {
            return 'danger';
        }
        if ($status >= 400) {
            return 'warning';
        }
        if ($status >= 300) {
            return 'info';
        }
        if ($status >= 200) {
            return 'success';
        }
        return 'secondary';
    }
}

$overviewCards = array();
$trafficTrend = array();
$statusDistribution = array();
$sourceBreakdown = array();
$siteAccessMatrix = array();
$realtimeLogs = array();
$recentLogs = array();
$suspiciousAlerts = array();
$exportOptions = array();
$logRetention = array();

$globalCandidates = array(
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'access-log.php',
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'access-log.json',
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'log-access-center.php'
);

$globalMetrics = array();
foreach ($globalCandidates as $candidate) {
    if (!empty($globalMetrics)) {
        break;
    }
    $globalMetrics = mgwLogAccessReadJsonWithGuard($candidate);
}

$totals = isset($globalMetrics['totals']) && is_array($globalMetrics['totals']) ? $globalMetrics['totals'] : $globalMetrics;
$totalRequests = isset($totals['requests']) ? (int)$totals['requests'] : 0;
$uniqueVisitors = isset($totals['uniqueVisitors']) ? (int)$totals['uniqueVisitors'] : 0;
$dataTransfer = isset($totals['bandwidth']) ? (float)$totals['bandwidth'] : 0;
$suspiciousCount = isset($totals['suspicious']) ? (int)$totals['suspicious'] : 0;
$lastRequest = isset($totals['lastRequest']) ? (int)$totals['lastRequest'] : null;

$overviewCards[] = array(
    'label' => $L->g('log-access-card-requests'),
    'value' => number_format($totalRequests),
    'hint' => $L->g('log-access-card-requests-hint')
);
$overviewCards[] = array(
    'label' => $L->g('log-access-card-visitors'),
    'value' => number_format($uniqueVisitors),
    'hint' => $L->g('log-access-card-visitors-hint')
);
$overviewCards[] = array(
    'label' => $L->g('log-access-card-bandwidth'),
    'value' => mgwLogAccessFormatBytesLabel($dataTransfer),
    'hint' => $L->g('log-access-card-bandwidth-hint')
);
$overviewCards[] = array(
    'label' => $L->g('log-access-card-suspicious'),
    'value' => number_format($suspiciousCount),
    'hint' => $L->g('log-access-card-suspicious-hint'),
    'status' => $suspiciousCount > 0 ? 'warn' : 'ok'
);

$trafficTrend = isset($globalMetrics['trend']) && is_array($globalMetrics['trend']) ? $globalMetrics['trend'] : array();
$statusDistribution = isset($globalMetrics['status']) && is_array($globalMetrics['status']) ? $globalMetrics['status'] : array();
$sourceBreakdown = isset($globalMetrics['sources']) && is_array($globalMetrics['sources']) ? $globalMetrics['sources'] : array();
$suspiciousAlerts = isset($globalMetrics['alerts']) && is_array($globalMetrics['alerts']) ? $globalMetrics['alerts'] : array();
$logRetention = isset($globalMetrics['retention']) && is_array($globalMetrics['retention']) ? $globalMetrics['retention'] : array();

$recentLogs = isset($globalMetrics['recent']) && is_array($globalMetrics['recent']) ? $globalMetrics['recent'] : array();
$realtimeLogs = isset($globalMetrics['realtime']) && is_array($globalMetrics['realtime']) ? $globalMetrics['realtime'] : $recentLogs;

$exportOptions = array(
    array('format' => 'csv', 'label' => $L->g('log-access-export-csv')),
    array('format' => 'json', 'label' => $L->g('log-access-export-json'))
);

$sitesRoot = PATH_ROOT . 'mgw-content' . DS;
$siteDirectories = glob($sitesRoot . '*', GLOB_ONLYDIR);
if ($siteDirectories === false) {
    $siteDirectories = array();
}

$maxRequests = 0;
$highFrequencyIps = array();
$crawlerHits = 0;

foreach ($siteDirectories as $directory) {
    $slug = basename($directory);
    if ($slug === '' || $slug[0] === '.') {
        continue;
    }

    $siteMeta = mgwLogAccessReadJsonWithGuard($directory . DS . 'databases' . DS . 'site.php');
    $siteLabel = isset($siteMeta['title']) && is_string($siteMeta['title']) && $siteMeta['title'] !== '' ? $siteMeta['title'] : $slug;

    $siteLogMetrics = mgwLogAccessReadJsonWithGuard($directory . DS . 'tmp' . DS . 'access-log.php');
    if (empty($siteLogMetrics)) {
        $siteLogMetrics = mgwLogAccessReadJsonWithGuard($directory . DS . 'tmp' . DS . 'access-log.json');
    }

    $siteRequests = isset($siteLogMetrics['requests']) ? (int)$siteLogMetrics['requests'] : (isset($siteLogMetrics['totals']['requests']) ? (int)$siteLogMetrics['totals']['requests'] : 0);
    $siteVisitors = isset($siteLogMetrics['uniqueVisitors']) ? (int)$siteLogMetrics['uniqueVisitors'] : (isset($siteLogMetrics['totals']['uniqueVisitors']) ? (int)$siteLogMetrics['totals']['uniqueVisitors'] : 0);
    $siteBandwidth = isset($siteLogMetrics['bandwidth']) ? (float)$siteLogMetrics['bandwidth'] : (isset($siteLogMetrics['totals']['bandwidth']) ? (float)$siteLogMetrics['totals']['bandwidth'] : 0);
    $siteSuspicious = isset($siteLogMetrics['suspicious']) ? (int)$siteLogMetrics['suspicious'] : (isset($siteLogMetrics['totals']['suspicious']) ? (int)$siteLogMetrics['totals']['suspicious'] : 0);
    $siteLastRequest = isset($siteLogMetrics['lastRequest']) ? (int)$siteLogMetrics['lastRequest'] : (isset($siteLogMetrics['totals']['lastRequest']) ? (int)$siteLogMetrics['totals']['lastRequest'] : null);

    $maxRequests = max($maxRequests, $siteRequests);

    $siteAccessMatrix[] = array(
        'slug' => $slug,
        'label' => $siteLabel,
        'requests' => $siteRequests,
        'visitors' => $siteVisitors,
        'bandwidth' => mgwLogAccessFormatBytesLabel($siteBandwidth),
        'suspicious' => $siteSuspicious,
        'lastRequest' => $siteLastRequest
    );

    if (isset($siteLogMetrics['topIps']) && is_array($siteLogMetrics['topIps'])) {
        foreach ($siteLogMetrics['topIps'] as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $ip = isset($entry['ip']) ? (string)$entry['ip'] : '';
            $count = isset($entry['count']) ? (int)$entry['count'] : 0;
            if ($ip === '') {
                continue;
            }
            if (!isset($highFrequencyIps[$ip])) {
                $highFrequencyIps[$ip] = 0;
            }
            $highFrequencyIps[$ip] += $count;
        }
    }

    if (isset($siteLogMetrics['agents']) && is_array($siteLogMetrics['agents'])) {
        foreach ($siteLogMetrics['agents'] as $agent => $count) {
            if (stripos((string)$agent, 'bot') !== false || stripos((string)$agent, 'spider') !== false || stripos((string)$agent, 'crawler') !== false) {
                $crawlerHits += is_numeric($count) ? (int)$count : 0;
            }
        }
    }
}

arsort($highFrequencyIps);
$highFrequencyIps = array_slice($highFrequencyIps, 0, 5, true);

if (!empty($highFrequencyIps)) {
    $suspiciousAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('log-access-alert-high-ip'),
        'description' => sprintf($L->g('log-access-alert-high-ip-desc'), key($highFrequencyIps), number_format(reset($highFrequencyIps)))
    );
}

if ($crawlerHits > 0) {
    $suspiciousAlerts[] = array(
        'status' => 'info',
        'title' => $L->g('log-access-alert-crawler'),
        'description' => sprintf($L->g('log-access-alert-crawler-desc'), number_format($crawlerHits))
    );
}

if ($suspiciousCount > 0 && $lastRequest !== null && $lastRequest >= strtotime('-1 hour')) {
    $suspiciousAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('log-access-alert-recent-block'),
        'description' => $L->g('log-access-alert-recent-block-desc')
    );
}

if (empty($trafficTrend)) {
    $trafficTrend = array(
        array('date' => date('Y-m-d', strtotime('-6 days')), 'pv' => 1200, 'uv' => 340),
        array('date' => date('Y-m-d', strtotime('-5 days')), 'pv' => 1450, 'uv' => 380),
        array('date' => date('Y-m-d', strtotime('-4 days')), 'pv' => 1310, 'uv' => 360),
        array('date' => date('Y-m-d', strtotime('-3 days')), 'pv' => 1575, 'uv' => 420),
        array('date' => date('Y-m-d', strtotime('-2 days')), 'pv' => 1688, 'uv' => 455),
        array('date' => date('Y-m-d', strtotime('-1 day')), 'pv' => 1760, 'uv' => 470),
        array('date' => date('Y-m-d'), 'pv' => 1820, 'uv' => 498)
    );
}

if (empty($statusDistribution)) {
    $statusDistribution = array(
        array('status' => 200, 'count' => 8650),
        array('status' => 301, 'count' => 1420),
        array('status' => 404, 'count' => 320),
        array('status' => 500, 'count' => 48)
    );
}

if (empty($sourceBreakdown)) {
    $sourceBreakdown = array(
        array('source' => 'Direct', 'count' => 3820),
        array('source' => 'Organic', 'count' => 2440),
        array('source' => 'Referral', 'count' => 670),
        array('source' => 'Paid', 'count' => 320)
    );
}

if (empty($realtimeLogs)) {
    $realtimeLogs = array();
}

if (empty($recentLogs)) {
    $recentLogs = $realtimeLogs;
}

if (empty($realtimeLogs)) {
    $realtimeLogs = array(
        array(
            'time' => time() - 35,
            'site' => 'example.com',
            'ip' => '203.0.113.45',
            'method' => 'GET',
            'url' => '/blog/how-to-backup',
            'status' => 200,
            'referer' => 'https://google.com',
            'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'duration' => 184
        ),
        array(
            'time' => time() - 28,
            'site' => 'example.com',
            'ip' => '198.51.100.88',
            'method' => 'POST',
            'url' => '/admin/login',
            'status' => 302,
            'referer' => 'https://example.com/admin',
            'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_3)',
            'duration' => 312
        ),
        array(
            'time' => time() - 20,
            'site' => '1dun.net',
            'ip' => '192.0.2.55',
            'method' => 'GET',
            'url' => '/wp-login.php',
            'status' => 404,
            'referer' => '-',
            'ua' => 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
            'duration' => 96
        )
    );
}

if (empty($recentLogs)) {
    $recentLogs = $realtimeLogs;
}

if (empty($siteAccessMatrix)) {
    $siteAccessMatrix[] = array(
        'slug' => 'example.com',
        'label' => 'example.com',
        'requests' => 4820,
        'visitors' => 1120,
        'bandwidth' => mgwLogAccessFormatBytesLabel(2.8 * 1024 * 1024 * 1024),
        'suspicious' => 12,
        'lastRequest' => time() - 120
    );
    $siteAccessMatrix[] = array(
        'slug' => '1dun.net',
        'label' => '1dun.net',
        'requests' => 3720,
        'visitors' => 920,
        'bandwidth' => mgwLogAccessFormatBytesLabel(1.9 * 1024 * 1024 * 1024),
        'suspicious' => 24,
        'lastRequest' => time() - 85
    );
}

if (empty($suspiciousAlerts)) {
    $suspiciousAlerts[] = array(
        'status' => 'info',
        'title' => $L->g('log-access-alert-demo-title'),
        'description' => $L->g('log-access-alert-demo-desc')
    );
}

if (empty($logRetention)) {
    $logRetention = array(
        'days' => 30,
        'autoClean' => true,
        'lastClean' => time() - 3600 * 26
    );
}

usort($realtimeLogs, function ($a, $b) {
    return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
});

$realtimeLogs = array_slice(array_values($realtimeLogs), 0, 25);
$recentLogs = array_slice(array_values($recentLogs), 0, 100);

usort($siteAccessMatrix, function ($a, $b) {
    return ($b['requests'] ?? 0) <=> ($a['requests'] ?? 0);
});

$logAccessPayload = array(
    'overviewCards' => array_values($overviewCards),
    'trafficTrend' => array_values($trafficTrend),
    'statusDistribution' => array_values($statusDistribution),
    'sourceBreakdown' => array_values($sourceBreakdown),
    'siteAccessMatrix' => array_values($siteAccessMatrix),
    'realtimeLogs' => array_values($realtimeLogs),
    'recentLogs' => array_values($recentLogs),
    'suspiciousAlerts' => array_values($suspiciousAlerts),
    'exportOptions' => array_values($exportOptions),
    'logRetention' => $logRetention
);

extract($logAccessPayload, EXTR_OVERWRITE);