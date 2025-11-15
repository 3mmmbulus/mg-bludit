<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$overviewCards = isset($overviewCards) && is_array($overviewCards) ? $overviewCards : array();
$siteSpeedSnapshots = isset($siteSpeedSnapshots) && is_array($siteSpeedSnapshots) ? $siteSpeedSnapshots : array();
$uxMetrics = isset($uxMetrics) && is_array($uxMetrics) ? $uxMetrics : array();
$cmsPerformance = isset($cmsPerformance) && is_array($cmsPerformance) ? $cmsPerformance : array();
$resourceUsage = isset($resourceUsage) && is_array($resourceUsage) ? $resourceUsage : array();
$queueSummaries = isset($queueSummaries) && is_array($queueSummaries) ? $queueSummaries : array();
$scheduleSummaries = isset($scheduleSummaries) && is_array($scheduleSummaries) ? $scheduleSummaries : array();
$clusterInsights = isset($clusterInsights) && is_array($clusterInsights) ? $clusterInsights : array();
$performanceAlerts = isset($performanceAlerts) && is_array($performanceAlerts) ? $performanceAlerts : array();

if (!function_exists('monitoringStatusBadgeClass')) {
    function monitoringStatusBadgeClass($status)
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

if (!function_exists('monitoringStatusIconClass')) {
    function monitoringStatusIconClass($status)
    {
        switch ($status) {
            case 'ok':
                return 'bi-check-circle';
            case 'warn':
                return 'bi-exclamation-triangle';
            case 'fail':
                return 'bi-slash-circle';
            default:
                return 'bi-info-circle';
        }
    }
}

if (!function_exists('monitoringStatusLabel')) {
    function monitoringStatusLabel($status, $language)
    {
        $key = 'monitoring-status-' . $status;
        return $language->g($key) ?: strtoupper($status);
    }
}

if (!function_exists('monitoringFormatLatency')) {
    function monitoringFormatLatency($value)
    {
        if ($value === null || !is_numeric($value)) {
            return '--';
        }
        $value = (float)$value;
        return ($value >= 1000 ? round($value) : round($value, 1)) . ' ms';
    }
}

if (!function_exists('monitoringFormatPercent')) {
    function monitoringFormatPercent($value)
    {
        if ($value === null || !is_numeric($value)) {
            return '--';
        }
        return round((float)$value, 1) . '%';
    }
}

if (!function_exists('monitoringFormatAge')) {
    function monitoringFormatAge($seconds)
    {
        if ($seconds === null || !is_numeric($seconds) || $seconds <= 0) {
            return '--';
        }
        $seconds = (int)$seconds;
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
            if (count($parts) === 2) {
                break;
            }
        }
        return !empty($parts) ? implode(' ', $parts) : $seconds . 's';
    }
}

