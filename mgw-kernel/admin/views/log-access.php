<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$overviewCards = isset($overviewCards) && is_array($overviewCards) ? $overviewCards : array();
$trafficTrend = isset($trafficTrend) && is_array($trafficTrend) ? $trafficTrend : array();
$statusDistribution = isset($statusDistribution) && is_array($statusDistribution) ? $statusDistribution : array();
$sourceBreakdown = isset($sourceBreakdown) && is_array($sourceBreakdown) ? $sourceBreakdown : array();
$siteAccessMatrix = isset($siteAccessMatrix) && is_array($siteAccessMatrix) ? $siteAccessMatrix : array();
$realtimeLogs = isset($realtimeLogs) && is_array($realtimeLogs) ? $realtimeLogs : array();
$recentLogs = isset($recentLogs) && is_array($recentLogs) ? $recentLogs : array();
$suspiciousAlerts = isset($suspiciousAlerts) && is_array($suspiciousAlerts) ? $suspiciousAlerts : array();
$exportOptions = isset($exportOptions) && is_array($exportOptions) ? $exportOptions : array();
$logRetention = isset($logRetention) && is_array($logRetention) ? $logRetention : array();

if (!function_exists('logAccessStatusBadgeClass')) {
    function logAccessStatusBadgeClass($status)
    {
        $tone = mgwLogAccessStatusBadge($status);
        switch ($tone) {
            case 'success':
                return 'bg-success-subtle text-success';
            case 'info':
                return 'bg-info-subtle text-info';
            case 'warning':
                return 'bg-warning-subtle text-warning';
            case 'danger':
                return 'bg-danger-subtle text-danger';
            default:
                return 'bg-secondary-subtle text-secondary';
        }
    }
}

