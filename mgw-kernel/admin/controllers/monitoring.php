<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

$pageLangFile = PATH_LANGUAGES . 'pages/monitoring/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$layout['title'] .= ' - ' . $L->g('monitoring-title');

if (!function_exists('mgwMonitoringReadJsonWithGuard')) {
    function mgwMonitoringReadJsonWithGuard($filePath)
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

        $jsonPayload = trim(implode('', $lines));
        if ($jsonPayload === '' || $jsonPayload === '{}' || $jsonPayload === '[]') {
            return array();
        }

        $decoded = json_decode($jsonPayload, true);
        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('mgwMonitoringFormatBytes')) {
    function mgwMonitoringFormatBytes($bytes)
    {
        if (!is_numeric($bytes)) {
            return '0 B';
        }
        $bytes = (float)$bytes;
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $index = 0;
        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }
        $value = $bytes >= 10 ? round($bytes) : round($bytes, 1);
        return $value . ' ' . $units[$index];
    }
}

if (!function_exists('mgwMonitoringFormatMs')) {
    function mgwMonitoringFormatMs($milliseconds)
    {
        if ($milliseconds === null || $milliseconds === '' || !is_numeric($milliseconds)) {
            return '--';
        }
        $milliseconds = (float)$milliseconds;
        return ($milliseconds >= 1000 ? round($milliseconds) : round($milliseconds, 1)) . ' ms';
    }
}

if (!function_exists('mgwMonitoringFormatPercent')) {
    function mgwMonitoringFormatPercent($ratio)
    {
        if ($ratio === null || !is_numeric($ratio)) {
            return '--';
        }
        return round((float)$ratio, 1) . '%';
    }
}

if (!function_exists('mgwMonitoringDetermineStatus')) {
    function mgwMonitoringDetermineStatus($value, $thresholds, $lowerIsBetter = true)
    {
        if ($value === null || !is_numeric($value)) {
            return 'unknown';
        }

        $value = (float)$value;
        $okThreshold = isset($thresholds['ok']) ? (float)$thresholds['ok'] : null;
        $warnThreshold = isset($thresholds['warn']) ? (float)$thresholds['warn'] : null;

        if ($okThreshold === null || $warnThreshold === null) {
            return 'unknown';
        }

        if ($lowerIsBetter) {
            if ($value <= $okThreshold) {
                return 'ok';
            }
            if ($value <= $warnThreshold) {
                return 'warn';
            }
            return 'fail';
        }

        if ($value >= $okThreshold) {
            return 'ok';
        }
        if ($value >= $warnThreshold) {
            return 'warn';
        }
        return 'fail';
    }
}

if (!function_exists('mgwMonitoringCpuCoreCount')) {
    function mgwMonitoringCpuCoreCount()
    {
        $cores = 1;
        if (function_exists('shell_exec')) {
            $nproc = @shell_exec('nproc 2>/dev/null');
            if (is_string($nproc) && trim($nproc) !== '') {
                $cores = max(1, (int)trim($nproc));
            }
        }
        if ($cores === 1 && is_readable('/proc/cpuinfo')) {
            $content = file_get_contents('/proc/cpuinfo');
            if (is_string($content)) {
                $cores = max(1, substr_count($content, 'processor'));
            }
        }
        return $cores;
    }
}

if (!function_exists('mgwMonitoringReadMeminfo')) {
    function mgwMonitoringReadMeminfo()
    {
        $result = array('total' => null, 'free' => null, 'available' => null, 'cached' => null, 'buffers' => null);
        if (!is_readable('/proc/meminfo')) {
            return $result;
        }
        $lines = @file('/proc/meminfo');
        if ($lines === false) {
            return $result;
        }
        foreach ($lines as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }
            list($key, $value) = array_map('trim', explode(':', $line, 2));
            $value = trim(str_replace(array('kB', 'KB'), '', $value));
            switch ($key) {
                case 'MemTotal':
                    $result['total'] = (float)$value * 1024;
                    break;
                case 'MemFree':
                    $result['free'] = (float)$value * 1024;
                    break;
                case 'MemAvailable':
                    $result['available'] = (float)$value * 1024;
                    break;
                case 'Cached':
                    $result['cached'] = (float)$value * 1024;
                    break;
                case 'Buffers':
                    $result['buffers'] = (float)$value * 1024;
                    break;
            }
        }
        return $result;
    }
}

