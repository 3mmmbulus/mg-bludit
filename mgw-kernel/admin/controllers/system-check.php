<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

$pageLangFile = PATH_LANGUAGES . 'pages/system-check/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$layout['title'] .= ' - ' . $L->g('system-check-title');

$rootContentBase = PATH_ROOT . 'mgw-content' . DS;

if (!function_exists('mgwSystemCheckFormatBytes')) {
    function mgwSystemCheckFormatBytes($bytes)
    {
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

if (!function_exists('mgwSystemCheckConvertToBytes')) {
    function mgwSystemCheckConvertToBytes($value)
    {
        $value = trim((string)$value);
        if ($value === '' || $value === '-1') {
            return -1;
        }
        $last = strtolower($value[strlen($value) - 1]);
        $number = (float)$value;
        switch ($last) {
            case 'g':
                $number *= 1024;
            case 'm':
                $number *= 1024;
            case 'k':
                $number *= 1024;
        }
        return (int)$number;
    }
}

if (!function_exists('mgwSystemCheckFormatSeconds')) {
    function mgwSystemCheckFormatSeconds($seconds)
    {
        $seconds = (int)$seconds;
        if ($seconds <= 0) {
            return '0s';
        }
        $units = array(
            'd' => 86400,
            'h' => 3600,
            'm' => 60,
            's' => 1
        );
        $parts = array();
        foreach ($units as $suffix => $length) {
            if ($seconds < $length) {
                continue;
            }
            $value = function_exists('intdiv') ? intdiv($seconds, $length) : (int)floor($seconds / $length);
            if ($value <= 0) {
                continue;
            }
            $parts[] = $value . $suffix;
            $seconds -= $value * $length;
        }
        return empty($parts) ? '0s' : implode(' ', $parts);
    }
}

$serverHealth = array();

$phpVersion = PHP_VERSION;
$phpStatus = version_compare($phpVersion, '8.1', '>=') ? 'ok' : (version_compare($phpVersion, '8.0', '>=') ? 'warn' : 'fail');
$serverHealth[] = array(
    'label' => $L->g('system-check-server-php-version'),
    'value' => $phpVersion,
    'status' => $phpStatus,
    'hint' => $L->g('system-check-server-php-hint')
);

$memoryLimitRaw = ini_get('memory_limit');
$memoryLimitBytes = mgwSystemCheckConvertToBytes($memoryLimitRaw);
$memoryStatus = $memoryLimitBytes === -1 ? 'ok' : ($memoryLimitBytes >= 268435456 ? 'ok' : ($memoryLimitBytes >= 134217728 ? 'warn' : 'fail'));
$memoryValue = $memoryLimitBytes === -1 ? 'Unlimited' : mgwSystemCheckFormatBytes($memoryLimitBytes);
$serverHealth[] = array(
    'label' => $L->g('system-check-server-memory-limit'),
    'value' => $memoryValue,
    'status' => $memoryStatus,
    'hint' => $L->g('system-check-server-memory-hint')
);

$maxExecution = (int)ini_get('max_execution_time');
if ($maxExecution <= 0) {
    $executionValue = 'Unlimited';
    $executionStatus = 'ok';
} else {
    $executionValue = $maxExecution . 's';
    $executionStatus = $maxExecution >= 120 ? 'ok' : ($maxExecution >= 60 ? 'warn' : 'fail');
}
$serverHealth[] = array(
    'label' => $L->g('system-check-server-max-execution'),
    'value' => $executionValue,
    'status' => $executionStatus,
    'hint' => $L->g('system-check-server-max-execution-hint')
);

$diskTotal = @disk_total_space(PATH_ROOT);
$diskFree = @disk_free_space(PATH_ROOT);
if ($diskTotal && $diskFree) {
    $diskUsed = $diskTotal - $diskFree;
    $diskUsagePercent = ($diskUsed / $diskTotal) * 100;
    $diskStatus = $diskUsagePercent < 80 ? 'ok' : ($diskUsagePercent < 90 ? 'warn' : 'fail');
    $diskValue = mgwSystemCheckFormatBytes($diskUsed) . ' / ' . mgwSystemCheckFormatBytes($diskTotal) . ' (' . round($diskUsagePercent, 1) . '%)';
} else {
    $diskStatus = 'warn';
    $diskValue = $L->g('system-check-not-available');
}
$serverHealth[] = array(
    'label' => $L->g('system-check-server-disk-usage'),
    'value' => $diskValue,
    'status' => $diskStatus,
    'hint' => $L->g('system-check-server-disk-hint')
);

$loadAverage = function_exists('sys_getloadavg') ? sys_getloadavg() : array();
if (!empty($loadAverage)) {
    $loadFormatted = array_map(function ($value) {
        return number_format((float)$value, 2);
    }, array_slice($loadAverage, 0, 3));
    $loadString = implode(', ', $loadFormatted);
    $coreCount = 1;
    if (function_exists('shell_exec')) {
        $coreOutput = @shell_exec('nproc 2>/dev/null');
        if (is_string($coreOutput) && trim($coreOutput) !== '') {
            $coreCount = max(1, (int)trim($coreOutput));
        }
    }
    $recentLoad = (float)$loadAverage[0];
    $loadStatus = $recentLoad <= $coreCount ? 'ok' : ($recentLoad <= ($coreCount * 1.5) ? 'warn' : 'fail');
} else {
    $loadString = $L->g('system-check-not-available');
    $loadStatus = 'warn';
}
$serverHealth[] = array(
    'label' => $L->g('system-check-server-cpu-load'),
    'value' => $loadString,
    'status' => $loadStatus,
    'hint' => $L->g('system-check-server-cpu-hint')
);

$uptimeSeconds = null;
$uptimeValue = $L->g('system-check-not-available');
if (is_readable('/proc/uptime')) {
    $uptimeRaw = trim((string)file_get_contents('/proc/uptime'));
    if ($uptimeRaw !== '') {
        $parts = explode(' ', $uptimeRaw);
        $uptimeSeconds = (int)floor((float)$parts[0]);
        $uptimeValue = mgwSystemCheckFormatSeconds($uptimeSeconds);
    }
} elseif (function_exists('shell_exec')) {
    $uptimeOutput = @shell_exec('uptime -p 2>/dev/null');
    if (is_string($uptimeOutput) && trim($uptimeOutput) !== '') {
        $uptimeValue = trim($uptimeOutput);
    }
}
$uptimeStatus = $uptimeSeconds === null ? 'warn' : ($uptimeSeconds >= 3600 ? 'ok' : 'warn');
$serverHealth[] = array(
    'label' => $L->g('system-check-server-uptime'),
    'value' => $uptimeValue,
    'status' => $uptimeStatus,
    'hint' => $L->g('system-check-server-uptime-hint')
);

$requiredExtensions = array('curl', 'mbstring', 'intl', 'gd', 'openssl');
$missingExtensions = array();
foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        $missingExtensions[] = $extension;
    }
}
$extensionsStatus = empty($missingExtensions) ? 'ok' : (count($missingExtensions) <= 2 ? 'warn' : 'fail');
$extensionsValue = empty($missingExtensions) ? $L->g('system-check-available') : implode(', ', $missingExtensions);
$serverHealth[] = array(
    'label' => $L->g('system-check-server-extensions'),
    'value' => $extensionsValue,
    'status' => $extensionsStatus,
    'hint' => $L->g('system-check-server-extensions-hint')
);

