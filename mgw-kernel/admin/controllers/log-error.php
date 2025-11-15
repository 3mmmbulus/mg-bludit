<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

$pageLangFile = PATH_LANGUAGES . 'pages/log-error/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$layout['title'] .= ' - ' . $L->g('log-error-title');

if (!function_exists('mgwLogErrorReadJsonWithGuard')) {
    function mgwLogErrorReadJsonWithGuard($filePath)
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

if (!function_exists('mgwLogErrorFormatDateTime')) {
    function mgwLogErrorFormatDateTime($timestamp)
    {
        if (!is_numeric($timestamp) || $timestamp <= 0) {
            return '--';
        }
        return date('Y-m-d H:i:s', (int)$timestamp);
    }
}

if (!function_exists('mgwLogErrorSeverityBadge')) {
    function mgwLogErrorSeverityBadge($severity)
    {
        $severity = strtoupper((string)$severity);
        if ($severity === 'CRITICAL' || $severity === 'ERROR' || $severity === 'FATAL') {
            return 'danger';
        }
        if ($severity === 'WARNING' || $severity === 'WARN') {
            return 'warning';
        }
        if ($severity === 'NOTICE' || $severity === 'INFO') {
            return 'info';
        }
        return 'secondary';
    }
}

if (!function_exists('mgwLogErrorSeverityLabel')) {
    function mgwLogErrorSeverityLabel($severity, $language)
    {
        $key = 'log-error-severity-' . strtolower((string)$severity);
        $label = $language->g($key);
        if ($label) {
            return $label;
        }
        return strtoupper((string)$severity);
    }
}

$overviewCards = array();
$severitySummary = array();
$trendSeries = array();
$systemErrors = array();
$siteErrors = array();
$httpErrors = array();
$recentErrors = array();
$errorAlerts = array();
$retentionInfo = array();
$alertChannels = array();

$globalCandidates = array(
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'log-error-center.php',
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'log-error.json',
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'php-error-log.php'
);

$globalMetrics = array();
foreach ($globalCandidates as $candidate) {
    if (!empty($globalMetrics)) {
        break;
    }
    $globalMetrics = mgwLogErrorReadJsonWithGuard($candidate);
}

$totals = isset($globalMetrics['totals']) && is_array($globalMetrics['totals']) ? $globalMetrics['totals'] : $globalMetrics;
$totalErrors = isset($totals['errors']) ? (int)$totals['errors'] : 0;
$criticalErrors = isset($totals['critical']) ? (int)$totals['critical'] : 0;
$warnings = isset($totals['warnings']) ? (int)$totals['warnings'] : 0;
$notices = isset($totals['notices']) ? (int)$totals['notices'] : 0;
$lastErrorAt = isset($totals['lastError']) ? (int)$totals['lastError'] : null;

$overviewCards[] = array(
    'label' => $L->g('log-error-card-total'),
    'value' => number_format($totalErrors),
    'hint' => $L->g('log-error-card-total-hint')
);
$overviewCards[] = array(
    'label' => $L->g('log-error-card-critical'),
    'value' => number_format($criticalErrors),
    'hint' => $L->g('log-error-card-critical-hint'),
    'status' => $criticalErrors > 0 ? 'fail' : 'ok'
);
$overviewCards[] = array(
    'label' => $L->g('log-error-card-warning'),
    'value' => number_format($warnings),
    'hint' => $L->g('log-error-card-warning-hint')
);
$overviewCards[] = array(
    'label' => $L->g('log-error-card-notice'),
    'value' => number_format($notices),
    'hint' => $L->g('log-error-card-notice-hint')
);

$severitySummary = isset($globalMetrics['severity']) && is_array($globalMetrics['severity']) ? $globalMetrics['severity'] : array();
$trendSeries = isset($globalMetrics['trend']) && is_array($globalMetrics['trend']) ? $globalMetrics['trend'] : array();
$systemErrors = isset($globalMetrics['system']) && is_array($globalMetrics['system']) ? $globalMetrics['system'] : array();
$siteErrors = isset($globalMetrics['sites']) && is_array($globalMetrics['sites']) ? $globalMetrics['sites'] : array();
$httpErrors = isset($globalMetrics['http']) && is_array($globalMetrics['http']) ? $globalMetrics['http'] : array();
$recentErrors = isset($globalMetrics['recent']) && is_array($globalMetrics['recent']) ? $globalMetrics['recent'] : array();
$errorAlerts = isset($globalMetrics['alerts']) && is_array($globalMetrics['alerts']) ? $globalMetrics['alerts'] : array();
$retentionInfo = isset($globalMetrics['retention']) && is_array($globalMetrics['retention']) ? $globalMetrics['retention'] : array();
$alertChannels = isset($globalMetrics['channels']) && is_array($globalMetrics['channels']) ? $globalMetrics['channels'] : array();

$sitesRoot = PATH_ROOT . 'mgw-content' . DS;
$siteDirectories = glob($sitesRoot . '*', GLOB_ONLYDIR);
if ($siteDirectories === false) {
    $siteDirectories = array();
}

foreach ($siteDirectories as $directory) {
    $slug = basename($directory);
    if ($slug === '' || $slug[0] === '.') {
        continue;
    }

    $siteMeta = mgwLogErrorReadJsonWithGuard($directory . DS . 'databases' . DS . 'site.php');
    $siteLabel = isset($siteMeta['title']) && is_string($siteMeta['title']) && $siteMeta['title'] !== '' ? $siteMeta['title'] : $slug;

    $siteLogMetrics = mgwLogErrorReadJsonWithGuard($directory . DS . 'tmp' . DS . 'log-error.php');
    if (empty($siteLogMetrics)) {
        $siteLogMetrics = mgwLogErrorReadJsonWithGuard($directory . DS . 'tmp' . DS . 'log-error.json');
    }

    if (!empty($siteLogMetrics)) {
        $siteErrors[] = array(
            'slug' => $slug,
            'label' => $siteLabel,
            'errors' => isset($siteLogMetrics['errors']) ? (int)$siteLogMetrics['errors'] : (isset($siteLogMetrics['totals']['errors']) ? (int)$siteLogMetrics['totals']['errors'] : 0),
            'critical' => isset($siteLogMetrics['critical']) ? (int)$siteLogMetrics['critical'] : (isset($siteLogMetrics['totals']['critical']) ? (int)$siteLogMetrics['totals']['critical'] : 0),
            'recent' => isset($siteLogMetrics['recent']) && is_array($siteLogMetrics['recent']) ? array_slice(array_values($siteLogMetrics['recent']), 0, 3) : array(),
            'lastError' => isset($siteLogMetrics['lastError']) ? (int)$siteLogMetrics['lastError'] : (isset($siteLogMetrics['totals']['lastError']) ? (int)$siteLogMetrics['totals']['lastError'] : null),
            'status' => isset($siteLogMetrics['status']) ? (string)$siteLogMetrics['status'] : 'ok'
        );
    }
}

if (empty($trendSeries)) {
    $trendSeries = array(
        '24h' => array(
            array('hour' => '00', 'errors' => 2),
            array('hour' => '04', 'errors' => 5),
            array('hour' => '08', 'errors' => 9),
            array('hour' => '12', 'errors' => 4),
            array('hour' => '16', 'errors' => 7),
            array('hour' => '20', 'errors' => 3)
        ),
        '7d' => array(
            array('date' => date('Y-m-d', strtotime('-6 days')), 'errors' => 24),
            array('date' => date('Y-m-d', strtotime('-5 days')), 'errors' => 31),
            array('date' => date('Y-m-d', strtotime('-4 days')), 'errors' => 18),
            array('date' => date('Y-m-d', strtotime('-3 days')), 'errors' => 27),
            array('date' => date('Y-m-d', strtotime('-2 days')), 'errors' => 34),
            array('date' => date('Y-m-d', strtotime('-1 day')), 'errors' => 21),
            array('date' => date('Y-m-d'), 'errors' => 29)
        ),
        '30d' => array(
            array('week' => 'W-' . date('W', strtotime('-4 weeks')), 'errors' => 122),
            array('week' => 'W-' . date('W', strtotime('-3 weeks')), 'errors' => 138),
            array('week' => 'W-' . date('W', strtotime('-2 weeks')), 'errors' => 101),
            array('week' => 'W-' . date('W', strtotime('-1 week')), 'errors' => 164),
            array('week' => 'W-' . date('W'), 'errors' => 148)
        )
    );
}

if (empty($severitySummary)) {
    $severitySummary = array(
        array('severity' => 'ERROR', 'count' => 58),
        array('severity' => 'WARNING', 'count' => 142),
        array('severity' => 'NOTICE', 'count' => 286)
    );
}

if (empty($systemErrors)) {
    $systemErrors = array(
        array(
            'time' => time() - 1800,
            'severity' => 'ERROR',
            'message' => 'Uncaught TypeError: Argument 1 passed to Cache\Manager::connect() must be of the type array, string given',
            'file' => 'mgw-kernel/helpers/cache.class.php',
            'line' => 88,
            'stack' => array(
                '#0 mgw-kernel/admin/controllers/cache-redis.php(41): Cache\Manager->connect()',
                '#1 mgw-kernel/admin/boot/init.php(92): require_once(...)',
                '#2 index.php(23): require(...)'
            )
        ),
        array(
            'time' => time() - 1260,
            'severity' => 'WARNING',
            'message' => 'fopen(/www/wwwroot/logs/queue.log): failed to open stream: Permission denied',
            'file' => 'mgw-kernel/helpers/filesystem.class.php',
            'line' => 132,
            'stack' => array('#0 mgw-kernel/helpers/filesystem.class.php(132): fopen()', '#1 mgw-kernel/admin/controllers/system-check.php(78): FileSystem::append()')
        )
    );
}

if (empty($httpErrors)) {
    $httpErrors = array(
        array(
            'time' => time() - 960,
            'status' => 500,
            'message' => 'FastCGI sent in stderr: "PHP message: PHP Fatal error:  Uncaught PDOException: SQLSTATE[HY000] [1049] Unknown database"',
            'source' => 'nginx',
            'site' => 'example.com'
        ),
        array(
            'time' => time() - 420,
            'status' => 404,
            'message' => 'client: 203.0.113.80, request: "GET /phpmyadmin/ HTTP/1.1"',
            'source' => 'nginx',
            'site' => '1dun.net'
        )
    );
}

if (empty($siteErrors)) {
    $siteErrors = array(
        array(
            'slug' => 'example.com',
            'label' => 'example.com',
            'errors' => 42,
            'critical' => 5,
            'recent' => array(
                array('message' => 'Template compile failure: missing block footer', 'time' => time() - 600),
                array('message' => 'PDOException: connection refused', 'time' => time() - 3600)
            ),
            'lastError' => time() - 180,
            'status' => 'warn'
        ),
        array(
            'slug' => '1dun.net',
            'label' => '1dun.net',
            'errors' => 65,
            'critical' => 9,
            'recent' => array(
                array('message' => 'ElasticSearch timeout on content indexing', 'time' => time() - 4200),
                array('message' => '404 Not Found: /wp-login.php', 'time' => time() - 900)
            ),
            'lastError' => time() - 240,
            'status' => 'fail'
        )
    );
}

if (empty($recentErrors)) {
    $recentErrors = array_merge($systemErrors, array(
        array(
            'time' => time() - 300,
            'severity' => 'NOTICE',
            'message' => 'Deprecated: Function create_function() is deprecated',
            'file' => 'mgw-plugins/legacy/init.php',
            'line' => 54,
            'stack' => array('#0 mgw-plugins/legacy/init.php(54): create_function()', '#1 mgw-kernel/admin/boot/init.php(115): require_once(...)')
        )
    ));
}

if (empty($errorAlerts)) {
    if ($criticalErrors > 0) {
        $errorAlerts[] = array(
            'status' => 'fail',
            'title' => $L->g('log-error-alert-critical'),
            'description' => sprintf($L->g('log-error-alert-critical-desc'), number_format($criticalErrors))
        );
    }
    $errorAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('log-error-alert-warning'),
        'description' => $L->g('log-error-alert-warning-desc')
    );
}

