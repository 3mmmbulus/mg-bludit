<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$spiderOverviewCards = isset($spiderOverviewCards) && is_array($spiderOverviewCards) ? $spiderOverviewCards : array();
$spiderTrend = isset($spiderTrend) && is_array($spiderTrend) ? $spiderTrend : array();
$spiderSource = isset($spiderSource) && is_array($spiderSource) ? $spiderSource : array();
$spiderArchives = isset($spiderArchives) && is_array($spiderArchives) ? $spiderArchives : array();
$spiderLogs = isset($spiderLogs) && is_array($spiderLogs) ? $spiderLogs : array();
$spiderSuspicious = isset($spiderSuspicious) && is_array($spiderSuspicious) ? $spiderSuspicious : array();
$spiderRetention = isset($spiderRetention) && is_array($spiderRetention) ? $spiderRetention : array();
$spiderSiteSummary = isset($spiderSiteSummary) && is_array($spiderSiteSummary) ? $spiderSiteSummary : array();
$siteOptions = isset($siteOptions) && is_array($siteOptions) ? $siteOptions : array();
$selectedSite = isset($selectedSite) ? (string)$selectedSite : '';
$spiderRuleStats = isset($spiderRuleStats) && is_array($spiderRuleStats) ? $spiderRuleStats : array('builtin' => 0, 'custom' => 0);
$spiderBuiltinRules = isset($spiderBuiltinRules) && is_array($spiderBuiltinRules) ? $spiderBuiltinRules : array();
$spiderCustomRules = isset($spiderCustomRules) && is_array($spiderCustomRules) ? $spiderCustomRules : array();

if (!function_exists('spiderLogRelativeTime')) {
    function spiderLogRelativeTime($timestamp)
    {
        if (!is_numeric($timestamp) || $timestamp <= 0) {
            return '--';
        }
        $diff = time() - (int)$timestamp;
        if ($diff < 60) {
            return $diff . 's';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . 'm';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . 'h';
        }
        return floor($diff / 86400) . 'd';
    }
}

if (!function_exists('spiderLogStatusBadgeClass')) {
    function spiderLogStatusBadgeClass($status)
    {
        if (!is_numeric($status)) {
            return 'bg-secondary-subtle text-secondary';
        }
        $status = (int)$status;
        if ($status >= 500) {
            return 'bg-danger-subtle text-danger';
        }
        if ($status >= 400) {
            return 'bg-warning-subtle text-warning';
        }
        if ($status >= 300) {
            return 'bg-info-subtle text-info';
        }
        if ($status >= 200) {
            return 'bg-success-subtle text-success';
        }
        return 'bg-secondary-subtle text-secondary';
    }
}

if (!function_exists('spiderLogTrendMax')) {
    function spiderLogTrendMax($dataset)
    {
        $max = 0;
        foreach ($dataset as $entry) {
            if (isset($entry['hits']) && is_numeric($entry['hits'])) {
                $max = max($max, (float)$entry['hits']);
            }
        }
        return $max > 0 ? $max : 1;
    }
}
?>