if (!function_exists('monitoringFormatDate')) {
    function monitoringFormatDate($timestamp)
    {
        if ($timestamp === null || $timestamp === '') {
            return '--';
        }
        if (is_numeric($timestamp)) {
            $timestamp = (int)$timestamp;
            if ($timestamp <= 0) {
                return '--';
            }
            return date('Y-m-d H:i:s', $timestamp);
        }
        return htmlspecialchars((string)$timestamp, ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="monitoring-page container-fluid px-0">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1 d-flex align-items-center gap-2">
                <span class="bi bi-graph-up"></span>
                <span><?php echo $L->g('monitoring-title'); ?></span>
            </h2>
            <p class="text-muted mb-0 small"><?php echo $L->g('monitoring-subtitle'); ?></p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?php echo Sanitize::html(HTML_PATH_ADMIN_ROOT . 'monitoring'); ?>" class="btn btn-outline-primary">
                <span class="bi bi-arrow-repeat"></span>
                <span class="ms-1"><?php echo $L->g('monitoring-refresh'); ?></span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('monitoring-overview-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('monitoring-overview-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($overviewCards)): ?>
                <div class="row g-3">
                    <?php foreach ($overviewCards as $card): ?>
                        <div class="col-sm-6 col-lg-3">
                            <div class="monitoring-overview-card border rounded-3 p-3 h-100">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div>
                                        <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($card['label']); ?>">
                                            <?php echo Sanitize::html($card['label']); ?>
                                        </div>
                                        <div class="display-6 fw-semibold mt-2">
                                            <?php echo Sanitize::html($card['value']); ?>
                                        </div>
                                    </div>
                                    <span class="badge <?php echo monitoringStatusBadgeClass(isset($card['status']) ? $card['status'] : 'unknown'); ?>">
                                        <?php echo Sanitize::html(monitoringStatusLabel(isset($card['status']) ? $card['status'] : 'unknown', $L)); ?>
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
                <div class="alert alert-warning mb-0"><?php echo Sanitize::html($L->g('monitoring-overview-empty')); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('monitoring-speed-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('monitoring-speed-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('monitoring-speed-table-site'); ?></th>
                            <th class="text-center" style="width: 160px;"><?php echo $L->g('monitoring-speed-table-local'); ?></th>
                            <th class="text-center" style="width: 160px;"><?php echo $L->g('monitoring-speed-table-remote'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('monitoring-speed-table-availability'); ?></th>
                            <th class="text-center" style="width: 200px;"><?php echo $L->g('monitoring-speed-table-status'); ?></th>
                            <th><?php echo $L->g('monitoring-speed-table-last'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($siteSpeedSnapshots)): ?>
                            <?php foreach ($siteSpeedSnapshots as $snapshot): ?>
                                <tr>
                                    <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html($snapshot['label']); ?>">
                                        <?php echo Sanitize::html($snapshot['label']); ?>
                                        <?php if (!empty($snapshot['remoteProvider'])): ?>
                                            <span class="badge bg-light text-dark border ms-1"><?php echo Sanitize::html($snapshot['remoteProvider']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo Sanitize::html(monitoringFormatLatency($snapshot['localLatency'])); ?>
                                        <?php if (!empty($snapshot['localSamples'])): ?>
                                            <div class="small text-muted"><?php echo sprintf($L->g('monitoring-speed-samples'), (int)$snapshot['localSamples']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo Sanitize::html(monitoringFormatLatency($snapshot['remoteLatency'])); ?>
                                        <?php if (!empty($snapshot['remoteSamples'])): ?>
                                            <div class="small text-muted"><?php echo sprintf($L->g('monitoring-speed-samples'), (int)$snapshot['remoteSamples']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">
                                            <?php echo Sanitize::html(monitoringFormatPercent(isset($snapshot['availability']) ? $snapshot['availability'] : null)); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo monitoringStatusBadgeClass($snapshot['status']); ?>">
                                            <?php echo Sanitize::html(monitoringStatusLabel($snapshot['status'], $L)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-muted"><?php echo monitoringFormatDate($snapshot['lastChecked']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <span class="bi bi-speedometer2 fs-1 d-block mb-2"></span>
                                    <span><?php echo $L->g('monitoring-speed-empty'); ?></span>
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
                <h5 class="mb-0"><?php echo $L->g('monitoring-ux-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('monitoring-ux-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($uxMetrics)): ?>
                <div class="row g-3">
                    <?php foreach ($uxMetrics as $ux): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="monitoring-ux-card border rounded-3 h-100 p-3">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($ux['label']); ?>">
                                            <?php echo Sanitize::html($ux['label']); ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?php echo $L->g('monitoring-ux-last'); ?>
                                            <span class="fw-semibold ms-1"><?php echo monitoringFormatDate($ux['recordedAt']); ?></span>
                                        </div>
                                    </div>
                                    <span class="badge <?php echo monitoringStatusBadgeClass($ux['status']); ?>">
                                        <?php echo Sanitize::html(monitoringStatusLabel($ux['status'], $L)); ?>
                                    </span>
                                </div>
                                <div class="row g-2 mt-3">
                                    <div class="col-6">
                                        <div class="monitoring-ux-metric">
                                            <div class="text-uppercase small text-muted">TTFB</div>
                                            <div class="fw-semibold"><?php echo Sanitize::html(monitoringFormatLatency($ux['ttfb'])); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="monitoring-ux-metric">
                                            <div class="text-uppercase small text-muted">FCP</div>
                                            <div class="fw-semibold"><?php echo Sanitize::html(monitoringFormatLatency($ux['fcp'])); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="monitoring-ux-metric">
                                            <div class="text-uppercase small text-muted">LCP</div>
                                            <div class="fw-semibold"><?php echo Sanitize::html(monitoringFormatLatency($ux['lcp'])); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="monitoring-ux-metric">
                                            <div class="text-uppercase small text-muted">CLS</div>
                                            <div class="fw-semibold"><?php echo Sanitize::html($ux['cls'] !== null ? number_format((float)$ux['cls'], 2) : '--'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="monitoring-ux-metric">
                                            <div class="text-uppercase small text-muted">TTI</div>
                                            <div class="fw-semibold"><?php echo Sanitize::html(monitoringFormatLatency($ux['tti'])); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0"><?php echo Sanitize::html($L->g('monitoring-ux-empty')); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('monitoring-cms-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('monitoring-cms-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold"><?php echo $L->g('monitoring-cms-render'); ?></div>
                                    <div class="small text-muted"><?php echo $L->g('monitoring-cms-render-hint'); ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold"><?php echo Sanitize::html(monitoringFormatLatency(isset($cmsPerformance['renderTime']) ? $cmsPerformance['renderTime'] : null)); ?></div>
                                    <span class="badge <?php echo monitoringStatusBadgeClass(isset($cmsPerformance['status']['render']) ? $cmsPerformance['status']['render'] : 'unknown'); ?>">
                                        <?php echo Sanitize::html(monitoringStatusLabel(isset($cmsPerformance['status']['render']) ? $cmsPerformance['status']['render'] : 'unknown', $L)); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold"><?php echo $L->g('monitoring-cms-db'); ?></div>
                                    <div class="small text-muted"><?php echo $L->g('monitoring-cms-db-hint'); ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold">--</div>
                                    <span class="badge <?php echo monitoringStatusBadgeClass(isset($cmsPerformance['status']['db']) ? $cmsPerformance['status']['db'] : 'unknown'); ?>">
                                        <?php echo Sanitize::html(monitoringStatusLabel(isset($cmsPerformance['status']['db']) ? $cmsPerformance['status']['db'] : 'unknown', $L)); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold"><?php echo $L->g('monitoring-cms-io'); ?></div>
                                    <div class="small text-muted"><?php echo $L->g('monitoring-cms-io-hint'); ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold">--</div>
                                    <span class="badge <?php echo monitoringStatusBadgeClass(isset($cmsPerformance['status']['io']) ? $cmsPerformance['status']['io'] : 'unknown'); ?>">
                                        <?php echo Sanitize::html(monitoringStatusLabel(isset($cmsPerformance['status']['io']) ? $cmsPerformance['status']['io'] : 'unknown', $L)); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold"><?php echo $L->g('monitoring-cms-cache'); ?></div>
                                    <div class="small text-muted"><?php echo $L->g('monitoring-cms-cache-hint'); ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold">--</div>
                                    <span class="badge <?php echo monitoringStatusBadgeClass(isset($cmsPerformance['status']['cache']) ? $cmsPerformance['status']['cache'] : 'unknown'); ?>">
                                        <?php echo Sanitize::html(monitoringStatusLabel(isset($cmsPerformance['status']['cache']) ? $cmsPerformance['status']['cache'] : 'unknown', $L)); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold"><?php echo $L->g('monitoring-cms-generation'); ?></div>
                                    <div class="small text-muted"><?php echo $L->g('monitoring-cms-generation-hint'); ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold"><?php echo Sanitize::html(monitoringFormatLatency(isset($cmsPerformance['pageGeneration']) ? $cmsPerformance['pageGeneration'] : null)); ?></div>
                                    <span class="badge <?php echo monitoringStatusBadgeClass(isset($cmsPerformance['status']['generation']) ? $cmsPerformance['status']['generation'] : 'unknown'); ?>">
                                        <?php echo Sanitize::html(monitoringStatusLabel(isset($cmsPerformance['status']['generation']) ? $cmsPerformance['status']['generation'] : 'unknown', $L)); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('monitoring-resources-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('monitoring-resources-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($resourceUsage)): ?>
                        <div class="row g-3">
                            <?php foreach ($resourceUsage as $resource): ?>
                                <div class="col-sm-6">
                                    <div class="monitoring-resource-card border rounded-3 p-3 h-100">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div>
                                                <div class="fw-semibold text-truncate"><?php echo Sanitize::html($resource['label']); ?></div>
                                                <div class="display-6 fw-semibold mt-2">
                                                    <?php echo Sanitize::html($resource['value'] !== null ? ($resource['value'] . $resource['units']) : '--'); ?>
                                                </div>
                                            </div>
                                            <span class="badge <?php echo monitoringStatusBadgeClass($resource['status']); ?>">
                                                <?php echo Sanitize::html(monitoringStatusLabel($resource['status'], $L)); ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($resource['meta']) && is_array($resource['meta'])): ?>
                                            <ul class="list-unstyled small text-muted mt-3 mb-0">
                                                <?php foreach ($resource['meta'] as $metaKey => $metaValue): ?>
                                                    <li class="d-flex align-items-center gap-2">
                                                        <span class="text-uppercase fw-semibold"><?php echo Sanitize::html($metaKey); ?>:</span>
                                                        <span><?php echo Sanitize::html($metaValue); ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                        <?php if (!empty($resource['hint'])): ?>
                                            <p class="small text-muted mt-3 mb-0"><?php echo Sanitize::html($resource['hint']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0"><?php echo Sanitize::html($L->g('monitoring-resources-empty')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('monitoring-queue-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('monitoring-queue-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('monitoring-queue-table-name'); ?></th>
                                    <th class="text-center" style="width: 140px;"><?php echo $L->g('monitoring-queue-table-pending'); ?></th>
                                    <th class="text-center" style="width: 160px;"><?php echo $L->g('monitoring-queue-table-oldest'); ?></th>
                                    <th class="text-center" style="width: 160px;"><?php echo $L->g('monitoring-queue-table-runtime'); ?></th>
                                    <th class="text-center" style="width: 140px;"><?php echo $L->g('monitoring-queue-table-status'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($queueSummaries)): ?>
                                    <?php foreach ($queueSummaries as $queue): ?>
                                        <tr>
                                            <td class="fw-semibold text-truncate"><?php echo Sanitize::html($queue['name']); ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border"><?php echo (int)$queue['pending']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="small text-muted"><?php echo monitoringFormatAge($queue['oldestAge']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="small text-muted"><?php echo monitoringFormatLatency($queue['longestRuntime']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?php echo monitoringStatusBadgeClass($queue['status']); ?>">
                                                    <?php echo Sanitize::html(monitoringStatusLabel($queue['status'], $L)); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <span class="bi bi-clipboard-check fs-1 d-block mb-2"></span>
                                            <span><?php echo $L->g('monitoring-queue-empty'); ?></span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('monitoring-schedule-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('monitoring-schedule-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('monitoring-schedule-table-task'); ?></th>
                                    <th class="text-center" style="width: 160px;"><?php echo $L->g('monitoring-schedule-table-frequency'); ?></th>
                                    <th class="text-center" style="width: 200px;"><?php echo $L->g('monitoring-schedule-table-last'); ?></th>
                                    <th class="text-center" style="width: 160px;"><?php echo $L->g('monitoring-schedule-table-duration'); ?></th>
                                    <th class="text-center" style="width: 140px;"><?php echo $L->g('monitoring-schedule-table-status'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($scheduleSummaries)): ?>
                                    <?php foreach ($scheduleSummaries as $task): ?>
                                        <tr>
                                            <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html($task['name']); ?>">
                                                <?php echo Sanitize::html($task['label']); ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border"><?php echo Sanitize::html($task['frequency'] ?: '--'); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="small text-muted"><?php echo monitoringFormatDate($task['lastRun']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="small text-muted"><?php echo monitoringFormatLatency($task['avgDuration']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?php echo monitoringStatusBadgeClass($task['status']); ?>">
                                                    <?php echo Sanitize::html(monitoringStatusLabel($task['status'], $L)); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <span class="bi bi-calendar-check fs-1 d-block mb-2"></span>
                                            <span><?php echo $L->g('monitoring-schedule-empty'); ?></span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('monitoring-cluster-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('monitoring-cluster-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="monitoring-cluster-widget border rounded-3 p-3 h-100">
                        <div class="small text-muted text-uppercase fw-semibold"><?php echo $L->g('monitoring-cluster-sites'); ?></div>
                        <div class="display-5 fw-semibold mt-2"><?php echo isset($clusterInsights['siteCount']) ? (int)$clusterInsights['siteCount'] : 0; ?></div>
                        <p class="small text-muted mb-0"><?php echo $L->g('monitoring-cluster-sites-hint'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="monitoring-cluster-widget border rounded-3 p-3 h-100">
                        <div class="small text-muted text-uppercase fw-semibold"><?php echo $L->g('monitoring-cluster-latency'); ?></div>
                        <div class="display-5 fw-semibold mt-2"><?php echo Sanitize::html(monitoringFormatLatency(isset($clusterInsights['averageRemoteLatency']) ? $clusterInsights['averageRemoteLatency'] : null)); ?></div>
                        <p class="small text-muted mb-0"><?php echo sprintf($L->g('monitoring-cluster-latency-hint'), isset($clusterInsights['slowSites']) ? (int)$clusterInsights['slowSites'] : 0); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="monitoring-cluster-widget border rounded-3 p-3 h-100">
                        <div class="small text-muted text-uppercase fw-semibold"><?php echo $L->g('monitoring-cluster-stress'); ?></div>
                        <div class="d-flex align-items-center gap-3 mt-2">
                            <div class="monitoring-stress-gauge flex-shrink-0" data-value="<?php echo isset($clusterInsights['stressScore']) ? (int)$clusterInsights['stressScore'] : 0; ?>" style="--value: <?php echo isset($clusterInsights['stressScore']) ? (int)$clusterInsights['stressScore'] : 0; ?>;"></div>
                            <div>
                                <div class="display-6 fw-semibold mb-0"><?php echo isset($clusterInsights['stressScore']) ? (int)$clusterInsights['stressScore'] . '%' : '--'; ?></div>
                                <span class="badge <?php echo monitoringStatusBadgeClass(isset($clusterInsights['stressStatus']) ? $clusterInsights['stressStatus'] : 'unknown'); ?>">
                                    <?php echo Sanitize::html(monitoringStatusLabel(isset($clusterInsights['stressStatus']) ? $clusterInsights['stressStatus'] : 'unknown', $L)); ?>
                                </span>
                            </div>
                        </div>
                        <p class="small text-muted mb-0 mt-2"><?php echo $L->g('monitoring-cluster-stress-hint'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('monitoring-alerts-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('monitoring-alerts-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($performanceAlerts)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($performanceAlerts as $alert): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
                                <div class="d-flex align-items-start gap-2">
                                    <span class="badge <?php echo monitoringStatusBadgeClass($alert['status']); ?>">
                                        <span class="bi <?php echo monitoringStatusIconClass($alert['status']); ?>"></span>
                                    </span>
                                    <div>
                                        <div class="fw-semibold"><?php echo Sanitize::html($alert['title']); ?></div>
                                        <?php if (!empty($alert['description'])): ?>
                                            <div class="small text-muted mt-1"><?php echo Sanitize::html($alert['description']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-success mb-0 d-flex align-items-center gap-2">
                    <span class="bi bi-check-circle"></span>
                    <span><?php echo $L->g('monitoring-alerts-empty'); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>monitoring.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">