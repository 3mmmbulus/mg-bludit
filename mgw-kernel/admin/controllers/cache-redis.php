<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

$pageLangFile = PATH_LANGUAGES . 'pages/cache-redis/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$layout['title'] .= ' - ' . $L->g('cache-redis-title');

if (!function_exists('mgwCacheRedisReadJsonWithGuard')) {
    function mgwCacheRedisReadJsonWithGuard($filePath)
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
        if ($payload === '' || $payload === '{}' || $payload === '[]') {
            return array();
        }

        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('mgwCacheRedisFormatBytes')) {
    function mgwCacheRedisFormatBytes($bytes)
    {
        if (!is_numeric($bytes)) {
            return array('value' => 0, 'label' => '0 B');
        }

        $bytes = (float)$bytes;
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $index = 0;
        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return array('value' => $bytes, 'label' => number_format($bytes, $bytes >= 10 ? 1 : 2) . ' ' . $units[$index]);
    }
}

if (!function_exists('mgwCacheRedisPercent')) {
    function mgwCacheRedisPercent($numerator, $denominator)
    {
        if (!is_numeric($numerator) || !is_numeric($denominator) || $denominator <= 0) {
            return 0;
        }
        return ($numerator / $denominator) * 100;
    }
}

if (!function_exists('mgwCacheRedisStatusFromRatio')) {
    function mgwCacheRedisStatusFromRatio($ratio, $warn = 70, $fail = 85)
    {
        if (!is_numeric($ratio)) {
            return 'unknown';
        }

        if ($ratio >= $fail) {
            return 'fail';
        }

        if ($ratio >= $warn) {
            return 'warn';
        }

        return 'ok';
    }
}

$redisOverview = array();
$redisPerformanceCards = array();
$redisSlowlog = array();
$localCacheSummary = array();
$localCacheTools = array();
$siteCacheMatrix = array();
$prewarmJobs = array();
$expirationPolicies = array();
$cacheKeySamples = array();
$cacheAlerts = array();
$cacheHeatmap = array();

$globalCacheCandidates = array(
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'cache-redis.php',
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'cache-redis.json',
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'cache-center.php',
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'redis-status.php'
);

$redisMetrics = array();
foreach ($globalCacheCandidates as $candidate) {
    if (!empty($redisMetrics)) {
        break;
    }
    $redisMetrics = mgwCacheRedisReadJsonWithGuard($candidate);
}

$redisInfo = isset($redisMetrics['redis']) && is_array($redisMetrics['redis']) ? $redisMetrics['redis'] : $redisMetrics;
$redisConnected = isset($redisInfo['connected']) ? (bool)$redisInfo['connected'] : false;
$redisMemoryUsed = isset($redisInfo['usedMemory']) ? (float)$redisInfo['usedMemory'] : (isset($redisInfo['used_memory']) ? (float)$redisInfo['used_memory'] : 0);
$redisMemoryPeak = isset($redisInfo['peakMemory']) ? (float)$redisInfo['peakMemory'] : (isset($redisInfo['used_memory_peak']) ? (float)$redisInfo['used_memory_peak'] : 0);
$redisMemoryLimit = isset($redisInfo['maxMemory']) ? (float)$redisInfo['maxMemory'] : (isset($redisInfo['maxmemory']) ? (float)$redisInfo['maxmemory'] : 0);
$redisKeys = isset($redisInfo['totalKeys']) ? (int)$redisInfo['totalKeys'] : (isset($redisInfo['db0']['keys']) ? (int)$redisInfo['db0']['keys'] : (isset($redisInfo['keys']) ? (int)$redisInfo['keys'] : 0));
$redisHitRate = isset($redisInfo['hitRate']) ? (float)$redisInfo['hitRate'] : (isset($redisInfo['keyspace_hits'], $redisInfo['keyspace_misses']) ? mgwCacheRedisPercent((float)$redisInfo['keyspace_hits'], (float)$redisInfo['keyspace_hits'] + (float)$redisInfo['keyspace_misses']) : null);
$redisUptime = isset($redisInfo['uptime']) ? (int)$redisInfo['uptime'] : (isset($redisInfo['uptime_in_seconds']) ? (int)$redisInfo['uptime_in_seconds'] : 0);
$redisLastSave = isset($redisInfo['lastSave']) ? (int)$redisInfo['lastSave'] : (isset($redisInfo['rdb_last_save_time']) ? (int)$redisInfo['rdb_last_save_time'] : 0);