if (!function_exists('logAccessFormatTimeBadge')) {
    function logAccessFormatTimeBadge($timestamp)
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

if (!function_exists('logAccessTrendMax')) {
    function logAccessTrendMax($entries, $key)
    {
        $max = 0;
        foreach ($entries as $entry) {
            if (isset($entry[$key]) && is_numeric($entry[$key])) {
                $max = max($max, (float)$entry[$key]);
            }
        }
        return $max > 0 ? $max : 1;
    }
}
?>

<div class="log-access-page container-fluid px-0">
    <div class="d-flex flex-column flex-xxl-row align-items-xxl-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1 d-flex align-items-center gap-2">
                <span class="bi bi-activity"></span>
                <span><?php echo $L->g('log-access-title'); ?></span>
            </h2>
            <p class="text-muted small mb-0"><?php echo $L->g('log-access-subtitle'); ?></p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="#" class="btn btn-primary">
                <span class="bi bi-arrow-clockwise"></span>
                <span class="ms-1"><?php echo $L->g('log-access-action-refresh'); ?></span>
            </a>
            <a href="#" class="btn btn-outline-secondary">
                <span class="bi bi-download"></span>
                <span class="ms-1"><?php echo $L->g('log-access-action-export'); ?></span>
            </a>
            <a href="#" class="btn btn-outline-danger">
                <span class="bi bi-trash"></span>
                <span class="ms-1"><?php echo $L->g('log-access-action-clear'); ?></span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end log-access-filter" action="#" method="get">
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label small text-uppercase text-muted">IP</label>
                    <input type="text" class="form-control" placeholder="203.0.113.*">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label small text-uppercase text-muted"><?php echo $L->g('log-access-filter-url'); ?></label>
                    <input type="text" class="form-control" placeholder="/admin">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label small text-uppercase text-muted"><?php echo $L->g('log-access-filter-status'); ?></label>
                    <input type="text" class="form-control" placeholder="200">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label small text-uppercase text-muted"><?php echo $L->g('log-access-filter-site'); ?></label>
                    <input type="text" class="form-control" placeholder="example.com">
                </div>
                <div class="col-sm-6 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-grow-1"><?php echo $L->g('log-access-filter-search'); ?></button>
                    <button type="reset" class="btn btn-outline-secondary"><?php echo $L->g('log-access-filter-reset'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($overviewCards as $card): ?>
                    <div class="col-sm-6 col-xl-3">
                        <div class="log-access-overview border rounded-3 h-100 p-3">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold"><?php echo Sanitize::html($card['label']); ?></div>
                                    <div class="fs-3 fw-semibold mt-2" title="<?php echo Sanitize::html($card['value']); ?>"><?php echo Sanitize::html($card['value']); ?></div>
                                </div>
                                <?php if (isset($card['status'])): ?>
                                    <span class="badge <?php echo $card['status'] === 'warn' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success'; ?>">
                                        <?php echo $card['status'] === 'warn' ? $L->g('log-access-status-warn') : $L->g('log-access-status-ok'); ?>
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
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('log-access-trend-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('log-access-trend-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($trafficTrend)): ?>
                        <?php $maxPv = logAccessTrendMax($trafficTrend, 'pv'); ?>
                        <div class="log-access-trend-grid">
                            <?php foreach ($trafficTrend as $entry): ?>
                                <div class="log-access-trend-day">
                                    <div class="d-flex align-items-center justify-content-between small text-muted">
                                        <span><?php echo Sanitize::html($entry['date']); ?></span>
                                        <span class="fw-semibold text-primary">PV <?php echo number_format(isset($entry['pv']) ? $entry['pv'] : 0); ?></span>
                                    </div>
                                    <div class="log-access-trend-bar">
                                        <?php $pvPercent = min(100, (isset($entry['pv']) && $maxPv > 0) ? ($entry['pv'] / $maxPv) * 100 : 0); ?>
                                        <div class="pv" style="width: <?php echo number_format($pvPercent, 2); ?>%;"></div>
                                        <?php if (isset($entry['uv'])): ?>
                                            <?php $uvPercent = $maxPv > 0 ? min(100, ($entry['uv'] / $maxPv) * 100) : 0; ?>
                                            <div class="uv" style="width: <?php echo number_format($uvPercent, 2); ?>%;"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted">UV <?php echo isset($entry['uv']) ? number_format($entry['uv']) : '--'; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0"><?php echo $L->g('log-access-trend-empty'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xxl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('log-access-pie-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('log-access-pie-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <h6 class="text-muted small text-uppercase mb-3"><?php echo $L->g('log-access-status-title'); ?></h6>
                            <?php if (!empty($statusDistribution)): ?>
                                <?php $totalStatus = array_sum(array_map(function ($entry) { return isset($entry['count']) ? (int)$entry['count'] : 0; }, $statusDistribution)); ?>
                                <ul class="list-unstyled log-access-ratio">
                                    <?php foreach ($statusDistribution as $entry): ?>
                                        <?php $percent = $totalStatus > 0 ? ($entry['count'] / $totalStatus) * 100 : 0; ?>
                                        <li>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="badge <?php echo logAccessStatusBadgeClass($entry['status']); ?>">HTTP <?php echo Sanitize::html($entry['status']); ?></span>
                                                <span class="fw-semibold"><?php echo number_format($entry['count']); ?></span>
                                            </div>
                                            <div class="progress mt-2" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo number_format($percent, 2); ?>%" aria-valuenow="<?php echo number_format($percent, 2); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="alert alert-light mb-0"><?php echo $L->g('log-access-status-empty'); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-sm-6">
                            <h6 class="text-muted small text-uppercase mb-3"><?php echo $L->g('log-access-source-title'); ?></h6>
                            <?php if (!empty($sourceBreakdown)): ?>
                                <?php $totalSource = array_sum(array_map(function ($entry) { return isset($entry['count']) ? (int)$entry['count'] : 0; }, $sourceBreakdown)); ?>
                                <ul class="list-unstyled log-access-ratio">
                                    <?php foreach ($sourceBreakdown as $entry): ?>
                                        <?php $percent = $totalSource > 0 ? ($entry['count'] / $totalSource) * 100 : 0; ?>
                                        <li>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="fw-semibold text-truncate" title="<?php echo Sanitize::html($entry['source']); ?>"><?php echo Sanitize::html($entry['source']); ?></span>
                                                <span class="fw-semibold"><?php echo number_format($entry['count']); ?></span>
                                            </div>
                                            <div class="progress mt-2" style="height: 6px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo number_format($percent, 2); ?>%" aria-valuenow="<?php echo number_format($percent, 2); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="alert alert-light mb-0"><?php echo $L->g('log-access-source-empty'); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('log-access-realtime-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('log-access-realtime-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th><?php echo $L->g('log-access-table-time'); ?></th>
                            <th><?php echo $L->g('log-access-table-site'); ?></th>
                            <th><?php echo $L->g('log-access-table-ip'); ?></th>
                            <th><?php echo $L->g('log-access-table-method'); ?></th>
                            <th><?php echo $L->g('log-access-table-url'); ?></th>
                            <th><?php echo $L->g('log-access-table-status'); ?></th>
                            <th><?php echo $L->g('log-access-table-referer'); ?></th>
                            <th><?php echo $L->g('log-access-table-ua'); ?></th>
                            <th class="text-end"><?php echo $L->g('log-access-table-duration'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($realtimeLogs)): ?>
                            <?php foreach ($realtimeLogs as $index => $entry): ?>
                                <tr>
                                    <td class="text-muted small">#<?php echo $index + 1; ?></td>
                                    <td class="small text-muted">
                                        <div><?php echo mgwLogAccessFormatDateTime(isset($entry['time']) ? $entry['time'] : null); ?></div>
                                        <span class="badge bg-light text-dark border mt-1"><?php echo logAccessFormatTimeBadge(isset($entry['time']) ? $entry['time'] : null); ?></span>
                                    </td>
                                    <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html((string)$entry['site']); ?>"><?php echo Sanitize::html(isset($entry['site']) ? $entry['site'] : '--'); ?></td>
                                    <td class="text-monospace small">&ZeroWidthSpace;<?php echo Sanitize::html(isset($entry['ip']) ? $entry['ip'] : '--'); ?></td>
                                    <td class="fw-semibold small text-uppercase"><?php echo Sanitize::html(isset($entry['method']) ? $entry['method'] : '--'); ?></td>
                                    <td class="small text-truncate" title="<?php echo Sanitize::html(isset($entry['url']) ? $entry['url'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['url']) ? $entry['url'] : '--'); ?></td>
                                    <td>
                                        <span class="badge <?php echo logAccessStatusBadgeClass(isset($entry['status']) ? $entry['status'] : null); ?>"><?php echo Sanitize::html(isset($entry['status']) ? $entry['status'] : '--'); ?></span>
                                    </td>
                                    <td class="small text-truncate" title="<?php echo Sanitize::html(isset($entry['referer']) ? $entry['referer'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['referer']) ? $entry['referer'] : '--'); ?></td>
                                    <td class="small text-muted" title="<?php echo Sanitize::html(isset($entry['ua']) ? $entry['ua'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['ua']) ? $entry['ua'] : '--'); ?></td>
                                    <td class="text-end small fw-semibold"><?php echo mgwLogAccessFormatDuration(isset($entry['duration']) ? $entry['duration'] : null); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4"><?php echo $L->g('log-access-realtime-empty'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 d-flex align-items-center justify-content-between">
            <span class="small text-muted"><?php echo $L->g('log-access-realtime-hint'); ?></span>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo $L->g('log-access-export'); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php foreach ($exportOptions as $option): ?>
                        <li><a class="dropdown-item" href="#"><?php echo Sanitize::html($option['label']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xxl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('log-access-table-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('log-access-table-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('log-access-table-site'); ?></th>
                                    <th><?php echo $L->g('log-access-table-requests'); ?></th>
                                    <th><?php echo $L->g('log-access-table-visitors'); ?></th>
                                    <th><?php echo $L->g('log-access-table-bandwidth'); ?></th>
                                    <th><?php echo $L->g('log-access-table-suspicious'); ?></th>
                                    <th><?php echo $L->g('log-access-table-last'); ?></th>
                                    <th class="text-end"><?php echo $L->g('log-access-table-ops'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($siteAccessMatrix)): ?>
                                    <?php foreach ($siteAccessMatrix as $entry): ?>
                                        <tr>
                                            <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html($entry['label']); ?>"><?php echo Sanitize::html($entry['label']); ?></td>
                                            <td class="fw-semibold text-primary"><?php echo number_format(isset($entry['requests']) ? $entry['requests'] : 0); ?></td>
                                            <td class="text-muted small"><?php echo number_format(isset($entry['visitors']) ? $entry['visitors'] : 0); ?></td>
                                            <td class="small text-muted"><?php echo Sanitize::html(isset($entry['bandwidth']) ? $entry['bandwidth'] : '--'); ?></td>
                                            <td>
                                                <span class="badge <?php echo (isset($entry['suspicious']) && $entry['suspicious'] > 0) ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary'; ?>"><?php echo number_format(isset($entry['suspicious']) ? $entry['suspicious'] : 0); ?></span>
                                            </td>
                                            <td class="small text-muted">
                                                <div><?php echo mgwLogAccessFormatDateTime(isset($entry['lastRequest']) ? $entry['lastRequest'] : null); ?></div>
                                                <span class="badge bg-light text-dark border mt-1"><?php echo logAccessFormatTimeBadge(isset($entry['lastRequest']) ? $entry['lastRequest'] : null); ?></span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-outline-primary" type="button" title="<?php echo Sanitize::html($L->g('log-access-action-see')); ?>"><span class="bi bi-journal-text"></span></button>
                                                    <button class="btn btn-outline-secondary" type="button" title="<?php echo Sanitize::html($L->g('log-access-action-export-site')); ?>"><span class="bi bi-download"></span></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4"><?php echo $L->g('log-access-table-empty'); ?></td>
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
                        <h5 class="mb-0"><?php echo $L->g('log-access-alerts-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('log-access-alerts-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($suspiciousAlerts)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($suspiciousAlerts as $alert): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="badge <?php echo isset($alert['status']) && $alert['status'] === 'fail' ? 'bg-danger-subtle text-danger' : (isset($alert['status']) && $alert['status'] === 'warn' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info'); ?>">
                                            <span class="bi <?php echo isset($alert['status']) && $alert['status'] === 'warn' ? 'bi-shield-exclamation' : (isset($alert['status']) && $alert['status'] === 'fail' ? 'bi-x-octagon' : 'bi-search'); ?>"></span>
                                        </span>
                                        <div>
                                            <div class="fw-semibold"><?php echo Sanitize::html(isset($alert['title']) ? $alert['title'] : '--'); ?></div>
                                            <?php if (!empty($alert['description'])): ?>
                                                <div class="small text-muted mt-1"><?php echo Sanitize::html($alert['description']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success mb-0">
                            <span class="bi bi-check-circle me-2"></span><?php echo $L->g('log-access-alerts-empty'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white border-0 small text-muted">
                    <?php if (!empty($logRetention)): ?>
                        <div><?php echo sprintf($L->g('log-access-retention-days'), isset($logRetention['days']) ? (int)$logRetention['days'] : 0); ?></div>
                        <div><?php echo $L->g('log-access-retention-clean'); ?>: <?php echo mgwLogAccessFormatDateTime(isset($logRetention['lastClean']) ? $logRetention['lastClean'] : null); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('log-access-history-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('log-access-history-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('log-access-table-time'); ?></th>
                            <th><?php echo $L->g('log-access-table-site'); ?></th>
                            <th><?php echo $L->g('log-access-table-ip'); ?></th>
                            <th><?php echo $L->g('log-access-table-url'); ?></th>
                            <th><?php echo $L->g('log-access-table-status'); ?></th>
                            <th><?php echo $L->g('log-access-table-referer'); ?></th>
                            <th class="text-end"><?php echo $L->g('log-access-table-duration'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentLogs)): ?>
                            <?php foreach ($recentLogs as $entry): ?>
                                <tr>
                                    <td class="small text-muted"><?php echo mgwLogAccessFormatDateTime(isset($entry['time']) ? $entry['time'] : null); ?></td>
                                    <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html(isset($entry['site']) ? $entry['site'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['site']) ? $entry['site'] : '--'); ?></td>
                                    <td class="text-monospace small">&ZeroWidthSpace;<?php echo Sanitize::html(isset($entry['ip']) ? $entry['ip'] : '--'); ?></td>
                                    <td class="small text-truncate" title="<?php echo Sanitize::html(isset($entry['url']) ? $entry['url'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['url']) ? $entry['url'] : '--'); ?></td>
                                    <td>
                                        <span class="badge <?php echo logAccessStatusBadgeClass(isset($entry['status']) ? $entry['status'] : null); ?>"><?php echo Sanitize::html(isset($entry['status']) ? $entry['status'] : '--'); ?></span>
                                    </td>
                                    <td class="small text-truncate" title="<?php echo Sanitize::html(isset($entry['referer']) ? $entry['referer'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['referer']) ? $entry['referer'] : '--'); ?></td>
                                    <td class="text-end small fw-semibold"><?php echo mgwLogAccessFormatDuration(isset($entry['duration']) ? $entry['duration'] : null); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4"><?php echo $L->g('log-access-history-empty'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2">
            <div class="small text-muted"><?php echo $L->g('log-access-history-hint'); ?></div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" type="button"><?php echo $L->g('log-access-action-export'); ?></button>
                <button class="btn btn-outline-secondary btn-sm" type="button"><?php echo $L->g('log-access-action-retention'); ?></button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>log-access.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">