if (!function_exists('mgwMonitoringReadNetworkTotals')) {
    function mgwMonitoringReadNetworkTotals()
    {
        $stats = array('rx' => 0.0, 'tx' => 0.0);
        if (!is_readable('/proc/net/dev')) {
            return $stats;
        }
        $lines = @file('/proc/net/dev');
        if ($lines === false) {
            return $stats;
        }
        foreach ($lines as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }
            list($iface, $data) = array_map('trim', explode(':', $line, 2));
            if ($iface === 'lo') {
                continue;
            }
            $parts = preg_split('/\s+/', trim($data));
            if (count($parts) >= 9) {
                $stats['rx'] += (float)$parts[0];
                $stats['tx'] += (float)$parts[8];
            }
        }
        return $stats;
    }
}

$siteSpeedSnapshots = array();
$uxMetrics = array();
$cmsPerformance = array();
$resourceUsage = array();
$queueSummaries = array();
$scheduleSummaries = array();
$clusterInsights = array();
$performanceAlerts = array();
$overviewCards = array();

$sitesRoot = PATH_ROOT . 'mgw-content' . DS;
$siteDirectories = glob($sitesRoot . '*', GLOB_ONLYDIR);
if ($siteDirectories === false) {
    $siteDirectories = array();
}

$localLatencySamples = array();
$remoteLatencySamples = array();
$ttfbSamples = array();
$lcpSamples = array();
$clsSamples = array();

