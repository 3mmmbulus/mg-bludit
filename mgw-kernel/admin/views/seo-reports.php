<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$overviewCards = isset($overviewCards) && is_array($overviewCards) ? $overviewCards : array();
$siteCards = isset($siteCards) && is_array($siteCards) ? $siteCards : array();
$indexTrends = isset($indexTrends) && is_array($indexTrends) ? $indexTrends : array();
$crawlerTrends = isset($crawlerTrends) && is_array($crawlerTrends) ? $crawlerTrends : array();
$sitemapMatrix = isset($sitemapMatrix) && is_array($sitemapMatrix) ? $sitemapMatrix : array();
$keywordTrends = isset($keywordTrends) && is_array($keywordTrends) ? $keywordTrends : array();
$trafficTrends = isset($trafficTrends) && is_array($trafficTrends) ? $trafficTrends : array();
$backlinkTrends = isset($backlinkTrends) && is_array($backlinkTrends) ? $backlinkTrends : array();
$anchorSummary = isset($anchorSummary) && is_array($anchorSummary) ? $anchorSummary : array();
$contentAudits = isset($contentAudits) && is_array($contentAudits) ? $contentAudits : array();
$seoAlerts = isset($seoAlerts) && is_array($seoAlerts) ? $seoAlerts : array();

if (!function_exists('seoReportsStatusBadgeClass')) {
    function seoReportsStatusBadgeClass($status)
    {
        switch ($status) {
            case 'ok':
                return 'bg-success-subtle text-success';
            case 'warn':
                return 'bg-warning-subtle text-warning';
            case 'fail':
                return 'bg-danger-subtle text-danger';
            default:
                return 'bg-secondary-subtle text-secondary';
        }
    }
}

if (!function_exists('seoReportsStatusLabel')) {
    function seoReportsStatusLabel($status, $language)
    {
        $key = 'seo-reports-status-' . $status;
        return $language->g($key) ?: strtoupper($status);
    }
}

