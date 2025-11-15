<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

$pageLangFile = PATH_LANGUAGES . 'pages/spider-anti-scraping/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$layout['title'] .= ' - ' . $L->g('spider-anti-title');

if (!function_exists('mgwSpiderReadJsonWithGuard')) {
    function mgwSpiderReadJsonWithGuard($filePath)
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

if (!function_exists('mgwSpiderPercent')) {
    function mgwSpiderPercent($numerator, $denominator)
    {
        if (!is_numeric($numerator) || !is_numeric($denominator) || $denominator <= 0) {
            return 0;
        }
        return ($numerator / $denominator) * 100;
    }
}

if (!function_exists('mgwSpiderStatusFromPercent')) {
    function mgwSpiderStatusFromPercent($value, $warn = 60, $fail = 80)
    {
        if (!is_numeric($value)) {
            return 'unknown';
        }
        if ($value >= $fail) {
            return 'fail';
        }
        if ($value >= $warn) {
            return 'warn';
        }
        return 'ok';
    }
}

if (!function_exists('mgwSpiderSafeCount')) {
    function mgwSpiderSafeCount($value)
    {
        return is_numeric($value) ? (int)$value : 0;
    }
}

$threatOverview = array();
$trafficTrends = array();
$blockedTimeline = array();
$ipRateLimits = array();
$uaBlacklist = array();
$pathProtections = array();
$automationPolicies = array();
$fingerprintLibrary = array();
$manualEntries = array();
$detectionStats = array();
$antiScrapeAlerts = array();
$uaDistribution = array();

$globalSources = array(
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'anti-scraping.php',
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'anti-scraping.json',
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'spider-defense.php'
);

$globalAntiMetrics = array();
foreach ($globalSources as $candidate) {
    if (!empty($globalAntiMetrics)) {
        break;
    }
    $globalAntiMetrics = mgwSpiderReadJsonWithGuard($candidate);
}

$globalTotals = isset($globalAntiMetrics['totals']) && is_array($globalAntiMetrics['totals']) ? $globalAntiMetrics['totals'] : $globalAntiMetrics;
$totalRequests = isset($globalTotals['requests']) ? (int)$globalTotals['requests'] : 0;
$totalBots = isset($globalTotals['bots']) ? (int)$globalTotals['bots'] : 0;
$totalBlocks = isset($globalTotals['blocked']) ? (int)$globalTotals['blocked'] : 0;
$totalFingerprints = isset($globalTotals['fingerprints']) ? (int)$globalTotals['fingerprints'] : 0;
$jsChallenges = isset($globalTotals['jsChallenges']) ? (int)$globalTotals['jsChallenges'] : 0;
$cookieChallenges = isset($globalTotals['cookieChallenges']) ? (int)$globalTotals['cookieChallenges'] : 0;

$challengePass = isset($globalTotals['challengePassed']) ? (int)$globalTotals['challengePassed'] : 0;
$challengeFail = isset($globalTotals['challengeFailed']) ? (int)$globalTotals['challengeFailed'] : 0;

$threatOverview[] = array(
    'label' => $L->g('spider-anti-card-total-requests'),
    'value' => number_format($totalRequests),
    'status' => 'ok',
    'hint' => $L->g('spider-anti-card-total-requests-hint')
);

$threatOverview[] = array(
    'label' => $L->g('spider-anti-card-bot-share'),
    'value' => $totalRequests > 0 ? number_format(mgwSpiderPercent($totalBots, $totalRequests), 1) . '%' : '--',
    'status' => mgwSpiderStatusFromPercent($totalRequests > 0 ? mgwSpiderPercent($totalBots, $totalRequests) : null, 45, 65),
    'hint' => $L->g('spider-anti-card-bot-share-hint')
);

$threatOverview[] = array(
    'label' => $L->g('spider-anti-card-block-rate'),
    'value' => $totalBots > 0 ? number_format(mgwSpiderPercent($totalBlocks, $totalBots), 1) . '%' : '--',
    'status' => mgwSpiderStatusFromPercent($totalBots > 0 ? mgwSpiderPercent($totalBlocks, $totalBots) : null, 35, 55),
    'hint' => $L->g('spider-anti-card-block-rate-hint')
);

$threatOverview[] = array(
    'label' => $L->g('spider-anti-card-fingerprint'),
    'value' => number_format($totalFingerprints),
    'status' => $totalFingerprints > 0 ? 'ok' : 'warn',
    'hint' => $L->g('spider-anti-card-fingerprint-hint')
);

$trendMetrics = isset($globalAntiMetrics['trends']) && is_array($globalAntiMetrics['trends']) ? $globalAntiMetrics['trends'] : array();
$trafficTrends = array(
    'requests' => isset($trendMetrics['requests']) && is_array($trendMetrics['requests']) ? $trendMetrics['requests'] : array(),
    'bots' => isset($trendMetrics['bots']) && is_array($trendMetrics['bots']) ? $trendMetrics['bots'] : array(),
    'blocked' => isset($trendMetrics['blocked']) && is_array($trendMetrics['blocked']) ? $trendMetrics['blocked'] : array()
);

$uaDistribution = isset($globalAntiMetrics['userAgents']) && is_array($globalAntiMetrics['userAgents']) ? $globalAntiMetrics['userAgents'] : array();
$blockedTimeline = isset($globalAntiMetrics['blockedTimeline']) && is_array($globalAntiMetrics['blockedTimeline']) ? $globalAntiMetrics['blockedTimeline'] : array();

$challengeStats = array(
    'js' => $jsChallenges,
    'cookie' => $cookieChallenges,
    'passed' => $challengePass,
    'failed' => $challengeFail
);
$detectionStats = isset($globalAntiMetrics['detection']) && is_array($globalAntiMetrics['detection']) ? $globalAntiMetrics['detection'] : array();
$detectionStats['challenge'] = $challengeStats;

$fingerprintLibrary = isset($globalAntiMetrics['fingerprints']) && is_array($globalAntiMetrics['fingerprints']) ? $globalAntiMetrics['fingerprints'] : array();

if (isset($globalAntiMetrics['rateLimits']) && is_array($globalAntiMetrics['rateLimits'])) {
    foreach ($globalAntiMetrics['rateLimits'] as $limit) {
        if (!is_array($limit)) {
            continue;
        }
        $ipRateLimits[] = array(
            'scope' => isset($limit['scope']) ? (string)$limit['scope'] : 'global',
            'threshold' => isset($limit['threshold']) ? (int)$limit['threshold'] : 0,
            'period' => isset($limit['period']) ? (int)$limit['period'] : 60,
            'status' => isset($limit['status']) ? (string)$limit['status'] : 'ok',
            'blocked' => isset($limit['blocked']) ? (int)$limit['blocked'] : 0,
            'type' => isset($limit['type']) ? (string)$limit['type'] : 'ip'
        );
    }
}

if (isset($globalAntiMetrics['pathProtections']) && is_array($globalAntiMetrics['pathProtections'])) {
    foreach ($globalAntiMetrics['pathProtections'] as $rule) {
        if (!is_array($rule)) {
            continue;
        }
        $pathProtections[] = array(
            'pattern' => isset($rule['pattern']) ? (string)$rule['pattern'] : '',
            'action' => isset($rule['action']) ? (string)$rule['action'] : 'block',
            'description' => isset($rule['description']) ? (string)$rule['description'] : ''
        );
    }
}

if (isset($globalAntiMetrics['automation']) && is_array($globalAntiMetrics['automation'])) {
    foreach ($globalAntiMetrics['automation'] as $policy) {
        if (!is_array($policy)) {
            continue;
        }
        $automationPolicies[] = array(
            'name' => isset($policy['name']) ? (string)$policy['name'] : '',
            'threshold' => isset($policy['threshold']) ? (int)$policy['threshold'] : 0,
            'window' => isset($policy['window']) ? (int)$policy['window'] : 0,
            'ban' => isset($policy['ban']) ? (int)$policy['ban'] : 0,
            'status' => isset($policy['status']) ? (string)$policy['status'] : 'ok',
            'description' => isset($policy['description']) ? (string)$policy['description'] : ''
        );
    }
}

if (isset($globalAntiMetrics['manualEntries']) && is_array($globalAntiMetrics['manualEntries'])) {
    foreach ($globalAntiMetrics['manualEntries'] as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $manualEntries[] = array(
            'type' => isset($entry['type']) ? (string)$entry['type'] : 'ua',
            'value' => isset($entry['value']) ? (string)$entry['value'] : '',
            'reason' => isset($entry['reason']) ? (string)$entry['reason'] : '',
            'created' => isset($entry['created']) ? (int)$entry['created'] : null
        );
    }
}

$uaBlacklist = isset($globalAntiMetrics['blacklist']) && is_array($globalAntiMetrics['blacklist']) ? $globalAntiMetrics['blacklist'] : array();

$sitesRoot = PATH_ROOT . 'mgw-content' . DS;
$siteDirectories = glob($sitesRoot . '*', GLOB_ONLYDIR);
if ($siteDirectories === false) {
    $siteDirectories = array();
}

$siteAlerts = 0;
$siteBlocked = 0;
$siteChallengeFails = 0;

foreach ($siteDirectories as $directory) {
    $slug = basename($directory);
    if ($slug === '' || $slug[0] === '.') {
        continue;
    }

    $siteMeta = mgwSpiderReadJsonWithGuard($directory . DS . 'databases' . DS . 'site.php');
    $siteLabel = isset($siteMeta['title']) && is_string($siteMeta['title']) && $siteMeta['title'] !== '' ? $siteMeta['title'] : $slug;

    $siteSpiderMetrics = mgwSpiderReadJsonWithGuard($directory . DS . 'tmp' . DS . 'anti-scraping.php');
    if (empty($siteSpiderMetrics)) {
        $siteSpiderMetrics = mgwSpiderReadJsonWithGuard($directory . DS . 'tmp' . DS . 'anti-scraping.json');
    }

    $siteRequests = isset($siteSpiderMetrics['requests']) ? (int)$siteSpiderMetrics['requests'] : 0;
    $siteBots = isset($siteSpiderMetrics['bots']) ? (int)$siteSpiderMetrics['bots'] : 0;
    $siteBlockedCount = isset($siteSpiderMetrics['blocked']) ? (int)$siteSpiderMetrics['blocked'] : 0;
    $siteAlerts += isset($siteSpiderMetrics['alerts']) ? (int)$siteSpiderMetrics['alerts'] : 0;
    $siteBlocked += $siteBlockedCount;
    $siteChallengeFails += isset($siteSpiderMetrics['challengeFailed']) ? (int)$siteSpiderMetrics['challengeFailed'] : 0;

    if (isset($siteSpiderMetrics['alertsList']) && is_array($siteSpiderMetrics['alertsList'])) {
        foreach ($siteSpiderMetrics['alertsList'] as $alert) {
            if (!is_array($alert)) {
                continue;
            }
            $antiScrapeAlerts[] = array(
                'status' => isset($alert['status']) ? (string)$alert['status'] : 'warn',
                'title' => isset($alert['title']) ? (string)$alert['title'] : '',
                'description' => isset($alert['description']) ? (string)$alert['description'] : '',
                'site' => $siteLabel
            );
        }
    }

    if (isset($siteSpiderMetrics['blockedTimeline']) && is_array($siteSpiderMetrics['blockedTimeline'])) {
        $blockedTimeline[$siteLabel] = $siteSpiderMetrics['blockedTimeline'];
    }

    if (isset($siteSpiderMetrics['uaDistribution']) && is_array($siteSpiderMetrics['uaDistribution'])) {
        $uaDistribution[$siteLabel] = $siteSpiderMetrics['uaDistribution'];
    }

    if (isset($siteSpiderMetrics['manual']) && is_array($siteSpiderMetrics['manual'])) {
        foreach ($siteSpiderMetrics['manual'] as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $manualEntries[] = array(
                'type' => isset($entry['type']) ? (string)$entry['type'] : 'ua',
                'value' => isset($entry['value']) ? (string)$entry['value'] : '',
                'reason' => isset($entry['reason']) ? (string)$entry['reason'] : '',
                'created' => isset($entry['created']) ? (int)$entry['created'] : null,
                'site' => $siteLabel
            );
        }
    }
}

if ($siteAlerts > 0) {
    $antiScrapeAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('spider-anti-alert-site-breach'),
        'description' => sprintf($L->g('spider-anti-alert-site-breach-desc'), $siteAlerts)
    );
}