$siteDirectories = array();
if (is_dir($rootContentBase)) {
    $siteDirectories = glob($rootContentBase . '*', GLOB_ONLYDIR);
    if ($siteDirectories === false) {
        $siteDirectories = array();
    }
}

$siteChecks = array();
foreach ($siteDirectories as $directory) {
    $slug = basename($directory);
    if ($slug === '' || $slug[0] === '.') {
        continue;
    }

    $pagesPath = $directory . DS . 'pages';
    $uploadsPath = $directory . DS . 'uploads';
    $databasePath = $directory . DS . 'databases';

    $pageCount = 0;
    $latestUpdate = null;
    if (is_dir($pagesPath)) {
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $pagesPath,
                    FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
                )
            );
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $pageCount++;
                    $latestUpdate = max($latestUpdate ?? 0, $fileInfo->getMTime());
                }
            }
        } catch (UnexpectedValueException $exception) {
            // Ignore directories that cannot be iterated due to permissions.
        }
    }

    $uploadsWritable = is_dir($uploadsPath) && is_writable($uploadsPath);
    $databaseWritable = is_dir($databasePath) && is_writable($databasePath);

    $status = 'ok';
    if (!$uploadsWritable || !$databaseWritable) {
        $status = (!$uploadsWritable && !$databaseWritable) ? 'fail' : 'warn';
    }

    $siteChecks[] = array(
        'site' => $slug,
        'pages' => $pageCount,
        'lastUpdate' => $latestUpdate ? date('Y-m-d H:i:s', $latestUpdate) : $L->g('system-check-not-available'),
        'uploadsWritable' => $uploadsWritable,
        'databaseWritable' => $databaseWritable,
        'status' => $status
    );
}