foreach ($siteDirectories as $directory) {
    $slug = basename($directory);
    if ($slug === '' || $slug[0] === '.') {
        continue;
    }

    $prettyName = $slug;
    $siteDataFile = $directory . DS . 'databases' . DS . 'site.php';
    $siteData = mgwMonitoringReadJsonWithGuard($siteDataFile);
    if (isset($siteData['title']) && is_string($siteData['title']) && $siteData['title'] !== '') {
        $prettyName = $siteData['title'];
    }

    $performanceProbe = array();
    $probeCandidates = array(
        $directory . DS . 'tmp' . DS . 'performance-probes.php',
        $directory . DS . 'tmp' . DS . 'performance-probes.json',
        $directory . DS . 'databases' . DS . 'performance.php'
    );
    foreach ($probeCandidates as $probeCandidate) {
        $candidateData = mgwMonitoringReadJsonWithGuard($probeCandidate);
        if (!empty($candidateData)) {
            $performanceProbe = $candidateData;
            break;
        }
    }

    $localLatency = null;
    $localSamplesCount = null;
    $remoteLatency = null;
    $remoteSamplesCount = null;
    $remoteProvider = '';
    $availability = null;
    $lastSpeedCheck = '';

    if (!empty($performanceProbe)) {
        if (isset($performanceProbe['local']['latency'])) {
            $localLatency = (float)$performanceProbe['local']['latency'];
        } elseif (isset($performanceProbe['localLatency'])) {
            $localLatency = (float)$performanceProbe['localLatency'];
        }
        if (isset($performanceProbe['local']['samples'])) {
            $localSamplesCount = (int)$performanceProbe['local']['samples'];
        }
        if (isset($performanceProbe['remote']['latency'])) {
            $remoteLatency = (float)$performanceProbe['remote']['latency'];
        } elseif (isset($performanceProbe['remoteLatency'])) {
            $remoteLatency = (float)$performanceProbe['remoteLatency'];
        }
        if (isset($performanceProbe['remote']['samples'])) {
            $remoteSamplesCount = (int)$performanceProbe['remote']['samples'];
        }
        if (isset($performanceProbe['remote']['provider'])) {
            $remoteProvider = (string)$performanceProbe['remote']['provider'];
        } elseif (isset($performanceProbe['provider'])) {
            $remoteProvider = (string)$performanceProbe['provider'];
        }
        if (isset($performanceProbe['availability'])) {
            $availability = (float)$performanceProbe['availability'];
        } elseif (isset($performanceProbe['remote']['availability'])) {
            $availability = (float)$performanceProbe['remote']['availability'];
        }
        if (isset($performanceProbe['checkedAt'])) {
            $lastSpeedCheck = (string)$performanceProbe['checkedAt'];
        } elseif (isset($performanceProbe['remote']['checkedAt'])) {
            $lastSpeedCheck = (string)$performanceProbe['remote']['checkedAt'];
        }
        if ($availability !== null && $availability > 0 && $availability <= 1) {
            $availability *= 100;
        }
    }

    $speedStatus = 'unknown';
    if ($remoteLatency !== null) {
        $speedStatus = mgwMonitoringDetermineStatus($remoteLatency, array('ok' => 800, 'warn' => 1500));
    } elseif ($localLatency !== null) {
        $speedStatus = mgwMonitoringDetermineStatus($localLatency, array('ok' => 400, 'warn' => 900));
    }

    $siteSpeedSnapshots[] = array(
        'slug' => $slug,
        'label' => $prettyName,
        'localLatency' => $localLatency,
        'localSamples' => $localSamplesCount,
        'remoteLatency' => $remoteLatency,
        'remoteSamples' => $remoteSamplesCount,
        'remoteProvider' => $remoteProvider,
    'availability' => $availability,
        'status' => $speedStatus,
        'lastChecked' => $lastSpeedCheck
    );

    if ($localLatency !== null) {
        $localLatencySamples[] = $localLatency;
    }
    if ($remoteLatency !== null) {
        $remoteLatencySamples[] = $remoteLatency;
    }

    $uxData = array();
    $uxCandidates = array(
        $directory . DS . 'tmp' . DS . 'web-vitals.php',
        $directory . DS . 'tmp' . DS . 'ux-metrics.php',
        $directory . DS . 'tmp' . DS . 'ux-metrics.json'
    );
    foreach ($uxCandidates as $uxCandidate) {
        $candidateData = mgwMonitoringReadJsonWithGuard($uxCandidate);
        if (!empty($candidateData)) {
            $uxData = $candidateData;
            break;
        }
    }

    $ttfb = null;
    $fcp = null;
    $lcp = null;
    $cls = null;
    $tti = null;
    $uxCheckedAt = '';

    if (!empty($uxData)) {
        foreach (array('ttfb', 'fcp', 'lcp', 'cls', 'tti') as $metricKey) {
            if (isset($uxData[$metricKey])) {
                ${$metricKey} = is_scalar($uxData[$metricKey]) ? (float)$uxData[$metricKey] : null;
            } elseif (isset($uxData[strtoupper($metricKey)])) {
                ${$metricKey} = is_scalar($uxData[strtoupper($metricKey)]) ? (float)$uxData[strtoupper($metricKey)] : null;
            }
        }
        if (isset($uxData['recordedAt'])) {
            $uxCheckedAt = (string)$uxData['recordedAt'];
        }
    }

    $uxStatusSamples = array();
    if ($ttfb !== null) {
        $ttfbSamples[] = $ttfb;
        $uxStatusSamples[] = mgwMonitoringDetermineStatus($ttfb, array('ok' => 200, 'warn' => 500));
    }
    if ($lcp !== null) {
        $lcpSamples[] = $lcp;
        $uxStatusSamples[] = mgwMonitoringDetermineStatus($lcp, array('ok' => 2500, 'warn' => 4000));
    }
    if ($cls !== null) {
        $clsSamples[] = $cls;
        $uxStatusSamples[] = mgwMonitoringDetermineStatus($cls, array('ok' => 0.1, 'warn' => 0.25));
    }

    $overallUxStatus = 'unknown';
    if (!empty($uxStatusSamples)) {
        if (in_array('fail', $uxStatusSamples, true)) {
            $overallUxStatus = 'fail';
        } elseif (in_array('warn', $uxStatusSamples, true)) {
            $overallUxStatus = 'warn';
        } else {
            $overallUxStatus = 'ok';
        }
    }

    $uxMetrics[] = array(
        'slug' => $slug,
        'label' => $prettyName,
        'ttfb' => $ttfb,
        'fcp' => $fcp,
        'lcp' => $lcp,
        'cls' => $cls,
        'tti' => $tti,
        'status' => $overallUxStatus,
        'recordedAt' => $uxCheckedAt
    );
}

