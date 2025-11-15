<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$overviewCards = isset($overviewCards) && is_array($overviewCards) ? $overviewCards : array();
$severitySummary = isset($severitySummary) && is_array($severitySummary) ? $severitySummary : array();
$trendSeries = isset($trendSeries) && is_array($trendSeries) ? $trendSeries : array();
$systemErrors = isset($systemErrors) && is_array($systemErrors) ? $systemErrors : array();
$siteErrors = isset($siteErrors) && is_array($siteErrors) ? $siteErrors : array();
$httpErrors = isset($httpErrors) && is_array($httpErrors) ? $httpErrors : array();
$recentErrors = isset($recentErrors) && is_array($recentErrors) ? $recentErrors : array();
$errorAlerts = isset($errorAlerts) && is_array($errorAlerts) ? $errorAlerts : array();
$retentionInfo = isset($retentionInfo) && is_array($retentionInfo) ? $retentionInfo : array();
$alertChannels = isset($alertChannels) && is_array($alertChannels) ? $alertChannels : array();
$lastErrorAt = isset($lastErrorAt) ? $lastErrorAt : null;

if (!function_exists('logErrorSeverityBadgeClass')) {
    function logErrorSeverityBadgeClass($severity)
    {
        $tone = mgwLogErrorSeverityBadge($severity);
        switch ($tone) {
            case 'danger':
                return 'bg-danger-subtle text-danger';
            case 'warning':
                return 'bg-warning-subtle text-warning';
            case 'info':
                return 'bg-info-subtle text-info';
            default:
                return 'bg-secondary-subtle text-secondary';
        }
    }
}

if (!function_exists('logErrorSeverityIcon')) {
    function logErrorSeverityIcon($severity)
    {
        $severity = strtoupper((string)$severity);
        if (in_array($severity, array('ERROR', 'CRITICAL', 'FATAL'), true)) {
            return 'bi-x-octagon';
        }
        if (in_array($severity, array('WARNING', 'WARN'), true)) {
            return 'bi-exclamation-triangle';
        }
        if (in_array($severity, array('NOTICE', 'INFO'), true)) {
            return 'bi-info-circle';
        }
        return 'bi-dot';
    }
}