$seoChecks = array();
$robotsFile = PATH_ROOT . 'robots.txt';
$seoChecks[] = array(
    'label' => $L->g('system-check-seo-robots'),
    'value' => file_exists($robotsFile) ? $L->g('system-check-available') : $L->g('system-check-missing'),
    'status' => file_exists($robotsFile) ? 'ok' : 'warn',
    'hint' => $L->g('system-check-seo-robots-hint')
);

$sitemapFile = PATH_ROOT . 'sitemap.xml';
$seoChecks[] = array(
    'label' => $L->g('system-check-seo-sitemap'),
    'value' => file_exists($sitemapFile) ? $L->g('system-check-available') : $L->g('system-check-missing'),
    'status' => file_exists($sitemapFile) ? 'ok' : 'warn',
    'hint' => $L->g('system-check-seo-sitemap-hint')
);

$httpsEnabled = method_exists($site, 'isHTTPS') ? $site->isHTTPS() : (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$seoChecks[] = array(
    'label' => $L->g('system-check-seo-https'),
    'value' => $httpsEnabled ? $L->g('system-check-yes') : $L->g('system-check-no'),
    'status' => $httpsEnabled ? 'ok' : 'warn',
    'hint' => $L->g('system-check-seo-https-hint')
);

$canonicalReady = is_dir(PATH_PLUGINS . 'canonical') || is_dir(PATH_PLUGINS . 'opengraph');
$seoChecks[] = array(
    'label' => $L->g('system-check-seo-canonical'),
    'value' => $canonicalReady ? $L->g('system-check-available') : $L->g('system-check-missing'),
    'status' => $canonicalReady ? 'ok' : 'warn',
    'hint' => $L->g('system-check-seo-canonical-hint')
);

$directoryChecks = array();
$directoryTargets = array(
    'mgw-config',
    'mgw-content',
    'mgw-content' . DS . '_default' . DS . 'uploads',
    'mgw-content' . DS . '_default' . DS . 'databases',
    'mgw-content' . DS . '_default' . DS . 'tmp',
    'mgw-content' . DS . '_default' . DS . 'workspaces',
    'mgw-kernel' . DS . 'cache'
);
foreach ($directoryTargets as $relative) {
    $absolute = PATH_ROOT . $relative;
    $directoryChecks[] = array(
        'path' => $relative,
        'readable' => is_readable($absolute),
        'writable' => is_writable($absolute)
    );
}

$spiderReports = array();
foreach ($siteDirectories as $directory) {
    $slug = basename($directory);
    if ($slug === '' || $slug[0] === '.') {
        continue;
    }
    $logs = glob($directory . DS . 'tmp' . DS . 'spider*.log');
    if ($logs === false || empty($logs)) {
        continue;
    }

    $totalSize = 0;
    $lastModified = 0;
    foreach ($logs as $logFile) {
        $totalSize += @filesize($logFile) ?: 0;
        $lastModified = max($lastModified, @filemtime($logFile) ?: 0);
    }

    $spiderReports[] = array(
        'site' => $slug,
        'files' => count($logs),
        'size' => mgwSystemCheckFormatBytes($totalSize),
        'lastActivity' => $lastModified ? date('Y-m-d H:i:s', $lastModified) : $L->g('system-check-not-available')
    );
}