if ($siteBlocked > 100000) {
    $antiScrapeAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('spider-anti-alert-high-blocks'),
        'description' => sprintf($L->g('spider-anti-alert-high-blocks-desc'), number_format($siteBlocked))
    );
}

if ($siteChallengeFails > 5000) {
    $antiScrapeAlerts[] = array(
        'status' => 'fail',
        'title' => $L->g('spider-anti-alert-challenge-fail'),
        'description' => sprintf($L->g('spider-anti-alert-challenge-fail-desc'), number_format($siteChallengeFails))
    );
}

usort($antiScrapeAlerts, function ($a, $b) {
    $priority = array('fail' => 0, 'warn' => 1, 'info' => 2, 'ok' => 3, 'unknown' => 4);
    return ($priority[$a['status']] ?? 4) <=> ($priority[$b['status']] ?? 4);
});

$manualEntries = array_slice($manualEntries, -50);

$spiderAntiPayload = array(
    'threatOverview' => array_values($threatOverview),
    'trafficTrends' => $trafficTrends,
    'blockedTimeline' => $blockedTimeline,
    'ipRateLimits' => array_values($ipRateLimits),
    'uaBlacklist' => is_array($uaBlacklist) ? array_values($uaBlacklist) : array(),
    'pathProtections' => array_values($pathProtections),
    'automationPolicies' => array_values($automationPolicies),
    'fingerprintLibrary' => is_array($fingerprintLibrary) ? array_values($fingerprintLibrary) : array(),
    'manualEntries' => array_values($manualEntries),
    'detectionStats' => $detectionStats,
    'antiScrapeAlerts' => array_values($antiScrapeAlerts),
    'uaDistribution' => $uaDistribution
);

extract($spiderAntiPayload, EXTR_OVERWRITE);