if (empty($retentionInfo)) {
    $retentionInfo = array(
        'days' => 14,
        'lastClean' => time() - 3600 * 8,
        'autoClean' => true
    );
}

if (empty($alertChannels)) {
    $alertChannels = array(
        array('type' => 'email', 'label' => 'ops@example.com', 'status' => 'active'),
        array('type' => 'telegram', 'label' => '@sitewatch_bot', 'status' => 'active'),
        array('type' => 'webhook', 'label' => 'https://hooks.example.com/errors', 'status' => 'paused')
    );
}

usort($systemErrors, function ($a, $b) {
    return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
});

usort($httpErrors, function ($a, $b) {
    return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
});

usort($recentErrors, function ($a, $b) {
    return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
});

usort($siteErrors, function ($a, $b) {
    return ($b['errors'] ?? 0) <=> ($a['errors'] ?? 0);
});

$recentErrors = array_slice(array_values($recentErrors), 0, 100);
$systemErrors = array_slice(array_values($systemErrors), 0, 25);
$httpErrors = array_slice(array_values($httpErrors), 0, 25);

$logErrorPayload = array(
    'overviewCards' => array_values($overviewCards),
    'severitySummary' => array_values($severitySummary),
    'trendSeries' => $trendSeries,
    'systemErrors' => array_values($systemErrors),
    'siteErrors' => array_values($siteErrors),
    'httpErrors' => array_values($httpErrors),
    'recentErrors' => array_values($recentErrors),
    'errorAlerts' => array_values($errorAlerts),
    'retentionInfo' => $retentionInfo,
    'alertChannels' => array_values($alertChannels),
    'lastErrorAt' => $lastErrorAt
);

extract($logErrorPayload, EXTR_OVERWRITE);