<div class="spider-log-page container-fluid px-0">
    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1 d-flex align-items-center gap-2">
                <span class="bi bi-globe2"></span>
                <span><?php echo $L->g('spider-log-title'); ?></span>
            </h2>
            <p class="text-muted small mb-0"><?php echo $L->g('spider-log-subtitle'); ?></p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="#" class="btn btn-primary">
                <span class="bi bi-arrow-repeat"></span>
                <span class="ms-1"><?php echo $L->g('spider-log-action-refresh'); ?></span>
            </a>
            <a href="#" class="btn btn-outline-secondary">
                <span class="bi bi-download"></span>
                <span class="ms-1"><?php echo $L->g('spider-log-action-export'); ?></span>
            </a>
            <a href="#" class="btn btn-outline-danger">
                <span class="bi bi-trash"></span>
                <span class="ms-1"><?php echo $L->g('spider-log-action-clear'); ?></span>
            </a>
            <a href="#" class="btn btn-outline-primary">
                <span class="bi bi-clock-history"></span>
                <span class="ms-1"><?php echo $L->g('spider-log-action-retention'); ?></span>
            </a>
            <a href="#" class="btn btn-outline-success">
                <span class="bi bi-sliders"></span>
                <span class="ms-1"><?php echo $L->g('spider-log-action-rules'); ?></span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end spider-log-filter" action="#" method="get">
                <div class="col-lg-3">
                    <label class="form-label small text-uppercase text-muted"><?php echo $L->g('spider-log-filter-site'); ?></label>
                    <select class="form-select">
                        <?php foreach ($siteOptions as $option): ?>
                            <option value="<?php echo Sanitize::html($option['value']); ?>"<?php echo $option['value'] === $selectedSite ? ' selected' : ''; ?>>
                                <?php echo Sanitize::html(isset($option['label']) ? $option['label'] : $option['value']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label small text-uppercase text-muted"><?php echo $L->g('spider-log-filter-ip'); ?></label>
                    <input type="text" class="form-control" placeholder="203.0.113.*">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label small text-uppercase text-muted"><?php echo $L->g('spider-log-filter-url'); ?></label>
                    <input type="text" class="form-control" placeholder="/sitemap.xml">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label small text-uppercase text-muted"><?php echo $L->g('spider-log-filter-ua'); ?></label>
                    <input type="text" class="form-control" placeholder="Googlebot">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label small text-uppercase text-muted"><?php echo $L->g('spider-log-filter-status'); ?></label>
                    <input type="text" class="form-control" placeholder="200">
                </div>
                <div class="col-sm-6 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-grow-1"><?php echo $L->g('spider-log-filter-search'); ?></button>
                    <button type="reset" class="btn btn-outline-secondary"><?php echo $L->g('spider-log-filter-reset'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($spiderOverviewCards as $card): ?>
                    <div class="col-sm-6 col-xl-3">
                        <div class="spider-overview border rounded-3 h-100 p-3">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold"><?php echo Sanitize::html($card['label']); ?></div>
                                    <div class="fs-3 fw-semibold mt-2" title="<?php echo Sanitize::html($card['value']); ?>"><?php echo Sanitize::html($card['value']); ?></div>
                                </div>
                                <?php if (isset($card['status'])): ?>
                                    <span class="badge <?php echo $card['status'] === 'warn' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success'; ?>">
                                        <?php echo $card['status'] === 'warn' ? $L->g('spider-log-status-warn') : $L->g('spider-log-status-ok'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($card['hint'])): ?>
                                <p class="text-muted small mt-3 mb-0"><?php echo Sanitize::html($card['hint']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xxl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="mb-0"><?php echo $L->g('spider-log-trend-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-log-trend-desc'); ?></span>
                    </div>
                    <div class="nav nav-pills spider-trend-switch" role="tablist">
                        <button class="nav-link active" data-spider-trend-range="24h" type="button"><?php echo $L->g('spider-log-trend-range-24h'); ?></button>
                        <button class="nav-link" data-spider-trend-range="7d" type="button"><?php echo $L->g('spider-log-trend-range-7d'); ?></button>
                        <button class="nav-link" data-spider-trend-range="30d" type="button"><?php echo $L->g('spider-log-trend-range-30d'); ?></button>
                    </div>
                </div>
                <div class="card-body">
                    <?php $ranges = array('24h', '7d', '30d'); ?>
                    <?php foreach ($ranges as $rangeKey): ?>
                        <?php $dataset = isset($spiderTrend[$rangeKey]) && is_array($spiderTrend[$rangeKey]) ? $spiderTrend[$rangeKey] : array(); ?>
                        <div class="spider-trend-range<?php echo $rangeKey === '24h' ? ' active' : ''; ?>" data-range="<?php echo $rangeKey; ?>">
                            <?php if (!empty($dataset)): ?>
                                <?php $maxHits = spiderLogTrendMax($dataset); ?>
                                <div class="spider-trend-grid">
                                    <?php foreach ($dataset as $entry): ?>
                                        <div class="spider-trend-item">
                                            <div class="d-flex align-items-center justify-content-between small text-muted">
                                                <span><?php echo Sanitize::html(isset($entry['label']) ? $entry['label'] : ''); ?></span>
                                                <span class="fw-semibold text-primary">Hits <?php echo number_format(isset($entry['hits']) ? $entry['hits'] : 0); ?></span>
                                            </div>
                                            <?php $percent = $maxHits > 0 ? min(100, ((isset($entry['hits']) ? (float)$entry['hits'] : 0) / $maxHits) * 100) : 0; ?>
                                            <?php $percentUnique = $maxHits > 0 ? min(100, ((isset($entry['unique']) ? (float)$entry['unique'] : 0) / $maxHits) * 100) : 0; ?>
                                            <div class="spider-trend-bar">
                                                <div class="hits" style="width: <?php echo number_format($percent, 2); ?>%;"></div>
                                                <div class="unique" style="width: <?php echo number_format($percentUnique, 2); ?>%;"></div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between small text-muted">
                                                <span><?php echo $L->g('spider-log-card-unique'); ?> <?php echo number_format(isset($entry['unique']) ? $entry['unique'] : 0); ?></span>
                                                <span><?php echo $L->g('spider-log-field-age'); ?> <?php echo isset($entry['timestamp']) ? spiderLogRelativeTime($entry['timestamp']) : '--'; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-light mb-0"><?php echo $L->g('spider-log-trend-empty'); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-xxl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('spider-log-source-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-log-source-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php $engineList = isset($spiderSource['engines']) && is_array($spiderSource['engines']) ? $spiderSource['engines'] : array(); ?>
                    <?php if (!empty($engineList)): ?>
                        <?php $totalSource = isset($spiderSource['total']) && is_numeric($spiderSource['total']) ? (int)$spiderSource['total'] : array_sum(array_map(function ($entry) { return isset($entry['count']) ? (int)$entry['count'] : 0; }, $engineList)); ?>
                        <ul class="list-unstyled spider-source-list mb-0">
                            <?php foreach ($engineList as $entry): ?>
                                <?php $percent = $totalSource > 0 ? ($entry['count'] / $totalSource) * 100 : 0; ?>
                                <li class="border rounded-3 p-3 mb-3">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold"><?php echo Sanitize::html(isset($entry['label']) ? $entry['label'] : 'Unknown'); ?></div>
                                            <div class="text-muted small mt-1">Hits <?php echo number_format(isset($entry['count']) ? $entry['count'] : 0); ?></div>
                                        </div>
                                        <?php if (!empty($entry['suspicious'])): ?>
                                            <span class="badge bg-warning-subtle text-warning"><?php echo $L->g('spider-log-badge-suspicious'); ?> <?php echo number_format($entry['suspicious']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success"><?php echo $L->g('spider-log-badge-confirmed'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="progress mt-3" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo number_format($percent, 2); ?>%" aria-valuenow="<?php echo number_format($percent, 2); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-light mb-0"><?php echo $L->g('spider-log-source-empty'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('spider-log-site-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('spider-log-site-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($spiderSiteSummary)): ?>
                <div class="spider-site-grid">
                    <?php foreach ($spiderSiteSummary as $siteItem): ?>
                        <div class="spider-site-card border rounded-3 p-3 <?php echo $siteItem['slug'] === $selectedSite ? 'active' : ''; ?>">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($siteItem['label']); ?>"><?php echo Sanitize::html($siteItem['label']); ?></div>
                                    <div class="text-muted small text-monospace"><?php echo Sanitize::html($siteItem['slug']); ?></div>
                                </div>
                                <a class="btn btn-sm btn-outline-primary" href="?site=<?php echo urlencode($siteItem['slug']); ?>"><?php echo $L->g('spider-log-action-view'); ?></a>
                            </div>
                            <div class="d-flex align-items-center gap-3 mt-3">
                                <div>
                                    <div class="fw-semibold fs-5 text-primary"><?php echo number_format(isset($siteItem['totalHits']) ? $siteItem['totalHits'] : 0); ?></div>
                                    <div class="text-muted small"><?php echo $L->g('spider-log-site-total'); ?></div>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?php echo number_format(isset($siteItem['uniqueBots']) ? $siteItem['uniqueBots'] : 0); ?></div>
                                    <div class="text-muted small"><?php echo $L->g('spider-log-site-unique'); ?></div>
                                </div>
                                <div>
                                    <span class="badge <?php echo (isset($siteItem['suspicious']) && $siteItem['suspicious'] > 0) ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success'; ?>"><?php echo number_format(isset($siteItem['suspicious']) ? $siteItem['suspicious'] : 0); ?></span>
                                    <div class="text-muted small"><?php echo $L->g('spider-log-site-suspicious'); ?></div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between text-muted small mt-3">
                                <span><?php echo $L->g('spider-log-site-last'); ?> <?php echo mgwSpiderLogFormatDateTime(isset($siteItem['lastSeen']) ? $siteItem['lastSeen'] : null); ?></span>
                                <span class="badge bg-light text-dark border"><?php echo spiderLogRelativeTime(isset($siteItem['lastSeen']) ? $siteItem['lastSeen'] : null); ?></span>
                            </div>
                            <div class="text-muted small mt-2">
                                <span class="bi bi-bug me-1"></span>
                                <?php echo $L->g('spider-log-site-agent'); ?>
                                <span class="fw-semibold ms-1"><?php echo Sanitize::html(isset($siteItem['topAgent']['label']) ? $siteItem['topAgent']['label'] : '—'); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0"><?php echo $L->g('spider-log-site-empty'); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xxl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('spider-log-suspicious-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-log-suspicious-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('spider-log-table-time'); ?></th>
                                    <th><?php echo $L->g('spider-log-table-site'); ?></th>
                                    <th><?php echo $L->g('spider-log-table-ip'); ?></th>
                                    <th><?php echo $L->g('spider-log-table-url'); ?></th>
                                    <th><?php echo $L->g('spider-log-table-status'); ?></th>
                                    <th><?php echo $L->g('spider-log-table-ua'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($spiderSuspicious)): ?>
                                    <?php foreach ($spiderSuspicious as $entry): ?>
                                        <tr>
                                            <td class="small text-muted">
                                                <div><?php echo mgwSpiderLogFormatDateTime(isset($entry['time']) ? $entry['time'] : null); ?></div>
                                                <span class="badge bg-light text-dark border mt-1"><?php echo spiderLogRelativeTime(isset($entry['time']) ? $entry['time'] : null); ?></span>
                                            </td>
                                            <td class="fw-semibold small"><?php echo Sanitize::html(isset($entry['site']) ? $entry['site'] : '--'); ?></td>
                                            <td class="text-monospace small">&ZeroWidthSpace;<?php echo Sanitize::html(isset($entry['ip']) ? $entry['ip'] : '--'); ?></td>
                                            <td class="small text-truncate" title="<?php echo Sanitize::html(isset($entry['url']) ? $entry['url'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['url']) ? $entry['url'] : '--'); ?></td>
                                            <td>
                                                <span class="badge <?php echo spiderLogStatusBadgeClass(isset($entry['status']) ? $entry['status'] : null); ?>"><?php echo Sanitize::html(isset($entry['status']) ? $entry['status'] : '--'); ?></span>
                                            </td>
                                            <td class="small" title="<?php echo Sanitize::html(isset($entry['ua']) ? $entry['ua'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['engine']) ? $entry['engine'] : '--'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4"><?php echo $L->g('spider-log-suspicious-empty'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('spider-log-archives-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-log-archives-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($spiderArchives)): ?>
                        <div class="spider-archives-grid">
                            <?php foreach ($spiderArchives as $archive): ?>
                                <?php
                                    $archiveType = isset($archive['type']) ? (string)$archive['type'] : 'daily';
                                    $typeKey = 'spider-log-archives-' . $archiveType;
                                    $typeLabel = $L->g($typeKey);
                                    if (empty($typeLabel) || $typeLabel === $typeKey) {
                                        $typeLabel = ucfirst($archiveType);
                                    }
                                ?>
                                <div class="spider-archive border rounded-3 p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="badge bg-secondary-subtle text-secondary text-uppercase"><?php echo Sanitize::html($typeLabel); ?></span>
                                        <span class="text-muted small"><?php echo mgwSpiderLogFormatDateTime(isset($archive['updated']) ? $archive['updated'] : null); ?></span>
                                    </div>
                                    <div class="fw-semibold mt-3" title="<?php echo Sanitize::html(isset($archive['label']) ? $archive['label'] : ''); ?>"><?php echo Sanitize::html(isset($archive['label']) ? $archive['label'] : ''); ?></div>
                                    <div class="text-muted small mt-1">
                                        <?php echo $L->g('spider-log-field-total'); ?> <?php echo number_format(isset($archive['count']) ? $archive['count'] : 0); ?> · <?php echo Sanitize::html(isset($archive['size']) ? $archive['size'] : ''); ?>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-3">
                                        <a class="btn btn-sm btn-outline-secondary" href="#"><?php echo $L->g('spider-log-archives-download'); ?></a>
                                        <button class="btn btn-sm btn-outline-danger" type="button"><?php echo $L->g('spider-log-action-clean-site'); ?></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light mb-0"><?php echo $L->g('spider-log-archives-empty'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xxl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('spider-log-retention-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-log-retention-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <div>
                            <div class="fw-semibold fs-4 text-primary"><?php echo sprintf($L->g('spider-log-retention-days'), isset($spiderRetention['days']) ? (int)$spiderRetention['days'] : 30); ?></div>
                            <div class="text-muted small mt-1"><?php echo $L->g('spider-log-retention-clean'); ?> <?php echo mgwSpiderLogFormatDateTime(isset($spiderRetention['lastClean']) ? $spiderRetention['lastClean'] : null); ?></div>
                        </div>
                        <div>
                            <div class="text-muted small"><?php echo $L->g('spider-log-retention-last'); ?> <span class="fw-semibold"><?php echo spiderLogRelativeTime(isset($spiderRetention['lastClean']) ? $spiderRetention['lastClean'] : null); ?></span></div>
                            <div class="text-muted small mt-1"><?php echo $L->g('spider-log-retention-next'); ?> <span class="fw-semibold"><?php echo mgwSpiderLogFormatDateTime(isset($spiderRetention['nextClean']) ? $spiderRetention['nextClean'] : null); ?></span></div>
                        </div>
                        <div class="ms-auto d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm"><?php echo $L->g('spider-log-action-retention'); ?></button>
                            <button type="button" class="btn btn-outline-success btn-sm"><?php echo $L->g('spider-log-action-test'); ?></button>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <span class="bi bi-info-circle me-2"></span><?php echo $L->g('spider-log-retention-desc'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('spider-log-rules-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-log-rules-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="spider-rule-pill bg-light border text-muted px-3 py-2 rounded-3">
                            <?php echo $L->g('spider-log-rules-built-in'); ?>: <span class="fw-semibold"><?php echo number_format(isset($spiderRuleStats['builtin']) ? $spiderRuleStats['builtin'] : 0); ?></span>
                        </div>
                        <div class="spider-rule-pill bg-light border text-muted px-3 py-2 rounded-3">
                            <?php echo $L->g('spider-log-rules-custom'); ?>: <span class="fw-semibold"><?php echo number_format(isset($spiderRuleStats['custom']) ? $spiderRuleStats['custom'] : 0); ?></span>
                        </div>
                        <button class="btn btn-outline-primary btn-sm ms-auto" type="button"><?php echo $L->g('spider-log-rules-add'); ?></button>
                    </div>
                    <div class="spider-rule-list">
                        <?php if (!empty($spiderBuiltinRules)): ?>
                            <?php foreach ($spiderBuiltinRules as $rule): ?>
                                <div class="spider-rule-item border-bottom py-2">
                                    <div class="fw-semibold"><?php echo Sanitize::html($rule['label']); ?></div>
                                    <div class="text-muted small mt-1">
                                        <?php echo $L->g('spider-log-field-provider'); ?>: <?php echo Sanitize::html(isset($rule['provider']) ? $rule['provider'] : '--'); ?> · <?php echo $L->g('spider-log-field-pattern'); ?>: <?php echo Sanitize::html(implode(', ', isset($rule['patterns']) && is_array($rule['patterns']) ? $rule['patterns'] : array())); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($spiderCustomRules)): ?>
                            <?php foreach ($spiderCustomRules as $rule): ?>
                                <div class="spider-rule-item border-bottom py-2">
                                    <div class="fw-semibold text-primary"><?php echo Sanitize::html(isset($rule['label']) ? $rule['label'] : 'Custom Spider'); ?></div>
                                    <div class="text-muted small mt-1">
                                        <?php echo $L->g('spider-log-field-pattern'); ?>: <?php echo Sanitize::html(isset($rule['pattern']) ? $rule['pattern'] : (isset($rule['contains']) ? implode(', ', (array)$rule['contains']) : '')); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php elseif (empty($spiderBuiltinRules)): ?>
                            <div class="alert alert-light mb-0"><?php echo $L->g('spider-log-rules-empty'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('spider-log-table-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('spider-log-table-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('spider-log-table-time'); ?></th>
                            <th><?php echo $L->g('spider-log-table-site'); ?></th>
                            <th><?php echo $L->g('spider-log-table-ip'); ?></th>
                            <th><?php echo $L->g('spider-log-table-url'); ?></th>
                            <th><?php echo $L->g('spider-log-table-status'); ?></th>
                            <th><?php echo $L->g('spider-log-table-engine'); ?></th>
                            <th><?php echo $L->g('spider-log-table-duration'); ?></th>
                            <th class="text-end"><?php echo $L->g('spider-log-table-actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($spiderLogs)): ?>
                            <?php foreach ($spiderLogs as $entry): ?>
                                <?php $encoded = Sanitize::html(json_encode($entry)); ?>
                                <tr>
                                    <td class="small text-muted">
                                        <div><?php echo mgwSpiderLogFormatDateTime(isset($entry['time']) ? $entry['time'] : null); ?></div>
                                        <span class="badge bg-light text-dark border mt-1"><?php echo spiderLogRelativeTime(isset($entry['time']) ? $entry['time'] : null); ?></span>
                                    </td>
                                    <td class="fw-semibold small text-truncate" title="<?php echo Sanitize::html(isset($entry['site']) ? $entry['site'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['site']) ? $entry['site'] : '--'); ?></td>
                                    <td class="text-monospace small">&ZeroWidthSpace;<?php echo Sanitize::html(isset($entry['ip']) ? $entry['ip'] : '--'); ?></td>
                                    <td class="small text-truncate" title="<?php echo Sanitize::html(isset($entry['url']) ? $entry['url'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['url']) ? $entry['url'] : '--'); ?></td>
                                    <td>
                                        <span class="badge <?php echo spiderLogStatusBadgeClass(isset($entry['status']) ? $entry['status'] : null); ?>"><?php echo Sanitize::html(isset($entry['status']) ? $entry['status'] : '--'); ?></span>
                                    </td>
                                    <td class="small" title="<?php echo Sanitize::html(isset($entry['ua']) ? $entry['ua'] : '--'); ?>">
                                        <div class="fw-semibold"><?php echo Sanitize::html(isset($entry['engine']) ? $entry['engine'] : '--'); ?></div>
                                        <div class="text-muted text-uppercase small"><?php echo Sanitize::html(isset($entry['confidence']) ? $entry['confidence'] : ''); ?></div>
                                    </td>
                                    <td class="small fw-semibold"><?php echo mgwSpiderLogFormatDuration(isset($entry['duration']) ? $entry['duration'] : null); ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-outline-primary btn-sm spider-log-detail" type="button" data-entry="<?php echo $encoded; ?>">
                                            <span class="bi bi-card-text"></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4"><?php echo $L->g('spider-log-table-empty'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="spiderLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $L->g('spider-log-modal-title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo $L->g('spider-log-modal-close'); ?>"></button>
            </div>
            <div class="modal-body">
                <pre class="spider-log-modal-body mb-0"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo $L->g('spider-log-modal-close'); ?></button>
                <button type="button" class="btn btn-primary spider-log-copy"><?php echo $L->g('spider-log-modal-copy'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const trendButtons = document.querySelectorAll('[data-spider-trend-range]');
    const trendPanels = document.querySelectorAll('.spider-trend-range');
    trendButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const range = button.getAttribute('data-spider-trend-range');
            trendButtons.forEach(function (btn) { btn.classList.toggle('active', btn === button); });
            trendPanels.forEach(function (panel) { panel.classList.toggle('active', panel.getAttribute('data-range') === range); });
        });
    });

    const modalEl = document.getElementById('spiderLogModal');
    if (!modalEl) {
        return;
    }
    const modalBody = modalEl.querySelector('.spider-log-modal-body');
    const copyBtn = modalEl.querySelector('.spider-log-copy');
    const detailButtons = document.querySelectorAll('.spider-log-detail');
    detailButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const payload = button.getAttribute('data-entry');
            let pretty = payload;
            try {
                const parsed = JSON.parse(payload);
                pretty = JSON.stringify(parsed, null, 2);
            } catch (err) {
                pretty = payload;
            }
            modalBody.textContent = pretty;
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });
    });
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            if (!modalBody) {
                return;
            }
            navigator.clipboard.writeText(modalBody.textContent || '').catch(function () { /* noop */ });
        });
    }
})();
</script>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>spider-log.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">