$memoryFormatted = mgwCacheRedisFormatBytes($redisMemoryUsed);
$peakFormatted = mgwCacheRedisFormatBytes($redisMemoryPeak);
$limitFormatted = mgwCacheRedisFormatBytes($redisMemoryLimit);

$uptimeHours = $redisUptime > 0 ? round($redisUptime / 3600, 1) : 0;

$redisOverview[] = array(
    'label' => $L->g('cache-redis-card-connection'),
    'value' => $redisConnected ? $L->g('cache-redis-connected') : $L->g('cache-redis-disconnected'),
    'status' => $redisConnected ? 'ok' : 'fail',
    'hint' => $redisConnected ? $L->g('cache-redis-card-connection-hint-online') : $L->g('cache-redis-card-connection-hint-offline')
);

$redisOverview[] = array(
    'label' => $L->g('cache-redis-card-memory'),
    'value' => $memoryFormatted['label'],
    'status' => mgwCacheRedisStatusFromRatio($redisMemoryLimit > 0 ? ($redisMemoryUsed / $redisMemoryLimit) * 100 : null, 70, 90),
    'hint' => sprintf($L->g('cache-redis-card-memory-hint'), $peakFormatted['label'], $limitFormatted['label'] ?: '∞')
);

$redisOverview[] = array(
    'label' => $L->g('cache-redis-card-keys'),
    'value' => number_format($redisKeys),
    'status' => $redisKeys > 750000 ? 'fail' : ($redisKeys > 300000 ? 'warn' : 'ok'),
    'hint' => $L->g('cache-redis-card-keys-hint')
);

$redisOverview[] = array(
    'label' => $L->g('cache-redis-card-hit-rate'),
    'value' => $redisHitRate !== null ? number_format($redisHitRate, 1) . '%' : '--',
    'status' => $redisHitRate !== null ? ($redisHitRate >= 85 ? 'ok' : ($redisHitRate >= 65 ? 'warn' : 'fail')) : 'unknown',
    'hint' => $L->g('cache-redis-card-hit-rate-hint')
);

$redisOverview[] = array(
    'label' => $L->g('cache-redis-card-uptime'),
    'value' => $uptimeHours > 0 ? $uptimeHours . 'h' : '--',
    'status' => $uptimeHours > 0 ? 'ok' : 'unknown',
    'hint' => $redisLastSave > 0 ? sprintf($L->g('cache-redis-card-uptime-hint'), date('Y-m-d H:i', $redisLastSave)) : $L->g('cache-redis-card-uptime-hint-fallback')
);

$redisPerformanceData = isset($redisMetrics['performance']) && is_array($redisMetrics['performance']) ? $redisMetrics['performance'] : array();
$redisPerformanceCards[] = array(
    'label' => $L->g('cache-redis-performance-connections'),
    'value' => number_format(isset($redisPerformanceData['connections']) ? (int)$redisPerformanceData['connections'] : (isset($redisInfo['connected_clients']) ? (int)$redisInfo['connected_clients'] : 0)),
    'status' => 'ok',
    'trend' => isset($redisPerformanceData['connectionsTrend']) ? $redisPerformanceData['connectionsTrend'] : array()
);
$redisPerformanceCards[] = array(
    'label' => $L->g('cache-redis-performance-commands'),
    'value' => number_format(isset($redisPerformanceData['commandsPerSec']) ? (int)$redisPerformanceData['commandsPerSec'] : (isset($redisInfo['instantaneous_ops_per_sec']) ? (int)$redisInfo['instantaneous_ops_per_sec'] : 0)),
    'status' => 'ok',
    'trend' => isset($redisPerformanceData['commandsTrend']) ? $redisPerformanceData['commandsTrend'] : array()
);
$redisPerformanceCards[] = array(
    'label' => $L->g('cache-redis-performance-io-latency'),
    'value' => isset($redisPerformanceData['ioLatency']) ? number_format((float)$redisPerformanceData['ioLatency'], 2) . ' ms' : '--',
    'status' => mgwCacheRedisStatusFromRatio(isset($redisPerformanceData['ioLatency']) ? (float)$redisPerformanceData['ioLatency'] : null, 4, 8),
    'trend' => isset($redisPerformanceData['ioTrend']) ? $redisPerformanceData['ioTrend'] : array()
);
$redisPerformanceCards[] = array(
    'label' => $L->g('cache-redis-performance-evictions'),
    'value' => number_format(isset($redisPerformanceData['evictions']) ? (int)$redisPerformanceData['evictions'] : (isset($redisInfo['evicted_keys']) ? (int)$redisInfo['evicted_keys'] : 0)),
    'status' => isset($redisPerformanceData['evictions']) && $redisPerformanceData['evictions'] > 0 ? 'warn' : 'ok',
    'trend' => isset($redisPerformanceData['evictionTrend']) ? $redisPerformanceData['evictionTrend'] : array()
);

