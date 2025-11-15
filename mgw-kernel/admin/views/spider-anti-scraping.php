<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$threatOverview = isset($threatOverview) && is_array($threatOverview) ? $threatOverview : array();
$trafficTrends = isset($trafficTrends) && is_array($trafficTrends) ? $trafficTrends : array('requests' => array(), 'bots' => array(), 'blocked' => array());
$blockedTimeline = isset($blockedTimeline) && is_array($blockedTimeline) ? $blockedTimeline : array();
$ipRateLimits = isset($ipRateLimits) && is_array($ipRateLimits) ? $ipRateLimits : array();
$uaBlacklist = isset($uaBlacklist) && is_array($uaBlacklist) ? $uaBlacklist : array();
$pathProtections = isset($pathProtections) && is_array($pathProtections) ? $pathProtections : array();
$automationPolicies = isset($automationPolicies) && is_array($automationPolicies) ? $automationPolicies : array();
$fingerprintLibrary = isset($fingerprintLibrary) && is_array($fingerprintLibrary) ? $fingerprintLibrary : array();
$manualEntries = isset($manualEntries) && is_array($manualEntries) ? $manualEntries : array();
$detectionStats = isset($detectionStats) && is_array($detectionStats) ? $detectionStats : array();
$antiScrapeAlerts = isset($antiScrapeAlerts) && is_array($antiScrapeAlerts) ? $antiScrapeAlerts : array();
$uaDistribution = isset($uaDistribution) && is_array($uaDistribution) ? $uaDistribution : array();

if (!function_exists('spiderAntiStatusBadgeClass')) {
    function spiderAntiStatusBadgeClass($status)
    {
        switch ($status) {
            case 'ok':
                return 'bg-success-subtle text-success';
            case 'warn':
                return 'bg-warning-subtle text-warning';
            case 'fail':
                return 'bg-danger-subtle text-danger';
            case 'info':
                return 'bg-info-subtle text-info';
            default:
                return 'bg-secondary-subtle text-secondary';
        }
    }
}

if (!function_exists('spiderAntiStatusLabel')) {
    function spiderAntiStatusLabel($status, $language)
    {
        $key = 'spider-anti-status-' . $status;
        return $language->g($key) ?: strtoupper($status);
    }
}

if (!function_exists('spiderAntiFormatPercent')) {
    function spiderAntiFormatPercent($value)
    {
        if (!is_numeric($value)) {
            return '--';
        }
        return number_format((float)$value, 1) . '%';
    }
}

if (!function_exists('spiderAntiRenderSparkline')) {
    function spiderAntiRenderSparkline($values)
    {
        if (!is_array($values) || empty($values)) {
            return '<div class="sparkline sparkline-empty"></div>';
        }
        $numbers = array();
        foreach ($values as $value) {
            $numbers[] = is_numeric($value) ? (float)$value : 0;
        }
        $max = max($numbers);
        $max = $max > 0 ? $max : 1;
        $bars = array();
        foreach ($numbers as $number) {
            $height = max(2, ($number / $max) * 32);
            $bars[] = '<span style="height:' . number_format($height, 2) . 'px"></span>';
        }
        return '<div class="sparkline">' . implode('', $bars) . '</div>';
    }
}

if (!function_exists('spiderAntiFormatTimestamp')) {
    function spiderAntiFormatTimestamp($value)
    {
        if (!is_numeric($value)) {
            return '--';
        }
        return date('Y-m-d H:i:s', (int)$value);
    }
}

if (!function_exists('spiderAntiFormatWindow')) {
    function spiderAntiFormatWindow($seconds)
    {
        if (!is_numeric($seconds) || $seconds <= 0) {
            return '--';
        }
        if ($seconds < 60) {
            return $seconds . 's';
        }
        if ($seconds < 3600) {
            return round($seconds / 60) . 'm';
        }
        if ($seconds < 86400) {
            return round($seconds / 3600, 1) . 'h';
        }
        return round($seconds / 86400, 1) . 'd';
    }
}
?>