$averageLocalLatency = !empty($localLatencySamples) ? array_sum($localLatencySamples) / count($localLatencySamples) : null;
$averageRemoteLatency = !empty($remoteLatencySamples) ? array_sum($remoteLatencySamples) / count($remoteLatencySamples) : null;
$averageTtfb = !empty($ttfbSamples) ? array_sum($ttfbSamples) / count($ttfbSamples) : null;
$averageLcp = !empty($lcpSamples) ? array_sum($lcpSamples) / count($lcpSamples) : null;
$averageCls = !empty($clsSamples) ? array_sum($clsSamples) / count($clsSamples) : null;

$loadAverage = function_exists('sys_getloadavg') ? sys_getloadavg() : array();
$cpuCores = mgwMonitoringCpuCoreCount();
$cpuPercent = null;
if (!empty($loadAverage)) {
    $recentLoad = (float)$loadAverage[0];
    if ($cpuCores > 0) {
        $cpuPercent = min(100, round(($recentLoad / $cpuCores) * 100, 1));
    }
}

$meminfo = mgwMonitoringReadMeminfo();
$memoryPercent = null;
if ($meminfo['total'] && $meminfo['available']) {
    $usedBytes = max(0, $meminfo['total'] - $meminfo['available']);
    if ($meminfo['total'] > 0) {
        $memoryPercent = round(($usedBytes / $meminfo['total']) * 100, 1);
    }
}

$diskTotal = @disk_total_space(PATH_ROOT);
$diskFree = @disk_free_space(PATH_ROOT);
$diskPercent = null;
if ($diskTotal && $diskTotal > 0) {
    $diskUsed = max(0, $diskTotal - (float)$diskFree);
    $diskPercent = round(($diskUsed / $diskTotal) * 100, 1);
}

$networkTotals = mgwMonitoringReadNetworkTotals();

$resourceUsage = array(
    array(
        'key' => 'cpu',
        'label' => $L->g('monitoring-resource-cpu'),
        'value' => $cpuPercent,
        'units' => '%',
        'status' => mgwMonitoringDetermineStatus($cpuPercent, array('ok' => 70, 'warn' => 85), false),
        'hint' => $L->g('monitoring-resource-cpu-hint'),
        'meta' => array('cores' => $cpuCores, 'load' => !empty($loadAverage) ? implode(', ', array_map(function ($value) {
            return number_format((float)$value, 2);
        }, array_slice($loadAverage, 0, 3))) : '--')
    ),
    array(
        'key' => 'memory',
        'label' => $L->g('monitoring-resource-memory'),
        'value' => $memoryPercent,
        'units' => '%',
        'status' => mgwMonitoringDetermineStatus($memoryPercent, array('ok' => 70, 'warn' => 85), false),
        'hint' => $L->g('monitoring-resource-memory-hint'),
        'meta' => array('total' => $meminfo['total'] ? mgwMonitoringFormatBytes($meminfo['total']) : '--', 'available' => $meminfo['available'] ? mgwMonitoringFormatBytes($meminfo['available']) : '--')
    ),
    array(
        'key' => 'disk',
        'label' => $L->g('monitoring-resource-disk'),
        'value' => $diskPercent,
        'units' => '%',
        'status' => mgwMonitoringDetermineStatus($diskPercent, array('ok' => 70, 'warn' => 85), false),
        'hint' => $L->g('monitoring-resource-disk-hint'),
        'meta' => array('total' => $diskTotal ? mgwMonitoringFormatBytes($diskTotal) : '--', 'free' => $diskFree ? mgwMonitoringFormatBytes($diskFree) : '--')
    ),
    array(
        'key' => 'network',
        'label' => $L->g('monitoring-resource-network'),
        'value' => ($networkTotals['rx'] + $networkTotals['tx']) > 0 ? mgwMonitoringFormatBytes($networkTotals['rx'] + $networkTotals['tx']) : '--',
        'units' => '',
        'status' => 'unknown',
        'hint' => $L->g('monitoring-resource-network-hint'),
        'meta' => array(
            'rx' => $networkTotals['rx'] > 0 ? mgwMonitoringFormatBytes($networkTotals['rx']) : '--',
            'tx' => $networkTotals['tx'] > 0 ? mgwMonitoringFormatBytes($networkTotals['tx']) : '--'
        )
    )
);