$redisSlowlog = isset($redisMetrics['slowlog']) && is_array($redisMetrics['slowlog']) ? $redisMetrics['slowlog'] : array();
if (empty($redisSlowlog) && isset($redisInfo['slowlog']) && is_array($redisInfo['slowlog'])) {
    $redisSlowlog = $redisInfo['slowlog'];
}

$localCacheMetrics = isset($redisMetrics['local']) && is_array($redisMetrics['local']) ? $redisMetrics['local'] : array();
$fileCacheBytes = isset($localCacheMetrics['file']['size']) ? (float)$localCacheMetrics['file']['size'] : 0;
$objectCacheBytes = isset($localCacheMetrics['object']['size']) ? (float)$localCacheMetrics['object']['size'] : 0;
$opcodeCacheBytes = isset($localCacheMetrics['opcode']['size']) ? (float)$localCacheMetrics['opcode']['size'] : 0;

$localCacheSummary = array(
    'file' => array(
        'size' => mgwCacheRedisFormatBytes($fileCacheBytes),
        'items' => isset($localCacheMetrics['file']['items']) ? (int)$localCacheMetrics['file']['items'] : 0,
        'path' => isset($localCacheMetrics['file']['path']) ? (string)$localCacheMetrics['file']['path'] : ''
    ),
    'object' => array(
        'size' => mgwCacheRedisFormatBytes($objectCacheBytes),
        'items' => isset($localCacheMetrics['object']['items']) ? (int)$localCacheMetrics['object']['items'] : 0
    ),
    'opcode' => array(
        'size' => mgwCacheRedisFormatBytes($opcodeCacheBytes),
        'hitRate' => isset($localCacheMetrics['opcode']['hitRate']) ? (float)$localCacheMetrics['opcode']['hitRate'] : null
    )
);

$localCacheTools = array(
    array(
        'label' => $L->g('cache-redis-tool-clear-file'),
        'description' => $L->g('cache-redis-tool-clear-file-desc'),
        'action' => 'file'
    ),
    array(
        'label' => $L->g('cache-redis-tool-clear-object'),
        'description' => $L->g('cache-redis-tool-clear-object-desc'),
        'action' => 'object'
    ),
    array(
        'label' => $L->g('cache-redis-tool-clear-opcode'),
        'description' => $L->g('cache-redis-tool-clear-opcode-desc'),
        'action' => 'opcode'
    )
);

$sitesRoot = PATH_ROOT . 'mgw-content' . DS;
$siteDirectories = glob($sitesRoot . '*', GLOB_ONLYDIR);
if ($siteDirectories === false) {
    $siteDirectories = array();
}

$siteKeyExplosions = 0;
$totalCacheSize = 0;
$totalNamespaces = array();