<div class="spider-anti-page container-fluid px-0">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1 d-flex align-items-center gap-2">
                <span class="bi bi-shield-slash"></span>
                <span><?php echo $L->g('spider-anti-title'); ?></span>
            </h2>
            <p class="text-muted mb-0 small"><?php echo $L->g('spider-anti-subtitle'); ?></p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?php echo Sanitize::html(HTML_PATH_ADMIN_ROOT . 'spider-anti-scraping'); ?>" class="btn btn-outline-primary">
                <span class="bi bi-arrow-repeat"></span>
                <span class="ms-1"><?php echo $L->g('spider-anti-refresh'); ?></span>
            </a>
            <a href="#" class="btn btn-primary">
                <span class="bi bi-ban"></span>
                <span class="ms-1"><?php echo $L->g('spider-anti-add-ua'); ?></span>
            </a>
            <a href="#" class="btn btn-outline-danger">
                <span class="bi bi-hammer"></span>
                <span class="ms-1"><?php echo $L->g('spider-anti-emergency-lock'); ?></span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <?php if (!empty($threatOverview)): ?>
                    <?php foreach ($threatOverview as $card): ?>
                        <div class="col-sm-6 col-lg-3">
                            <div class="spider-overview-card border rounded-3 h-100 p-3">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <div class="text-muted small text-uppercase fw-semibold"><?php echo Sanitize::html($card['label']); ?></div>
                                        <div class="fs-3 fw-semibold mt-2 text-truncate" title="<?php echo Sanitize::html($card['value']); ?>"><?php echo Sanitize::html($card['value']); ?></div>
                                    </div>
                                    <span class="badge <?php echo spiderAntiStatusBadgeClass(isset($card['status']) ? $card['status'] : 'unknown'); ?>">
                                        <?php echo Sanitize::html(spiderAntiStatusLabel(isset($card['status']) ? $card['status'] : 'unknown', $L)); ?>
                                    </span>
                                </div>
                                <?php if (!empty($card['hint'])): ?>
                                    <p class="text-muted small mt-3 mb-0"><?php echo Sanitize::html($card['hint']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col">
                        <div class="alert alert-warning mb-0"><?php echo Sanitize::html($L->g('spider-anti-overview-empty')); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('spider-anti-traffic-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-anti-traffic-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="spider-line-chart">
                        <div class="spider-line-row">
                            <div>
                                <span class="badge bg-primary-subtle text-primary"></span>
                                <span class="small text-muted ms-2"><?php echo $L->g('spider-anti-traffic-requests'); ?></span>
                            </div>
                            <?php echo spiderAntiRenderSparkline(isset($trafficTrends['requests']) ? $trafficTrends['requests'] : array()); ?>
                        </div>
                        <div class="spider-line-row">
                            <div>
                                <span class="badge bg-warning-subtle text-warning"></span>
                                <span class="small text-muted ms-2"><?php echo $L->g('spider-anti-traffic-bots'); ?></span>
                            </div>
                            <?php echo spiderAntiRenderSparkline(isset($trafficTrends['bots']) ? $trafficTrends['bots'] : array()); ?>
                        </div>
                        <div class="spider-line-row">
                            <div>
                                <span class="badge bg-danger-subtle text-danger"></span>
                                <span class="small text-muted ms-2"><?php echo $L->g('spider-anti-traffic-blocked'); ?></span>
                            </div>
                            <?php echo spiderAntiRenderSparkline(isset($trafficTrends['blocked']) ? $trafficTrends['blocked'] : array()); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('spider-anti-ua-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-anti-ua-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($uaDistribution)): ?>
                        <div class="spider-ua-grid">
                            <?php foreach ($uaDistribution as $name => $map): ?>
                                <div class="spider-ua-card border rounded-3 p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="fw-semibold text-truncate" title="<?php echo Sanitize::html($name); ?>"><?php echo Sanitize::html($name); ?></span>
                                        <a href="#" class="small text-muted"><?php echo $L->g('spider-anti-ua-view'); ?></a>
                                    </div>
                                    <div class="spider-ua-bars mt-3">
                                        <?php if (is_array($map) && !empty($map)): ?>
                                            <?php $total = array_sum(array_map(function ($v) { return is_numeric($v) ? (float)$v : 0; }, $map)); ?>
                                            <?php $total = $total > 0 ? $total : 1; ?>
                                            <?php foreach ($map as $ua => $count): ?>
                                                <?php $ratio = is_numeric($count) ? ((float)$count / $total) * 100 : 0; ?>
                                                <div class="spider-ua-bar">
                                                    <span class="text-truncate" title="<?php echo Sanitize::html($ua); ?>"><?php echo Sanitize::html($ua); ?></span>
                                                    <div class="meter"><span style="width: <?php echo min(100, $ratio); ?>%"></span></div>
                                                    <span class="small text-muted ms-2"><?php echo number_format((float)$ratio, 1); ?>%</span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted small"><?php echo $L->g('spider-anti-ua-empty-site'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0"><?php echo Sanitize::html($L->g('spider-anti-ua-empty')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('spider-anti-rate-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('spider-anti-rate-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('spider-anti-rate-table-scope'); ?></th>
                            <th class="text-center" style="width: 120px;"><?php echo $L->g('spider-anti-rate-table-type'); ?></th>
                            <th class="text-center" style="width: 160px;"><?php echo $L->g('spider-anti-rate-table-threshold'); ?></th>
                            <th class="text-center" style="width: 120px;"><?php echo $L->g('spider-anti-rate-table-window'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('spider-anti-rate-table-blocked'); ?></th>
                            <th class="text-center" style="width: 120px;"><?php echo $L->g('spider-anti-rate-table-status'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ipRateLimits)): ?>
                            <?php foreach ($ipRateLimits as $limit): ?>
                                <tr>
                                    <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html($limit['scope']); ?>"><?php echo Sanitize::html($limit['scope']); ?></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?php echo Sanitize::html(strtoupper((string)$limit['type'])); ?></span></td>
                                    <td class="text-center fw-semibold"><?php echo number_format((int)$limit['threshold']); ?></td>
                                    <td class="text-center text-muted small"><?php echo spiderAntiFormatWindow(isset($limit['period']) ? $limit['period'] : (isset($limit['window']) ? $limit['window'] : 0)); ?></td>
                                    <td class="text-center text-muted small"><?php echo number_format((int)$limit['blocked']); ?></td>
                                    <td class="text-center"><span class="badge <?php echo spiderAntiStatusBadgeClass(isset($limit['status']) ? $limit['status'] : 'unknown'); ?>"><?php echo Sanitize::html(spiderAntiStatusLabel(isset($limit['status']) ? $limit['status'] : 'unknown', $L)); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <span class="bi bi-speedometer2 fs-1 d-block mb-2"></span>
                                    <span><?php echo $L->g('spider-anti-rate-empty'); ?></span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('spider-anti-blacklist-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-anti-blacklist-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('spider-anti-blacklist-table-ua'); ?></th>
                                    <th><?php echo $L->g('spider-anti-blacklist-table-reason'); ?></th>
                                    <th class="text-center" style="width: 140px;"><?php echo $L->g('spider-anti-blacklist-table-hits'); ?></th>
                                    <th class="text-center" style="width: 160px;"><?php echo $L->g('spider-anti-blacklist-table-last'); ?></th>
                                    <th style="width: 100px;" class="text-center"><?php echo $L->g('spider-anti-blacklist-table-actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($uaBlacklist)): ?>
                                    <?php $uaRows = is_array($uaBlacklist) ? array_values($uaBlacklist) : array(); ?>
                                    <?php $uaRows = array_slice($uaRows, 0, 12); ?>
                                    <?php foreach ($uaRows as $entry): ?>
                                        <?php if (!is_array($entry)) { continue; } ?>
                                        <tr>
                                            <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html(isset($entry['ua']) ? $entry['ua'] : ''); ?>"><?php echo Sanitize::html(isset($entry['ua']) ? $entry['ua'] : ''); ?></td>
                                            <td class="small text-muted text-truncate" title="<?php echo Sanitize::html(isset($entry['reason']) ? $entry['reason'] : ''); ?>"><?php echo Sanitize::html(isset($entry['reason']) ? $entry['reason'] : ''); ?></td>
                                            <td class="text-center fw-semibold"><?php echo number_format(isset($entry['hits']) ? (int)$entry['hits'] : 0); ?></td>
                                            <td class="text-center text-muted small"><?php echo spiderAntiFormatTimestamp(isset($entry['lastSeen']) ? $entry['lastSeen'] : null); ?></td>
                                            <td class="text-center">
                                                <a href="#" class="btn btn-outline-danger btn-sm"><?php echo $L->g('spider-anti-blacklist-remove'); ?></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4"><?php echo $L->g('spider-anti-blacklist-empty'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('spider-anti-blocked-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-anti-blocked-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($blockedTimeline)): ?>
                        <div class="spider-blocked-grid">
                            <?php foreach ($blockedTimeline as $siteName => $series): ?>
                                <div class="spider-blocked-card border rounded-3 p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="fw-semibold text-truncate" title="<?php echo Sanitize::html($siteName); ?>"><?php echo Sanitize::html($siteName); ?></span>
                                        <span class="badge bg-danger-subtle text-danger"><?php echo $L->g('spider-anti-blocked-label'); ?></span>
                                    </div>
                                    <div class="mt-3">
                                        <?php echo spiderAntiRenderSparkline(is_array($series) ? array_values($series) : array()); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0"><?php echo Sanitize::html($L->g('spider-anti-blocked-empty')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('spider-anti-path-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-anti-path-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($pathProtections)): ?>
                            <?php foreach ($pathProtections as $rule): ?>
                                <div class="list-group-item d-flex align-items-start justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($rule['pattern']); ?>"><?php echo Sanitize::html($rule['pattern']); ?></div>
                                        <div class="small text-muted mt-1"><?php echo Sanitize::html($rule['description']); ?></div>
                                    </div>
                                    <span class="badge bg-light text-dark border"><?php echo Sanitize::html(strtoupper((string)$rule['action'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center text-muted py-5">
                                <span class="bi bi-link-45deg fs-1 d-block mb-2"></span>
                                <span><?php echo $L->g('spider-anti-path-empty'); ?></span>
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
                        <h5 class="mb-0"><?php echo $L->g('spider-anti-automation-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-anti-automation-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('spider-anti-automation-table-policy'); ?></th>
                                    <th class="text-center" style="width: 140px;"><?php echo $L->g('spider-anti-automation-table-threshold'); ?></th>
                                    <th class="text-center" style="width: 120px;"><?php echo $L->g('spider-anti-automation-table-window'); ?></th>
                                    <th class="text-center" style="width: 120px;"><?php echo $L->g('spider-anti-automation-table-ban'); ?></th>
                                    <th class="text-center" style="width: 120px;"><?php echo $L->g('spider-anti-automation-table-status'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($automationPolicies)): ?>
                                    <?php foreach ($automationPolicies as $policy): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($policy['name']); ?>"><?php echo Sanitize::html($policy['name']); ?></div>
                                                <div class="small text-muted"><?php echo Sanitize::html($policy['description']); ?></div>
                                            </td>
                                            <td class="text-center fw-semibold"><?php echo number_format((int)$policy['threshold']); ?></td>
                                            <td class="text-center text-muted small"><?php echo spiderAntiFormatWindow(isset($policy['window']) ? $policy['window'] : 0); ?></td>
                                            <td class="text-center text-muted small"><?php echo spiderAntiFormatWindow(isset($policy['ban']) ? $policy['ban'] : 0); ?></td>
                                            <td class="text-center"><span class="badge <?php echo spiderAntiStatusBadgeClass(isset($policy['status']) ? $policy['status'] : 'unknown'); ?>"><?php echo Sanitize::html(spiderAntiStatusLabel(isset($policy['status']) ? $policy['status'] : 'unknown', $L)); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4"><?php echo $L->g('spider-anti-automation-empty'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('spider-anti-fingerprint-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-anti-fingerprint-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('spider-anti-fingerprint-table-id'); ?></th>
                                    <th><?php echo $L->g('spider-anti-fingerprint-table-signature'); ?></th>
                                    <th style="width: 140px;" class="text-center"><?php echo $L->g('spider-anti-fingerprint-table-matches'); ?></th>
                                    <th style="width: 140px;" class="text-center"><?php echo $L->g('spider-anti-fingerprint-table-last'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($fingerprintLibrary)): ?>
                                    <?php foreach (array_slice($fingerprintLibrary, 0, 12) as $fingerprint): ?>
                                        <tr>
                                            <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html(isset($fingerprint['id']) ? $fingerprint['id'] : ''); ?>"><?php echo Sanitize::html(isset($fingerprint['id']) ? $fingerprint['id'] : ''); ?></td>
                                            <td class="small text-muted text-truncate" title="<?php echo Sanitize::html(isset($fingerprint['signature']) ? $fingerprint['signature'] : ''); ?>"><?php echo Sanitize::html(isset($fingerprint['signature']) ? $fingerprint['signature'] : ''); ?></td>
                                            <td class="text-center fw-semibold"><?php echo number_format(isset($fingerprint['matches']) ? (int)$fingerprint['matches'] : 0); ?></td>
                                            <td class="text-center text-muted small"><?php echo spiderAntiFormatTimestamp(isset($fingerprint['lastSeen']) ? $fingerprint['lastSeen'] : null); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <span class="bi bi-ui-checks-grid fs-1 d-block mb-2"></span>
                                            <span><?php echo $L->g('spider-anti-fingerprint-empty'); ?></span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('spider-anti-manual-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('spider-anti-manual-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($manualEntries)): ?>
                            <?php foreach (array_reverse($manualEntries) as $entry): ?>
                                <div class="list-group-item">
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="badge bg-light text-dark border"><?php echo Sanitize::html(strtoupper(isset($entry['type']) ? $entry['type'] : 'UA')); ?></span>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html(isset($entry['value']) ? $entry['value'] : ''); ?>"><?php echo Sanitize::html(isset($entry['value']) ? $entry['value'] : ''); ?></div>
                                            <div class="small text-muted mt-1">
                                                <?php if (!empty($entry['site'])): ?>
                                                    <span class="badge bg-primary-subtle text-primary me-1"><?php echo Sanitize::html($entry['site']); ?></span>
                                                <?php endif; ?>
                                                <?php echo Sanitize::html(isset($entry['reason']) ? $entry['reason'] : ''); ?>
                                            </div>
                                        </div>
                                        <div class="text-end small text-muted">
                                            <?php echo spiderAntiFormatTimestamp(isset($entry['created']) ? $entry['created'] : null); ?>
                                            <a href="#" class="d-block mt-1 text-danger"><?php echo $L->g('spider-anti-manual-remove'); ?></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center text-muted py-5">
                                <span class="bi bi-clipboard-x fs-1 d-block mb-2"></span>
                                <span><?php echo $L->g('spider-anti-manual-empty'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('spider-anti-detection-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('spider-anti-detection-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php if (!empty($detectionStats)): ?>
                    <?php foreach ($detectionStats as $key => $value): ?>
                        <?php if ($key === 'challenge' && is_array($value)): ?>
                            <div class="col-sm-6 col-lg-3">
                                <div class="spider-detect-card border rounded-3 p-3 h-100">
                                    <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('spider-anti-detection-challenge'); ?></div>
                                    <div class="d-flex align-items-center justify-content-between mt-3">
                                        <div>
                                            <div class="fw-semibold small text-muted"><?php echo $L->g('spider-anti-detection-js'); ?></div>
                                            <div class="fs-5 fw-semibold"><?php echo number_format(isset($value['js']) ? (int)$value['js'] : 0); ?></div>
                                        </div>
                                        <div>
                                            <div class="fw-semibold small text-muted text-end"><?php echo $L->g('spider-anti-detection-cookie'); ?></div>
                                            <div class="fs-5 fw-semibold text-end"><?php echo number_format(isset($value['cookie']) ? (int)$value['cookie'] : 0); ?></div>
                                        </div>
                                    </div>
                                    <div class="mt-3 d-flex align-items-center justify-content-between text-muted small">
                                        <span><?php echo $L->g('spider-anti-detection-pass'); ?></span>
                                        <span class="fw-semibold text-success"><?php echo number_format(isset($value['passed']) ? (int)$value['passed'] : 0); ?></span>
                                    </div>
                                    <div class="mt-1 d-flex align-items-center justify-content-between text-muted small">
                                        <span><?php echo $L->g('spider-anti-detection-fail'); ?></span>
                                        <span class="fw-semibold text-danger"><?php echo number_format(isset($value['failed']) ? (int)$value['failed'] : 0); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-sm-6 col-lg-3">
                                <div class="spider-detect-card border rounded-3 p-3 h-100">
                                    <div class="text-muted small text-uppercase fw-semibold"><?php echo Sanitize::html($L->g('spider-anti-detection-' . $key) ?: strtoupper((string)$key)); ?></div>
                                    <div class="fs-3 fw-semibold mt-2"><?php echo number_format(is_numeric($value) ? (float)$value : 0); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col">
                        <div class="alert alert-info mb-0"><?php echo Sanitize::html($L->g('spider-anti-detection-empty')); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('spider-anti-alerts-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('spider-anti-alerts-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($antiScrapeAlerts)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($antiScrapeAlerts as $alert): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge <?php echo spiderAntiStatusBadgeClass($alert['status']); ?>">
                                    <span class="bi <?php echo $alert['status'] === 'fail' ? 'bi-exclamation-octagon' : ($alert['status'] === 'warn' ? 'bi-shield-exclamation' : 'bi-info-circle'); ?>"></span>
                                </span>
                                <div>
                                    <div class="fw-semibold">
                                        <?php if (!empty($alert['site'])): ?>
                                            <span class="badge bg-primary-subtle text-primary me-2"><?php echo Sanitize::html($alert['site']); ?></span>
                                        <?php endif; ?>
                                        <?php echo Sanitize::html($alert['title']); ?>
                                    </div>
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
                    <span><?php echo $L->g('spider-anti-alerts-empty'); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>spider-anti-scraping.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">