$queueBase = PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'queue';
if (is_dir($queueBase)) {
    $queueFiles = glob($queueBase . DS . '*.json');
    if ($queueFiles === false) {
        $queueFiles = array();
    }

    $pendingJobs = count($queueFiles);
    $oldestJobAge = 0;
    $longestRuntime = 0;

    foreach ($queueFiles as $queueFile) {
        $fileMtime = @filemtime($queueFile);
        if ($fileMtime) {
            $age = time() - $fileMtime;
            if ($age > $oldestJobAge) {
                $oldestJobAge = $age;
            }
        }
        $jobData = mgwMonitoringReadJsonWithGuard($queueFile);
        if (isset($jobData['duration'])) {
            $duration = (float)$jobData['duration'];
            if ($duration > $longestRuntime) {
                $longestRuntime = $duration;
            }
        }
    }

    $queueStatus = 'ok';
    if ($pendingJobs >= 20 || $oldestJobAge > 3600) {
        $queueStatus = 'fail';
    } elseif ($pendingJobs >= 10 || $oldestJobAge > 900) {
        $queueStatus = 'warn';
    }

    $queueSummaries[] = array(
        'name' => 'default',
        'pending' => $pendingJobs,
        'oldestAge' => $oldestJobAge,
        'longestRuntime' => $longestRuntime,
        'status' => $queueStatus
    );
}

$scheduleFile = PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'databases' . DS . 'scheduler.php';
$scheduleData = mgwMonitoringReadJsonWithGuard($scheduleFile);
if (!empty($scheduleData) && is_array($scheduleData)) {
    foreach ($scheduleData as $identifier => $task) {
        $lastRun = isset($task['lastRun']) ? (string)$task['lastRun'] : '';
        $avgDuration = isset($task['avgDuration']) ? (float)$task['avgDuration'] : null;
        $status = 'unknown';
        if ($avgDuration !== null) {
            $status = mgwMonitoringDetermineStatus($avgDuration, array('ok' => 2000, 'warn' => 5000));
        }
        $scheduleSummaries[] = array(
            'name' => $identifier,
            'label' => isset($task['label']) ? (string)$task['label'] : $identifier,
            'frequency' => isset($task['frequency']) ? (string)$task['frequency'] : '',
            'lastRun' => $lastRun,
            'avgDuration' => $avgDuration,
            'status' => $status
        );
    }
}

$clusterSiteCount = count($siteSpeedSnapshots);
$sitesWithRemoteLatency = 0;
$slowSites = 0;
foreach ($siteSpeedSnapshots as $snapshot) {
    if ($snapshot['remoteLatency'] !== null) {
        $sitesWithRemoteLatency++;
        if ($snapshot['remoteLatency'] > 1500) {
            $slowSites++;
        }
    }
}

$stressScore = null;
if ($clusterSiteCount > 0 && $cpuPercent !== null && $memoryPercent !== null) {
    $latencyFactor = $averageRemoteLatency !== null ? min(1, $averageRemoteLatency / 3000) : 0;
    $slowFactor = $clusterSiteCount > 0 ? ($slowSites / $clusterSiteCount) : 0;
    $cpuFactor = min(1, $cpuPercent / 100);
    $memoryFactor = min(1, $memoryPercent / 100);
    $stressScore = round(($latencyFactor * 30) + ($slowFactor * 30) + ($cpuFactor * 20) + ($memoryFactor * 20));
}

$stressStatus = 'unknown';
if ($stressScore !== null) {
    if ($stressScore <= 40) {
        $stressStatus = 'ok';
    } elseif ($stressScore <= 70) {
        $stressStatus = 'warn';
    } else {
        $stressStatus = 'fail';
    }
}

$clusterInsights = array(
    'siteCount' => $clusterSiteCount,
    'activeLatencySites' => $sitesWithRemoteLatency,
    'slowSites' => $slowSites,
    'averageLocalLatency' => $averageLocalLatency,
    'averageRemoteLatency' => $averageRemoteLatency,
    'stressScore' => $stressScore,
    'stressStatus' => $stressStatus
);

if ($stressStatus === 'fail') {
    $performanceAlerts[] = array(
        'status' => 'fail',
        'title' => $L->g('monitoring-alert-high-stress-title'),
        'description' => $L->g('monitoring-alert-high-stress-body')
    );
} elseif ($stressStatus === 'warn') {
    $performanceAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('monitoring-alert-moderate-stress-title'),
        'description' => $L->g('monitoring-alert-moderate-stress-body')
    );
}