if (!function_exists('seoReportsFormatNumber')) {
    function seoReportsFormatNumber($value)
    {
        if ($value === null || $value === '') {
            return '--';
        }
        if (is_numeric($value)) {
            if (abs($value) >= 1000) {
                return number_format((float)$value);
            }
            if (round($value) != $value) {
                return number_format((float)$value, 2);
            }
            return (string)(float)$value;
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('seoReportsFormatPercent')) {
    function seoReportsFormatPercent($value)
    {
        if (!is_numeric($value)) {
            return '--';
        }
        return number_format((float)$value, 1) . '%';
    }
}

if (!function_exists('seoReportsRenderSparkline')) {
    function seoReportsRenderSparkline($values)
    {
        if (!is_array($values) || empty($values)) {
            return '<div class="sparkline sparkline-empty"></div>';
        }
        $max = max(array_map('floatval', $values));
        if ($max <= 0) {
            $max = 1;
        }
        $bars = array();
        foreach ($values as $value) {
            $height = is_numeric($value) ? max(2, (float)$value / $max * 32) : 2;
            $bars[] = '<span style="height:' . number_format($height, 2) . 'px"></span>';
        }
        return '<div class="sparkline">' . implode('', $bars) . '</div>';
    }
}

if (!function_exists('seoReportsFormatDate')) {
    function seoReportsFormatDate($value)
    {
        if ($value === null || $value === '') {
            return '--';
        }
        if (is_numeric($value)) {
            $value = (int)$value;
            if ($value <= 0) {
                return '--';
            }
            return date('Y-m-d', $value);
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="seo-reports-page container-fluid px-0">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1 d-flex align-items-center gap-2">
                <span class="bi bi-bar-chart-steps"></span>
                <span><?php echo $L->g('seo-reports-title'); ?></span>
            </h2>
            <p class="text-muted mb-0 small"><?php echo $L->g('seo-reports-subtitle'); ?></p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?php echo Sanitize::html(HTML_PATH_ADMIN_ROOT . 'seo-reports'); ?>" class="btn btn-outline-primary">
                <span class="bi bi-arrow-repeat"></span>
                <span class="ms-1"><?php echo $L->g('seo-reports-refresh'); ?></span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('seo-reports-overview-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('seo-reports-overview-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($overviewCards)): ?>
                <div class="row g-3">
                    <?php foreach ($overviewCards as $card): ?>
                        <div class="col-sm-6 col-lg-3">
                            <div class="seo-overview-card border rounded-3 p-3 h-100">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($card['label']); ?>"><?php echo Sanitize::html($card['label']); ?></div>
                                        <div class="display-6 fw-semibold mt-2">
                                            <?php echo Sanitize::html($card['value']); ?><?php echo isset($card['unit']) ? Sanitize::html($card['unit']) : ''; ?>
                                        </div>
                                    </div>
                                    <span class="badge <?php echo seoReportsStatusBadgeClass(isset($card['status']) ? $card['status'] : 'unknown'); ?>">
                                        <?php echo Sanitize::html(seoReportsStatusLabel(isset($card['status']) ? $card['status'] : 'unknown', $L)); ?>
                                    </span>
                                </div>
                                <?php if (!empty($card['hint'])): ?>
                                    <p class="small text-muted mt-3 mb-0"><?php echo Sanitize::html($card['hint']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0"><?php echo Sanitize::html($L->g('seo-reports-overview-empty')); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach ($siteCards as $card): ?>
            <div class="col-xl-4">
                <div class="seo-site-card border rounded-3 h-100 p-3">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($card['label']); ?>"><?php echo Sanitize::html($card['label']); ?></div>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <span class="seo-health-badge" style="--score: <?php echo (int)$card['healthScore']; ?>;">
                                    <?php echo (int)$card['healthScore']; ?>
                                </span>
                                <span class="text-muted small"><?php echo $L->g('seo-reports-site-health'); ?></span>
                            </div>
                        </div>
                        <div class="text-end small text-muted">
                            <div><?php echo $L->g('seo-reports-site-indexed-pc'); ?>: <span class="fw-semibold"><?php echo number_format((int)$card['indexedPc']); ?></span></div>
                            <div><?php echo $L->g('seo-reports-site-indexed-mobile'); ?>: <span class="fw-semibold"><?php echo number_format((int)$card['indexedMobile']); ?></span></div>
                        </div>
                    </div>
                    <div class="row g-2 mt-3">
                        <div class="col-4">
                            <div class="seo-site-mini-card">
                                <div class="small text-muted"><?php echo $L->g('seo-reports-site-keyword'); ?></div>
                                <div class="fw-semibold <?php echo ($card['keywordDelta'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>"><?php echo ($card['keywordDelta'] ?? 0) >= 0 ? '+' : ''; ?><?php echo (int)$card['keywordDelta']; ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="seo-site-mini-card">
                                <div class="small text-muted"><?php echo $L->g('seo-reports-site-traffic'); ?></div>
                                <div class="fw-semibold <?php echo ($card['trafficDelta'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>"><?php echo ($card['trafficDelta'] ?? 0) >= 0 ? '+' : ''; ?><?php echo (int)$card['trafficDelta']; ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="seo-site-mini-card">
                                <div class="small text-muted"><?php echo $L->g('seo-reports-site-backlink'); ?></div>
                                <div class="fw-semibold <?php echo ($card['backlinkDelta'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>"><?php echo ($card['backlinkDelta'] ?? 0) >= 0 ? '+' : ''; ?><?php echo (int)$card['backlinkDelta']; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($card['deviceSplit'])): ?>
                        <div class="seo-device-split mt-3">
                            <?php foreach ($card['deviceSplit'] as $device => $ratio): ?>
                                <div class="seo-device-bar">
                                    <span><?php echo Sanitize::html(Text::firstCharUp((string)$device)); ?></span>
                                    <div class="seo-device-meter">
                                        <div style="width: <?php echo min(100, (float)$ratio); ?>%"></div>
                                    </div>
                                    <span class="small text-muted ms-2"><?php echo seoReportsFormatPercent($ratio); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($card['alerts'])): ?>
                        <ul class="list-unstyled small text-muted mt-3 mb-0">
                            <?php foreach ($card['alerts'] as $alert): ?>
                                <li class="d-flex align-items-start gap-2">
                                    <span class="bi bi-exclamation-diamond text-warning"></span>
                                    <span><?php echo Sanitize::html($alert); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('seo-reports-index-trend-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('seo-reports-index-trend-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('seo-reports-index-table-site'); ?></th>
                            <th style="width: 220px;"><?php echo $L->g('seo-reports-index-table-pc'); ?></th>
                            <th style="width: 220px;"><?php echo $L->g('seo-reports-index-table-mobile'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($indexTrends['pc'])): ?>
                            <?php foreach ($indexTrends['pc'] as $idx => $pcRow): ?>
                                <?php $mobileRow = $indexTrends['mobile'][$idx] ?? array('values' => array()); ?>
                                <tr>
                                    <td class="fw-semibold text-truncate"><?php echo Sanitize::html($pcRow['site']); ?></td>
                                    <td><?php echo seoReportsRenderSparkline($pcRow['values']); ?></td>
                                    <td><?php echo seoReportsRenderSparkline($mobileRow['values']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-5">
                                    <span class="bi bi-graph-up-arrow fs-1 d-block mb-2"></span>
                                    <span><?php echo $L->g('seo-reports-index-empty'); ?></span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('seo-reports-crawler-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('seo-reports-crawler-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('seo-reports-crawler-table-engine'); ?></th>
                            <th><?php echo $L->g('seo-reports-crawler-table-site'); ?></th>
                            <th style="width: 220px;"><?php echo $L->g('seo-reports-crawler-table-trend'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $hasCrawlerData = false; ?>
                        <?php foreach ($crawlerTrends as $engine => $rows): ?>
                            <?php if (empty($rows)) { continue; } ?>
                            <?php $engineLabel = $L->g('seo-reports-engine-' . $engine) ?: Text::firstCharUp($engine); ?>
                            <?php foreach ($rows as $row): ?>
                                <?php $hasCrawlerData = true; ?>
                                <tr>
                                    <td class="fw-semibold text-uppercase"><?php echo Sanitize::html($engineLabel); ?></td>
                                    <td class="text-truncate"><?php echo Sanitize::html($row['site']); ?></td>
                                    <td><?php echo seoReportsRenderSparkline($row['values']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        <?php if (!$hasCrawlerData): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-5">
                                    <span class="bi bi-search fs-1 d-block mb-2"></span>
                                    <span><?php echo $L->g('seo-reports-crawler-empty'); ?></span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('seo-reports-sitemap-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('seo-reports-sitemap-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('seo-reports-sitemap-table-site'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('seo-reports-sitemap-table-status'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('seo-reports-sitemap-table-submitted'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('seo-reports-sitemap-table-indexed'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('seo-reports-sitemap-table-deadlinks'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('seo-reports-sitemap-table-redirects'); ?></th>
                            <th style="width: 180px;"><?php echo $L->g('seo-reports-sitemap-table-last'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sitemapMatrix)): ?>
                            <?php foreach ($sitemapMatrix as $row): ?>
                                <tr>
                                    <td class="fw-semibold text-truncate"><?php echo Sanitize::html($row['site']); ?></td>
                                    <td class="text-center"><span class="badge <?php echo seoReportsStatusBadgeClass($row['meta']['status']); ?>"><?php echo Sanitize::html(seoReportsStatusLabel($row['meta']['status'], $L)); ?></span></td>
                                    <td class="text-center"><?php echo number_format((int)$row['meta']['submitted']); ?></td>
                                    <td class="text-center fw-semibold">
                                        <?php echo number_format((int)$row['meta']['indexed']); ?>
                                        <?php if ((int)$row['meta']['submitted'] > 0): ?>
                                            <span class="badge bg-light text-dark border ms-1">
                                                <?php echo seoReportsFormatPercent(((int)$row['meta']['indexed'] / max(1, (int)$row['meta']['submitted'])) * 100); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo (int)$row['meta']['deadLinks'] > 0 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success'; ?>"><?php echo number_format((int)$row['meta']['deadLinks']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo (int)$row['meta']['redirectChains'] > 0 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success'; ?>"><?php echo number_format((int)$row['meta']['redirectChains']); ?></span>
                                    </td>
                                    <td class="small text-muted"><?php echo seoReportsFormatDate($row['meta']['lastSubmitted']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <span class="bi bi-diagram-3 fs-1 d-block mb-2"></span>
                                    <span><?php echo $L->g('seo-reports-sitemap-empty'); ?></span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('seo-reports-keywords-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('seo-reports-keywords-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($keywordTrends)): ?>
                            <?php foreach ($keywordTrends as $entry): ?>
                                <div class="list-group-item px-3 py-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <div class="fw-semibold text-truncate"><?php echo Sanitize::html($entry['site']); ?></div>
                                            <?php if (!empty($entry['topKeywords'])): ?>
                                                <div class="small text-muted mt-2">
                                                    <?php echo $L->g('seo-reports-keywords-top'); ?>
                                                    <?php foreach (array_slice($entry['topKeywords'], 0, 4) as $keyword => $pos): ?>
                                                        <span class="badge bg-light text-dark border ms-1"><?php echo Sanitize::html(is_string($keyword) ? $keyword : $pos); ?><?php echo is_numeric($pos) ? ' #' . (int)$pos : ''; ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <?php echo seoReportsRenderSparkline($entry['series']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center text-muted py-5">
                                <span class="bi bi-card-text fs-1 d-block mb-2"></span>
                                <span><?php echo $L->g('seo-reports-keywords-empty'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('seo-reports-traffic-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('seo-reports-traffic-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($trafficTrends)): ?>
                            <?php foreach ($trafficTrends as $entry): ?>
                                <div class="list-group-item px-3 py-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="fw-semibold text-truncate"><?php echo Sanitize::html($entry['site']); ?></div>
                                        <div class="text-end">
                                            <?php echo seoReportsRenderSparkline($entry['series']); ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($entry['devices'])): ?>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <?php foreach ($entry['devices'] as $device => $ratio): ?>
                                                <span class="badge bg-light text-dark border"><?php echo Sanitize::html(Text::firstCharUp((string)$device)); ?> <?php echo seoReportsFormatPercent($ratio); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center text-muted py-5">
                                <span class="bi bi-activity fs-1 d-block mb-2"></span>
                                <span><?php echo $L->g('seo-reports-traffic-empty'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('seo-reports-backlinks-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('seo-reports-backlinks-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($backlinkTrends)): ?>
                            <?php foreach ($backlinkTrends as $entry): ?>
                                <div class="list-group-item px-3 py-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <div class="fw-semibold text-truncate"><?php echo Sanitize::html($entry['site']); ?></div>
                                            <div class="small <?php echo ($entry['delta'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?> mt-1">
                                                <?php echo $L->g('seo-reports-backlinks-change'); ?>
                                                <span class="fw-semibold"><?php echo ($entry['delta'] ?? 0) >= 0 ? '+' : ''; ?><?php echo (int)$entry['delta']; ?></span>
                                            </div>
                                        </div>
                                        <div><?php echo seoReportsRenderSparkline($entry['series']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center text-muted py-5">
                                <span class="bi bi-link-45deg fs-1 d-block mb-2"></span>
                                <span><?php echo $L->g('seo-reports-backlinks-empty'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('seo-reports-anchors-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('seo-reports-anchors-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($anchorSummary)): ?>
                        <div class="row g-2">
                            <?php foreach ($anchorSummary as $anchor => $count): ?>
                                <div class="col-sm-6">
                                    <div class="seo-anchor-chip d-flex align-items-center justify-content-between">
                                        <span class="text-truncate" title="<?php echo Sanitize::html($anchor); ?>"><?php echo Sanitize::html($anchor); ?></span>
                                        <span class="badge bg-light text-dark border"><?php echo number_format((int)$count); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0"><?php echo Sanitize::html($L->g('seo-reports-anchors-empty')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('seo-reports-content-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('seo-reports-content-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('seo-reports-content-table-site'); ?></th>
                            <th class="text-center" style="width: 160px;"><?php echo $L->g('seo-reports-content-table-tdk'); ?></th>
                            <th class="text-center" style="width: 160px;"><?php echo $L->g('seo-reports-content-table-content'); ?></th>
                            <th class="text-center" style="width: 160px;"><?php echo $L->g('seo-reports-content-table-internal'); ?></th>
                            <th><?php echo $L->g('seo-reports-content-table-issues'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($contentAudits)): ?>
                            <?php foreach ($contentAudits as $row): ?>
                                <tr>
                                    <td class="fw-semibold text-truncate"><?php echo Sanitize::html($row['site']); ?></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?php echo seoReportsFormatNumber($row['titleScore']); ?></span></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?php echo seoReportsFormatNumber($row['contentScore']); ?></span></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?php echo seoReportsFormatNumber($row['internalLinkScore']); ?></span></td>
                                    <td>
                                        <?php if (!empty($row['tdkIssues'])): ?>
                                            <ul class="list-unstyled small text-muted mb-0">
                                                <?php foreach (array_slice($row['tdkIssues'], 0, 4) as $issue): ?>
                                                    <li class="d-flex align-items-start gap-2">
                                                        <span class="bi bi-dot"></span>
                                                        <span><?php echo Sanitize::html($issue); ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                                <?php if (count($row['tdkIssues']) > 4): ?>
                                                    <li class="text-muted small">+<?php echo count($row['tdkIssues']) - 4; ?> <?php echo $L->g('seo-reports-content-more'); ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success"><?php echo $L->g('seo-reports-content-clean'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <span class="bi bi-layout-text-sidebar fs-1 d-block mb-2"></span>
                                    <span><?php echo $L->g('seo-reports-content-empty'); ?></span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('seo-reports-alerts-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('seo-reports-alerts-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($seoAlerts)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($seoAlerts as $alert): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge <?php echo seoReportsStatusBadgeClass($alert['status']); ?>">
                                    <span class="bi <?php echo $alert['status'] === 'fail' ? 'bi-slash-circle' : ($alert['status'] === 'warn' ? 'bi-exclamation-triangle' : 'bi-info-circle'); ?>"></span>
                                </span>
                                <div>
                                    <div class="fw-semibold"><?php echo Sanitize::html($alert['title']); ?></div>
                                    <?php if (!empty($alert['description'])): ?>
                                        <div class="small text-muted mt-1"><?php echo Sanitize::html($alert['description']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-success mb-0 d-flex align-items-center gap-2">
                    <span class="bi bi-check-circle"></span>
                    <span><?php echo $L->g('seo-reports-alerts-empty'); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>seo-reports.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">