foreach ($siteDirectories as $directory) {
    $slug = basename($directory);
    if ($slug === '' || $slug[0] === '.') {
        continue;
    }

    $siteMeta = mgwCacheRedisReadJsonWithGuard($directory . DS . 'databases' . DS . 'site.php');
    $siteLabel = isset($siteMeta['title']) && is_string($siteMeta['title']) && $siteMeta['title'] !== '' ? $siteMeta['title'] : $slug;

    $siteCacheMetrics = mgwCacheRedisReadJsonWithGuard($directory . DS . 'tmp' . DS . 'cache-metrics.php');
    if (empty($siteCacheMetrics)) {
        $siteCacheMetrics = mgwCacheRedisReadJsonWithGuard($directory . DS . 'tmp' . DS . 'cache-metrics.json');
    }

    $redisNamespace = isset($siteCacheMetrics['redisNamespace']) ? (string)$siteCacheMetrics['redisNamespace'] : $slug;
    if (!isset($totalNamespaces[$redisNamespace])) {
        $totalNamespaces[$redisNamespace] = 0;
    }

    $redisKeyCount = isset($siteCacheMetrics['redis']['keys']) ? (int)$siteCacheMetrics['redis']['keys'] : 0;
    $fileCacheSize = isset($siteCacheMetrics['local']['fileSize']) ? (float)$siteCacheMetrics['local']['fileSize'] : 0;
    $objectCacheSize = isset($siteCacheMetrics['local']['objectSize']) ? (float)$siteCacheMetrics['local']['objectSize'] : 0;
    $pageCacheHit = isset($siteCacheMetrics['pageCache']['hitRate']) ? (float)$siteCacheMetrics['pageCache']['hitRate'] : null;
    $pageCacheTrend = isset($siteCacheMetrics['pageCache']['trend']) && is_array($siteCacheMetrics['pageCache']['trend']) ? $siteCacheMetrics['pageCache']['trend'] : array();
    $invalidations = isset($siteCacheMetrics['invalidations']) ? (int)$siteCacheMetrics['invalidations'] : 0;
    $prewarmStatus = isset($siteCacheMetrics['prewarm']['status']) ? (string)$siteCacheMetrics['prewarm']['status'] : 'idle';
    $prewarmLastRun = isset($siteCacheMetrics['prewarm']['lastRun']) ? (int)$siteCacheMetrics['prewarm']['lastRun'] : null;

    $totalNamespaces[$redisNamespace] += $redisKeyCount;
    $totalCacheSize += $fileCacheSize + $objectCacheSize;

    if ($redisKeyCount > 150000) {
        $siteKeyExplosions++;
    }

    $siteCacheMatrix[] = array(
        'slug' => $slug,
        'label' => $siteLabel,
        'redisNamespace' => $redisNamespace,
        'redisKeys' => $redisKeyCount,
        'localSize' => mgwCacheRedisFormatBytes($fileCacheSize + $objectCacheSize),
        'pageCacheHit' => $pageCacheHit,
        'pageCacheTrend' => $pageCacheTrend,
        'invalidations' => $invalidations,
        'prewarmStatus' => $prewarmStatus,
        'prewarmLastRun' => $prewarmLastRun
    );

    if (isset($siteCacheMetrics['prewarm']['jobs']) && is_array($siteCacheMetrics['prewarm']['jobs'])) {
        foreach ($siteCacheMetrics['prewarm']['jobs'] as $job) {
            if (!is_array($job)) {
                continue;
            }
            $prewarmJobs[] = array(
                'site' => $siteLabel,
                'pattern' => isset($job['pattern']) ? (string)$job['pattern'] : '',
                'status' => isset($job['status']) ? (string)$job['status'] : 'pending',
                'duration' => isset($job['duration']) ? (float)$job['duration'] : null,
                'cached' => isset($job['cached']) ? (int)$job['cached'] : null
            );
        }
    }

    if (isset($siteCacheMetrics['expiration']) && is_array($siteCacheMetrics['expiration'])) {
        foreach ($siteCacheMetrics['expiration'] as $policy) {
            if (!is_array($policy)) {
                continue;
            }
            $expirationPolicies[] = array(
                'site' => $siteLabel,
                'pattern' => isset($policy['pattern']) ? (string)$policy['pattern'] : '',
                'ttl' => isset($policy['ttl']) ? (int)$policy['ttl'] : null,
                'strategy' => isset($policy['strategy']) ? (string)$policy['strategy'] : 'ttl',
                'description' => isset($policy['description']) ? (string)$policy['description'] : ''
            );
        }
    }

    if (isset($siteCacheMetrics['samples']) && is_array($siteCacheMetrics['samples'])) {
        foreach ($siteCacheMetrics['samples'] as $sample) {
            if (!is_array($sample)) {
                continue;
            }
            $cacheKeySamples[] = array(
                'site' => $siteLabel,
                'key' => isset($sample['key']) ? (string)$sample['key'] : '',
                'ttl' => isset($sample['ttl']) ? (int)$sample['ttl'] : null,
                'size' => mgwCacheRedisFormatBytes(isset($sample['size']) ? (float)$sample['size'] : 0),
                'type' => isset($sample['type']) ? (string)$sample['type'] : ''
            );
        }
    }

    if (isset($siteCacheMetrics['hotKeys']) && is_array($siteCacheMetrics['hotKeys'])) {
        $cacheHeatmap[$siteLabel] = $siteCacheMetrics['hotKeys'];
    }

    if (isset($siteCacheMetrics['alerts']) && is_array($siteCacheMetrics['alerts'])) {
        foreach ($siteCacheMetrics['alerts'] as $alert) {
            if (!is_array($alert)) {
                continue;
            }
            $cacheAlerts[] = array(
                'status' => isset($alert['status']) ? (string)$alert['status'] : 'warn',
                'title' => isset($alert['title']) ? (string)$alert['title'] : ($siteLabel . ' cache alert'),
                'description' => isset($alert['description']) ? (string)$alert['description'] : '',
                'site' => $siteLabel
            );
        }
    }
}