foreach ($queueSummaries as $queueSummary) {
    if ($queueSummary['status'] === 'fail') {
        $performanceAlerts[] = array(
            'status' => 'fail',
            'title' => sprintf($L->g('monitoring-alert-queue-blocked-title'), $queueSummary['name']),
            'description' => $L->g('monitoring-alert-queue-blocked-body')
        );
    } elseif ($queueSummary['status'] === 'warn') {
        $performanceAlerts[] = array(
            'status' => 'warn',
            'title' => sprintf($L->g('monitoring-alert-queue-delayed-title'), $queueSummary['name']),
            'description' => $L->g('monitoring-alert-queue-delayed-body')
        );
    }
}

foreach ($siteSpeedSnapshots as $snapshot) {
    if ($snapshot['status'] === 'fail') {
        $performanceAlerts[] = array(
            'status' => 'fail',
            'title' => sprintf($L->g('monitoring-alert-slow-site-title'), $snapshot['label']),
            'description' => $L->g('monitoring-alert-slow-site-body')
        );
    }
}

$overviewCards = array(
    array(
        'label' => $L->g('monitoring-card-local-latency-title'),
        'value' => mgwMonitoringFormatMs($averageLocalLatency),
        'status' => mgwMonitoringDetermineStatus($averageLocalLatency, array('ok' => 500, 'warn' => 900)),
        'hint' => $L->g('monitoring-card-local-latency-hint')
    ),
    array(
        'label' => $L->g('monitoring-card-remote-latency-title'),
        'value' => mgwMonitoringFormatMs($averageRemoteLatency),
        'status' => mgwMonitoringDetermineStatus($averageRemoteLatency, array('ok' => 900, 'warn' => 1600)),
        'hint' => $L->g('monitoring-card-remote-latency-hint')
    ),
    array(
        'label' => $L->g('monitoring-card-ttfb-title'),
        'value' => mgwMonitoringFormatMs($averageTtfb),
        'status' => mgwMonitoringDetermineStatus($averageTtfb, array('ok' => 300, 'warn' => 600)),
        'hint' => $L->g('monitoring-card-ttfb-hint')
    ),
    array(
        'label' => $L->g('monitoring-card-stress-title'),
        'value' => $stressScore !== null ? $stressScore . '%' : '--',
        'status' => $stressStatus,
        'hint' => $L->g('monitoring-card-stress-hint')
    )
);

$cmsPerformance = array(
    'renderTime' => $averageRemoteLatency !== null ? max(0, $averageRemoteLatency - ($averageTtfb !== null ? $averageTtfb : 0)) : null,
    'dbTime' => null,
    'ioWait' => null,
    'cacheHitRate' => null,
    'pageGeneration' => isset($_SERVER['REQUEST_TIME_FLOAT']) ? (microtime(true) - (float)$_SERVER['REQUEST_TIME_FLOAT']) * 1000 : null,
    'status' => array(
        'render' => mgwMonitoringDetermineStatus($averageRemoteLatency !== null ? max(0, $averageRemoteLatency - ($averageTtfb !== null ? $averageTtfb : 0)) : null, array('ok' => 600, 'warn' => 1200)),
        'db' => 'unknown',
        'io' => 'unknown',
        'cache' => 'unknown',
        'generation' => mgwMonitoringDetermineStatus(isset($_SERVER['REQUEST_TIME_FLOAT']) ? (microtime(true) - (float)$_SERVER['REQUEST_TIME_FLOAT']) * 1000 : null, array('ok' => 500, 'warn' => 1200))
    )
);

if ($averageCls !== null && $averageCls > 0.25) {
    $performanceAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('monitoring-alert-cls-title'),
        'description' => $L->g('monitoring-alert-cls-body')
    );
}

$siteSpeedSnapshots = array_values($siteSpeedSnapshots);
$uxMetrics = array_values($uxMetrics);
$resourceUsage = array_values($resourceUsage);
$queueSummaries = array_values($queueSummaries);
$scheduleSummaries = array_values($scheduleSummaries);
$performanceAlerts = array_values($performanceAlerts);
$overviewCards = array_values($overviewCards);