if (!function_exists('logErrorFormatAgo')) {
    function logErrorFormatAgo($timestamp)
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

if (!function_exists('logErrorTrendMax')) {
    function logErrorTrendMax($entries, $key)
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

<div class="log-error-page container-fluid px-0">
    <div class="d-flex flex-column flex-xxl-row align-items-xxl-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1 d-flex align-items-center gap-2">
                <span class="bi bi-bug"></span>
                <span><?php echo $L->g('log-error-title'); ?></span>
            </h2>
            <p class="text-muted small mb-0"><?php echo $L->g('log-error-subtitle'); ?></p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="#" class="btn btn-primary">
                <span class="bi bi-arrow-repeat"></span>
                <span class="ms-1"><?php echo $L->g('log-error-action-refresh'); ?></span>
            </a>
            <a href="#" class="btn btn-outline-secondary">
                <span class="bi bi-broom"></span>
                <span class="ms-1"><?php echo $L->g('log-error-action-clear'); ?></span>
            </a>
            <a href="#" class="btn btn-outline-primary">
                <span class="bi bi-download"></span>
                <span class="ms-1"><?php echo $L->g('log-error-action-export'); ?></span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end log-error-filter" action="#" method="get">
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label small text-uppercase text-muted"><?php echo $L->g('log-error-filter-keyword'); ?></label>
                    <input type="text" class="form-control" placeholder="PDOException">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label small text-uppercase text-muted"><?php echo $L->g('log-error-filter-file'); ?></label>
                    <input type="text" class="form-control" placeholder="kernel/helpers">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label small text-uppercase text-muted"><?php echo $L->g('log-error-filter-status'); ?></label>
                    <input type="text" class="form-control" placeholder="500">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label small text-uppercase text-muted"><?php echo $L->g('log-error-filter-severity'); ?></label>
                    <select class="form-select">
                        <option>ERROR</option>
                        <option>WARNING</option>
                        <option>NOTICE</option>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-grow-1"><?php echo $L->g('log-error-filter-search'); ?></button>
                    <button type="reset" class="btn btn-outline-secondary"><?php echo $L->g('log-error-filter-reset'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($overviewCards as $card): ?>
                    <div class="col-sm-6 col-xl-3">
                        <div class="log-error-overview border rounded-3 h-100 p-3">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold"><?php echo Sanitize::html($card['label']); ?></div>
                                    <div class="fs-3 fw-semibold mt-2"><?php echo Sanitize::html($card['value']); ?></div>
                                </div>
                                <?php if (isset($card['status'])): ?>
                                    <span class="badge <?php echo $card['status'] === 'fail' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'; ?>">
                                        <?php echo $card['status'] === 'fail' ? $L->g('log-error-status-critical') : $L->g('log-error-status-ok'); ?>
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
                        <h5 class="mb-0"><?php echo $L->g('log-error-trend-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('log-error-trend-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills gap-2 mb-3" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#trend24" type="button">24h</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#trend7" type="button">7d</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#trend30" type="button">30d</button></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="trend24">
                            <?php $series24 = isset($trendSeries['24h']) ? $trendSeries['24h'] : array(); ?>
                            <?php if (!empty($series24)): ?>
                                <?php $max24 = logErrorTrendMax($series24, 'errors'); ?>
                                <div class="log-error-trend-grid">
                                    <?php foreach ($series24 as $entry): ?>
                                        <div class="log-error-trend-bar">
                                            <div class="log-error-trend-label"><?php echo Sanitize::html(isset($entry['hour']) ? $entry['hour'] : '--'); ?>h</div>
                                            <?php $percent = $max24 > 0 ? min(100, ($entry['errors'] / $max24) * 100) : 0; ?>
                                            <div class="log-error-trend-meter" style="height: <?php echo number_format($percent, 2); ?>%;"></div>
                                            <div class="log-error-trend-value"><?php echo number_format($entry['errors']); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-light mb-0"><?php echo $L->g('log-error-trend-empty'); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="tab-pane fade" id="trend7">
                            <?php $series7 = isset($trendSeries['7d']) ? $trendSeries['7d'] : array(); ?>
                            <?php if (!empty($series7)): ?>
                                <div class="log-error-trend-line">
                                    <?php foreach ($series7 as $entry): ?>
                                        <div class="log-error-trend-point">
                                            <span class="badge bg-danger-subtle text-danger"><?php echo number_format($entry['errors']); ?></span>
                                            <span class="small text-muted"><?php echo Sanitize::html($entry['date']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-light mb-0"><?php echo $L->g('log-error-trend-empty'); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="tab-pane fade" id="trend30">
                            <?php $series30 = isset($trendSeries['30d']) ? $trendSeries['30d'] : array(); ?>
                            <?php if (!empty($series30)): ?>
                                <div class="log-error-trend-line">
                                    <?php foreach ($series30 as $entry): ?>
                                        <div class="log-error-trend-point">
                                            <span class="badge bg-warning-subtle text-warning"><?php echo number_format($entry['errors']); ?></span>
                                            <span class="small text-muted"><?php echo Sanitize::html($entry['week']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-light mb-0"><?php echo $L->g('log-error-trend-empty'); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('log-error-severity-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('log-error-severity-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($severitySummary)): ?>
                        <?php $totalSeverity = array_sum(array_map(function ($entry) { return isset($entry['count']) ? (int)$entry['count'] : 0; }, $severitySummary)); ?>
                        <ul class="list-unstyled log-error-severity">
                            <?php foreach ($severitySummary as $entry): ?>
                                <?php $percent = $totalSeverity > 0 ? ($entry['count'] / $totalSeverity) * 100 : 0; ?>
                                <li>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge <?php echo logErrorSeverityBadgeClass($entry['severity']); ?>">
                                                <span class="bi <?php echo logErrorSeverityIcon($entry['severity']); ?>"></span>
                                            </span>
                                            <span class="fw-semibold text-uppercase"><?php echo Sanitize::html($entry['severity']); ?></span>
                                        </div>
                                        <span class="fw-semibold"><?php echo number_format($entry['count']); ?></span>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo number_format($percent, 2); ?>%;" aria-valuenow="<?php echo number_format($percent, 2); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-light mb-0"><?php echo $L->g('log-error-severity-empty'); ?></div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white border-0 small text-muted d-flex align-items-center justify-content-between">
                    <span><?php echo $L->g('log-error-last-seen'); ?>: <?php echo mgwLogErrorFormatDateTime($lastErrorAt); ?></span>
                    <span class="badge bg-light text-dark border"><?php echo logErrorFormatAgo($lastErrorAt); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xxl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('log-error-system-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('log-error-system-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('log-error-table-time'); ?></th>
                                    <th><?php echo $L->g('log-error-table-severity'); ?></th>
                                    <th><?php echo $L->g('log-error-table-message'); ?></th>
                                    <th><?php echo $L->g('log-error-table-file'); ?></th>
                                    <th class="text-end"><?php echo $L->g('log-error-table-actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($systemErrors)): ?>
                                    <?php foreach ($systemErrors as $entry): ?>
                                        <tr>
                                            <td class="small text-muted">
                                                <div><?php echo mgwLogErrorFormatDateTime(isset($entry['time']) ? $entry['time'] : null); ?></div>
                                                <span class="badge bg-light text-dark border mt-1"><?php echo logErrorFormatAgo(isset($entry['time']) ? $entry['time'] : null); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo logErrorSeverityBadgeClass(isset($entry['severity']) ? $entry['severity'] : null); ?>">
                                                    <span class="bi <?php echo logErrorSeverityIcon(isset($entry['severity']) ? $entry['severity'] : null); ?> me-1"></span>
                                                    <?php echo Sanitize::html(mgwLogErrorSeverityLabel(isset($entry['severity']) ? $entry['severity'] : '', $L)); ?>
                                                </span>
                                            </td>
                                            <td class="small text-truncate" title="<?php echo Sanitize::html(isset($entry['message']) ? $entry['message'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['message']) ? $entry['message'] : '--'); ?></td>
                                            <td class="small text-muted">
                                                <?php if (!empty($entry['file'])): ?>
                                                    <div class="text-truncate" title="<?php echo Sanitize::html($entry['file']); ?>"><?php echo Sanitize::html($entry['file']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($entry['line'])): ?>
                                                    <div class="text-muted">L<?php echo (int)$entry['line']; ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#errorModal" data-stack="<?php echo Sanitize::html(json_encode(isset($entry['stack']) ? $entry['stack'] : array())); ?>" data-message="<?php echo Sanitize::html(isset($entry['message']) ? $entry['message'] : '--'); ?>">
                                                    <?php echo $L->g('log-error-action-detail'); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4"><?php echo $L->g('log-error-system-empty'); ?></td>
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
                        <h5 class="mb-0"><?php echo $L->g('log-error-site-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('log-error-site-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($siteErrors)): ?>
                        <div class="log-error-site-grid">
                            <?php foreach ($siteErrors as $site): ?>
                                <div class="log-error-site-card border rounded-3 p-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($site['label']); ?>"><?php echo Sanitize::html($site['label']); ?></div>
                                            <div class="small text-muted mt-1"><?php echo $L->g('log-error-site-total'); ?>: <span class="fw-semibold text-danger"><?php echo number_format(isset($site['errors']) ? $site['errors'] : 0); ?></span></div>
                                        </div>
                                        <span class="badge <?php echo (isset($site['status']) && $site['status'] === 'fail') ? 'bg-danger-subtle text-danger' : ((isset($site['status']) && $site['status'] === 'warn') ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary'); ?>"><?php echo strtoupper(isset($site['status']) ? $site['status'] : 'ok'); ?></span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-3 small text-muted">
                                        <span><?php echo $L->g('log-error-site-critical'); ?>:</span>
                                        <span class="badge bg-danger-subtle text-danger"><?php echo number_format(isset($site['critical']) ? $site['critical'] : 0); ?></span>
                                    </div>
                                    <?php if (!empty($site['recent'])): ?>
                                        <ul class="log-error-site-recent mt-3">
                                            <?php foreach ($site['recent'] as $recent): ?>
                                                <li>
                                                    <div class="text-truncate" title="<?php echo Sanitize::html($recent['message']); ?>"><?php echo Sanitize::html($recent['message']); ?></div>
                                                    <span class="badge bg-light text-dark border"><?php echo logErrorFormatAgo(isset($recent['time']) ? $recent['time'] : null); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    <div class="mt-3 small text-muted d-flex align-items-center justify-content-between">
                                        <span><?php echo $L->g('log-error-site-last'); ?>:</span>
                                        <span><?php echo mgwLogErrorFormatDateTime(isset($site['lastError']) ? $site['lastError'] : null); ?></span>
                                    </div>
                                    <div class="mt-3 d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary flex-grow-1" type="button"><?php echo $L->g('log-error-action-view-site'); ?></button>
                                        <button class="btn btn-sm btn-outline-secondary" type="button"><?php echo $L->g('log-error-action-export-site'); ?></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0"><?php echo $L->g('log-error-site-empty'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('log-error-http-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('log-error-http-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('log-error-table-time'); ?></th>
                            <th><?php echo $L->g('log-error-http-status'); ?></th>
                            <th><?php echo $L->g('log-error-http-source'); ?></th>
                            <th><?php echo $L->g('log-error-http-site'); ?></th>
                            <th><?php echo $L->g('log-error-table-message'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($httpErrors)): ?>
                            <?php foreach ($httpErrors as $entry): ?>
                                <tr>
                                    <td class="small text-muted"><?php echo mgwLogErrorFormatDateTime(isset($entry['time']) ? $entry['time'] : null); ?></td>
                                    <td><span class="badge <?php echo logErrorSeverityBadgeClass((isset($entry['status']) && (int)$entry['status'] >= 500) ? 'ERROR' : 'WARNING'); ?>"><?php echo Sanitize::html(isset($entry['status']) ? $entry['status'] : '--'); ?></span></td>
                                    <td class="small text-uppercase text-muted"><?php echo Sanitize::html(isset($entry['source']) ? $entry['source'] : '--'); ?></td>
                                    <td class="small text-muted"><?php echo Sanitize::html(isset($entry['site']) ? $entry['site'] : '--'); ?></td>
                                    <td class="small text-truncate" title="<?php echo Sanitize::html(isset($entry['message']) ? $entry['message'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['message']) ? $entry['message'] : '--'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4"><?php echo $L->g('log-error-http-empty'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xxl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('log-error-recent-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('log-error-recent-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('log-error-table-time'); ?></th>
                                    <th><?php echo $L->g('log-error-table-severity'); ?></th>
                                    <th><?php echo $L->g('log-error-table-message'); ?></th>
                                    <th><?php echo $L->g('log-error-table-file'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentErrors)): ?>
                                    <?php foreach ($recentErrors as $entry): ?>
                                        <tr>
                                            <td class="small text-muted"><?php echo mgwLogErrorFormatDateTime(isset($entry['time']) ? $entry['time'] : null); ?></td>
                                            <td><span class="badge <?php echo logErrorSeverityBadgeClass(isset($entry['severity']) ? $entry['severity'] : null); ?>"><?php echo Sanitize::html(mgwLogErrorSeverityLabel(isset($entry['severity']) ? $entry['severity'] : '', $L)); ?></span></td>
                                            <td class="small text-truncate" title="<?php echo Sanitize::html(isset($entry['message']) ? $entry['message'] : '--'); ?>"><?php echo Sanitize::html(isset($entry['message']) ? $entry['message'] : '--'); ?></td>
                                            <td class="small text-muted">
                                                <?php if (!empty($entry['file'])): ?>
                                                    <span class="text-truncate d-inline-block" style="max-width: 180px;" title="<?php echo Sanitize::html($entry['file']); ?>"><?php echo Sanitize::html($entry['file']); ?></span>
                                                <?php else: ?>
                                                    --
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4"><?php echo $L->g('log-error-recent-empty'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center small text-muted">
                    <span><?php echo $L->g('log-error-recent-hint'); ?></span>
                    <button class="btn btn-outline-secondary btn-sm" type="button"><?php echo $L->g('log-error-action-export'); ?></button>
                </div>
            </div>
        </div>
        <div class="col-xxl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('log-error-alerts-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('log-error-alerts-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($errorAlerts)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($errorAlerts as $alert): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="badge <?php echo isset($alert['status']) && $alert['status'] === 'fail' ? 'bg-danger-subtle text-danger' : (isset($alert['status']) && $alert['status'] === 'warn' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info'); ?>">
                                            <span class="bi <?php echo isset($alert['status']) && $alert['status'] === 'fail' ? 'bi-broadcast-pin' : 'bi-exclamation-circle'; ?>"></span>
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
                        <div class="alert alert-success mb-0 d-flex align-items-center gap-2">
                            <span class="bi bi-check-circle"></span>
                            <span><?php echo $L->g('log-error-alerts-empty'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white border-0 small text-muted">
                    <div><?php echo sprintf($L->g('log-error-retention-days'), isset($retentionInfo['days']) ? (int)$retentionInfo['days'] : 0); ?></div>
                    <div><?php echo $L->g('log-error-retention-clean'); ?>: <?php echo mgwLogErrorFormatDateTime(isset($retentionInfo['lastClean']) ? $retentionInfo['lastClean'] : null); ?></div>
                    <div class="mt-2 d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" type="button"><?php echo $L->g('log-error-action-retention'); ?></button>
                        <button class="btn btn-outline-danger btn-sm" type="button"><?php echo $L->g('log-error-action-clear'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('log-error-channels-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('log-error-channels-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($alertChannels)): ?>
                <div class="log-error-channel-grid">
                    <?php foreach ($alertChannels as $channel): ?>
                        <div class="log-error-channel border rounded-3 p-3">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <div class="text-uppercase small text-muted fw-semibold"><?php echo Sanitize::html(isset($channel['type']) ? $channel['type'] : '--'); ?></div>
                                    <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html(isset($channel['label']) ? $channel['label'] : '--'); ?>"><?php echo Sanitize::html(isset($channel['label']) ? $channel['label'] : '--'); ?></div>
                                </div>
                                <span class="badge <?php echo (isset($channel['status']) && $channel['status'] === 'active') ? 'bg-success-subtle text-success' : ((isset($channel['status']) && $channel['status'] === 'paused') ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary'); ?>"><?php echo strtoupper(isset($channel['status']) ? $channel['status'] : 'unknown'); ?></span>
                            </div>
                            <div class="mt-3 small text-muted d-flex gap-2">
                                <button class="btn btn-sm btn-outline-secondary" type="button"><?php echo $L->g('log-error-action-test'); ?></button>
                                <button class="btn btn-sm btn-outline-primary" type="button"><?php echo $L->g('log-error-action-edit'); ?></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0"><?php echo $L->g('log-error-channels-empty'); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorModalLabel"><?php echo $L->g('log-error-modal-title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <pre class="log-error-stack mb-0"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo $L->g('log-error-modal-close'); ?></button>
                <button type="button" class="btn btn-primary"><?php echo $L->g('log-error-modal-copy'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('errorModal');
        if (!modal) {
            return;
        }
        modal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) {
                return;
            }
            var stack = button.getAttribute('data-stack');
            var message = button.getAttribute('data-message');
            var pre = modal.querySelector('.log-error-stack');
            if (pre) {
                try {
                    var parsed = JSON.parse(stack || '[]');
                    if (Array.isArray(parsed) && parsed.length) {
                        pre.textContent = message + '\n' + parsed.join('\n');
                    } else {
                        pre.textContent = message || '';
                    }
                } catch (e) {
                    pre.textContent = message || '';
                }
            }
            var title = modal.querySelector('#errorModalLabel');
            if (title && message) {
                title.textContent = message;
            }
        });
    });
</script>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>log-error.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">