if ($siteKeyExplosions > 0) {
    $cacheAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('cache-redis-alert-key-explosion'),
        'description' => sprintf($L->g('cache-redis-alert-key-explosion-desc'), $siteKeyExplosions)
    );
}

if ($redisConnected === false) {
    $cacheAlerts[] = array(
        'status' => 'fail',
        'title' => $L->g('cache-redis-alert-redis-offline'),
        'description' => $L->g('cache-redis-alert-redis-offline-desc')
    );
}

if ($redisMemoryLimit > 0 && $redisMemoryUsed > 0) {
    $memoryRatio = ($redisMemoryUsed / $redisMemoryLimit) * 100;
    if ($memoryRatio >= 90) {
        $cacheAlerts[] = array(
            'status' => 'fail',
            'title' => $L->g('cache-redis-alert-memory-critical'),
            'description' => sprintf($L->g('cache-redis-alert-memory-critical-desc'), number_format($memoryRatio, 1) . '%')
        );
    } elseif ($memoryRatio >= 75) {
        $cacheAlerts[] = array(
            'status' => 'warn',
            'title' => $L->g('cache-redis-alert-memory-warn'),
            'description' => sprintf($L->g('cache-redis-alert-memory-warn-desc'), number_format($memoryRatio, 1) . '%')
        );
    }
}

if (!empty($redisSlowlog)) {
    $slowCount = count(array_filter($redisSlowlog, function ($entry) {
        return isset($entry['duration']) && is_numeric($entry['duration']) && $entry['duration'] >= 100;
    }));
    if ($slowCount > 0) {
        $cacheAlerts[] = array(
            'status' => 'warn',
            'title' => $L->g('cache-redis-alert-slowlog'),
            'description' => sprintf($L->g('cache-redis-alert-slowlog-desc'), $slowCount)
        );
    }
}

if ($totalCacheSize > 0 && $totalCacheSize >= 15 * 1024 * 1024 * 1024) { // 15GB
    $cacheAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('cache-redis-alert-local-storage'),
        'description' => $L->g('cache-redis-alert-local-storage-desc')
    );
}

arsort($totalNamespaces);
$namespaceBreakdown = array();
foreach (array_slice($totalNamespaces, 0, 8, true) as $namespace => $count) {
    $namespaceBreakdown[] = array(
        'namespace' => $namespace,
        'keys' => $count
    );
}

usort($cacheAlerts, function ($a, $b) {
    $priority = array('fail' => 0, 'warn' => 1, 'ok' => 2, 'info' => 3, 'unknown' => 4);
    return ($priority[$a['status']] ?? 4) <=> ($priority[$b['status']] ?? 4);
});

$cacheRedisPayload = array(
    'redisOverview' => array_values($redisOverview),
    'redisPerformanceCards' => array_values($redisPerformanceCards),
    'redisSlowlog' => array_values($redisSlowlog),
    'localCacheSummary' => $localCacheSummary,
    'localCacheTools' => array_values($localCacheTools),
    'siteCacheMatrix' => array_values($siteCacheMatrix),
    'prewarmJobs' => array_values($prewarmJobs),
    'expirationPolicies' => array_values($expirationPolicies),
    'cacheKeySamples' => array_values($cacheKeySamples),
    'cacheAlerts' => array_values($cacheAlerts),
    'cacheHeatmap' => $cacheHeatmap,
    'namespaceBreakdown' => $namespaceBreakdown
);

extract($cacheRedisPayload, EXTR_OVERWRITE);