<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

$pageLangFile = PATH_LANGUAGES . 'pages/seo-reports/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$layout['title'] .= ' - ' . $L->g('seo-reports-title');

if (!function_exists('mgwSeoReportsReadJsonWithGuard')) {
    function mgwSeoReportsReadJsonWithGuard($filePath)
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

if (!function_exists('mgwSeoReportsFormatNumber')) {
    function mgwSeoReportsFormatNumber($value)
    {
        if (!is_numeric($value)) {
            return 0;
        }
        return (float)$value;
    }
}

if (!function_exists('mgwSeoReportsAverage')) {
    function mgwSeoReportsAverage($numbers)
    {
        if (empty($numbers) || !is_array($numbers)) {
            return null;
        }
        $filtered = array_filter($numbers, 'is_numeric');
        if (empty($filtered)) {
            return null;
        }
        return array_sum($filtered) / count($filtered);
    }
}

if (!function_exists('mgwSeoReportsTrendDelta')) {
    function mgwSeoReportsTrendDelta($series)
    {
        if (!is_array($series) || count($series) < 2) {
            return 0;
        }
        $series = array_values(array_filter($series, 'is_numeric'));
        if (count($series) < 2) {
            return 0;
        }
        $first = reset($series);
        $last = end($series);
        return $last - $first;
    }
}

$siteCards = array();
$indexTrends = array('pc' => array(), 'mobile' => array());
$crawlerTrends = array();
$sitemapMatrix = array();
$keywordTrends = array();
$trafficTrends = array();
$backlinkTrends = array();
$anchorSummary = array();
$contentAudits = array();
$overviewCards = array();
$seoAlerts = array();

$engineKeys = array('baidu', 'google', 'bing', 'sogou', 'so', 'yandex', 'toutiao');
foreach ($engineKeys as $engine) {
    $crawlerTrends[$engine] = array();
}

$sitesRoot = PATH_ROOT . 'mgw-content' . DS;
$siteDirectories = glob($sitesRoot . '*', GLOB_ONLYDIR);
if ($siteDirectories === false) {
    $siteDirectories = array();
}

$aggregateHealthScores = array();
$aggregateIndexedPc = 0;
$aggregateIndexedMobile = 0;
$aggregateTrafficTrend = array();
$aggregateKeywordChange = 0;
$aggregateBacklinkDelta = 0;

foreach ($siteDirectories as $directory) {
    $slug = basename($directory);
    if ($slug === '' || $slug[0] === '.') {
        continue;
    }

    $siteLabel = $slug;
    $siteMetaFile = $directory . DS . 'databases' . DS . 'site.php';
    $siteMeta = mgwSeoReportsReadJsonWithGuard($siteMetaFile);
    if (isset($siteMeta['title']) && is_string($siteMeta['title']) && $siteMeta['title'] !== '') {
        $siteLabel = $siteMeta['title'];
    }

    $seoMetrics = mgwSeoReportsReadJsonWithGuard($directory . DS . 'databases' . DS . 'seo.php');
    $seoAnalytics = mgwSeoReportsReadJsonWithGuard($directory . DS . 'tmp' . DS . 'seo-analytics.php');
    if (empty($seoAnalytics)) {
        $seoAnalytics = mgwSeoReportsReadJsonWithGuard($directory . DS . 'tmp' . DS . 'seo-analytics.json');
    }
    $crawlerData = mgwSeoReportsReadJsonWithGuard($directory . DS . 'tmp' . DS . 'crawler-trends.php');
    $sitemapReport = mgwSeoReportsReadJsonWithGuard($directory . DS . 'tmp' . DS . 'sitemap-report.php');
    $keywordReport = mgwSeoReportsReadJsonWithGuard($directory . DS . 'tmp' . DS . 'keywords.php');
    $trafficReport = mgwSeoReportsReadJsonWithGuard($directory . DS . 'tmp' . DS . 'traffic.php');
    $backlinkReport = mgwSeoReportsReadJsonWithGuard($directory . DS . 'tmp' . DS . 'backlinks.php');
    $contentReport = mgwSeoReportsReadJsonWithGuard($directory . DS . 'tmp' . DS . 'content-audit.php');

    $healthScore = isset($seoMetrics['healthScore']) ? mgwSeoReportsFormatNumber($seoMetrics['healthScore']) : null;
    if ($healthScore === null) {
        $healthScore = 70;
    }
    $aggregateHealthScores[] = $healthScore;

    $indexedPc = isset($seoMetrics['indexed']['pc']) ? (int)$seoMetrics['indexed']['pc'] : (isset($seoAnalytics['indexedPc']) ? (int)$seoAnalytics['indexedPc'] : 0);
    $indexedMobile = isset($seoMetrics['indexed']['mobile']) ? (int)$seoMetrics['indexed']['mobile'] : (isset($seoAnalytics['indexedMobile']) ? (int)$seoAnalytics['indexedMobile'] : 0);
    $aggregateIndexedPc += $indexedPc;
    $aggregateIndexedMobile += $indexedMobile;

    $pcTrend = isset($seoAnalytics['indexedPcTrend']) && is_array($seoAnalytics['indexedPcTrend']) ? $seoAnalytics['indexedPcTrend'] : array();
    $mobileTrend = isset($seoAnalytics['indexedMobileTrend']) && is_array($seoAnalytics['indexedMobileTrend']) ? $seoAnalytics['indexedMobileTrend'] : array();
    $indexTrends['pc'][] = array('site' => $siteLabel, 'values' => $pcTrend);
    $indexTrends['mobile'][] = array('site' => $siteLabel, 'values' => $mobileTrend);

    foreach ($engineKeys as $engine) {
        $engineSeries = array();
        if (isset($crawlerData[$engine]) && is_array($crawlerData[$engine])) {
            $engineSeries = $crawlerData[$engine];
        }
        $crawlerTrends[$engine][] = array('site' => $siteLabel, 'values' => $engineSeries);
    }

    $sitemapStatus = array(
        'status' => isset($sitemapReport['status']) ? (string)$sitemapReport['status'] : 'unknown',
        'submitted' => isset($sitemapReport['submitted']) ? (int)$sitemapReport['submitted'] : 0,
        'indexed' => isset($sitemapReport['indexed']) ? (int)$sitemapReport['indexed'] : $indexedPc,
        'deadLinks' => isset($sitemapReport['deadLinks']) ? (int)$sitemapReport['deadLinks'] : 0,
        'redirectChains' => isset($sitemapReport['redirectChains']) ? (int)$sitemapReport['redirectChains'] : 0,
        'lastSubmitted' => isset($sitemapReport['lastSubmitted']) ? (string)$sitemapReport['lastSubmitted'] : ''
    );
    $sitemapMatrix[] = array('site' => $siteLabel, 'slug' => $slug, 'meta' => $sitemapStatus);

    $keywordTrendSeries = isset($keywordReport['rankingTrend']) && is_array($keywordReport['rankingTrend']) ? $keywordReport['rankingTrend'] : array();
    $trafficSeries = isset($trafficReport['sessions']) && is_array($trafficReport['sessions']) ? $trafficReport['sessions'] : array();
    $trafficDevice = isset($trafficReport['deviceSplit']) && is_array($trafficReport['deviceSplit']) ? $trafficReport['deviceSplit'] : array();

    $aggregateTrafficTrend[] = array_sum(array_filter($trafficSeries, 'is_numeric'));
    $keywordDelta = mgwSeoReportsTrendDelta($keywordTrendSeries);
    $aggregateKeywordChange += $keywordDelta;

    $backlinkSeries = isset($backlinkReport['growth']) && is_array($backlinkReport['growth']) ? $backlinkReport['growth'] : array();
    $anchorDistribution = isset($backlinkReport['anchors']) && is_array($backlinkReport['anchors']) ? $backlinkReport['anchors'] : array();
    $backlinkDelta = mgwSeoReportsTrendDelta($backlinkSeries);
    $aggregateBacklinkDelta += $backlinkDelta;

    $contentScore = isset($contentReport['qualityScore']) ? mgwSeoReportsFormatNumber($contentReport['qualityScore']) : null;
    $titleScore = isset($contentReport['titleScore']) ? mgwSeoReportsFormatNumber($contentReport['titleScore']) : null;
    $internalLinkScore = isset($contentReport['internalLinkScore']) ? mgwSeoReportsFormatNumber($contentReport['internalLinkScore']) : null;
    $tdkIssues = isset($contentReport['tdkIssues']) && is_array($contentReport['tdkIssues']) ? $contentReport['tdkIssues'] : array();

    $siteCards[] = array(
        'slug' => $slug,
        'label' => $siteLabel,
        'healthScore' => $healthScore,
        'indexedPc' => $indexedPc,
        'indexedMobile' => $indexedMobile,
        'keywordDelta' => $keywordDelta,
        'trafficDelta' => mgwSeoReportsTrendDelta($trafficSeries),
        'backlinkDelta' => $backlinkDelta,
        'deviceSplit' => $trafficDevice,
        'alerts' => isset($seoMetrics['alerts']) && is_array($seoMetrics['alerts']) ? $seoMetrics['alerts'] : array()
    );

    $keywordTrends[] = array(
        'site' => $siteLabel,
        'series' => $keywordTrendSeries,
        'topKeywords' => isset($keywordReport['topKeywords']) && is_array($keywordReport['topKeywords']) ? $keywordReport['topKeywords'] : array()
    );

    $trafficTrends[] = array(
        'site' => $siteLabel,
        'series' => $trafficSeries,
        'devices' => $trafficDevice
    );

    $backlinkTrends[] = array(
        'site' => $siteLabel,
        'series' => $backlinkSeries,
        'delta' => $backlinkDelta
    );

    if (!empty($anchorDistribution)) {
        foreach ($anchorDistribution as $anchor => $count) {
            if (!isset($anchorSummary[$anchor])) {
                $anchorSummary[$anchor] = 0;
            }
            $anchorSummary[$anchor] += (int)$count;
        }
    }

    $contentAudits[] = array(
        'site' => $siteLabel,
        'contentScore' => $contentScore,
        'titleScore' => $titleScore,
        'internalLinkScore' => $internalLinkScore,
        'tdkIssues' => $tdkIssues
    );

    if ($healthScore < 60) {
        $seoAlerts[] = array(
            'status' => 'fail',
            'title' => sprintf($L->g('seo-reports-alert-health-low'), $siteLabel),
            'description' => $L->g('seo-reports-alert-health-low-desc')
        );
    } elseif ($healthScore < 75) {
        $seoAlerts[] = array(
            'status' => 'warn',
            'title' => sprintf($L->g('seo-reports-alert-health-watch'), $siteLabel),
            'description' => $L->g('seo-reports-alert-health-watch-desc')
        );
    }

    if ($sitemapStatus['deadLinks'] > 0) {
        $seoAlerts[] = array(
            'status' => 'warn',
            'title' => sprintf($L->g('seo-reports-alert-deadlinks'), $siteLabel),
            'description' => sprintf($L->g('seo-reports-alert-deadlinks-desc'), $sitemapStatus['deadLinks'])
        );
    }

    if ($backlinkDelta < 0) {
        $seoAlerts[] = array(
            'status' => 'warn',
            'title' => sprintf($L->g('seo-reports-alert-backlinks-drop'), $siteLabel),
            'description' => $L->g('seo-reports-alert-backlinks-drop-desc')
        );
    }

    if (!empty($tdkIssues)) {
        $seoAlerts[] = array(
            'status' => 'warn',
            'title' => sprintf($L->g('seo-reports-alert-tdk'), $siteLabel),
            'description' => $L->g('seo-reports-alert-tdk-desc')
        );
    }
}

$averageHealth = mgwSeoReportsAverage($aggregateHealthScores);
$overviewCards[] = array(
    'label' => $L->g('seo-reports-card-health'),
    'value' => $averageHealth !== null ? round($averageHealth) : '--',
    'unit' => '',
    'status' => $averageHealth !== null ? ($averageHealth >= 80 ? 'ok' : ($averageHealth >= 65 ? 'warn' : 'fail')) : 'unknown',
    'hint' => $L->g('seo-reports-card-health-hint')
);

$overviewCards[] = array(
    'label' => $L->g('seo-reports-card-indexed'),
    'value' => number_format($aggregateIndexedPc + $aggregateIndexedMobile),
    'unit' => '',
    'status' => ($aggregateIndexedPc + $aggregateIndexedMobile) > 0 ? 'ok' : 'warn',
    'hint' => $L->g('seo-reports-card-indexed-hint')
);

$overviewCards[] = array(
    'label' => $L->g('seo-reports-card-keyword'),
    'value' => $aggregateKeywordChange > 0 ? '+' . $aggregateKeywordChange : (string)$aggregateKeywordChange,
    'unit' => '',
    'status' => $aggregateKeywordChange >= 0 ? 'ok' : 'warn',
    'hint' => $L->g('seo-reports-card-keyword-hint')
);

$overviewCards[] = array(
    'label' => $L->g('seo-reports-card-backlinks'),
    'value' => $aggregateBacklinkDelta > 0 ? '+' . $aggregateBacklinkDelta : (string)$aggregateBacklinkDelta,
    'unit' => '',
    'status' => $aggregateBacklinkDelta >= 0 ? 'ok' : 'warn',
    'hint' => $L->g('seo-reports-card-backlinks-hint')
);

arsort($anchorSummary);
$topAnchors = array_slice($anchorSummary, 0, 8, true);

usort($seoAlerts, function ($a, $b) {
    $priority = array('fail' => 0, 'warn' => 1, 'ok' => 2, 'unknown' => 3);
    return ($priority[$a['status']] ?? 3) <=> ($priority[$b['status']] ?? 3);
});

$siteCards = array_values($siteCards);
$indexTrends['pc'] = array_values($indexTrends['pc']);
$indexTrends['mobile'] = array_values($indexTrends['mobile']);
foreach ($crawlerTrends as $engine => $series) {
    $crawlerTrends[$engine] = array_values($series);
}
$sitemapMatrix = array_values($sitemapMatrix);
$keywordTrends = array_values($keywordTrends);
$trafficTrends = array_values($trafficTrends);
$backlinkTrends = array_values($backlinkTrends);
$contentAudits = array_values($contentAudits);
$overviewCards = array_values($overviewCards);
$seoAlerts = array_values($seoAlerts);

$seoReportsPayload = array(
    'siteCards' => $siteCards,
    'overviewCards' => $overviewCards,
    'indexTrends' => $indexTrends,
    'crawlerTrends' => $crawlerTrends,
    'sitemapMatrix' => $sitemapMatrix,
    'keywordTrends' => $keywordTrends,
    'trafficTrends' => $trafficTrends,
    'backlinkTrends' => $backlinkTrends,
    'anchorSummary' => $topAnchors,
    'contentAudits' => $contentAudits,
    'seoAlerts' => $seoAlerts
);

extract($seoReportsPayload, EXTR_OVERWRITE);