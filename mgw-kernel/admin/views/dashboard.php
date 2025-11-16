<?php defined('MAIGEWAN') or die('Maigewan CMS.');

// -----------------------------------------------------------------------------
// Dataset guards
// -----------------------------------------------------------------------------
$overviewDefaults = array(
    'totalGroups' => 0,
    'activeGroups' => 0,
    'abnormalGroups' => 0,
    'pendingGroups' => 0,
    'totalSites' => 0,
    'activeSites' => 0,
    'abnormalSites' => 0,
    'pendingSites' => 0
);
$overviewSummary = isset($dashboardOverviewSummary) && is_array($dashboardOverviewSummary) ? $dashboardOverviewSummary : array();
$overviewSummary = array_merge($overviewDefaults, $overviewSummary);

$deltaDefaults = array(
    'totalDelta' => 0.0,
    'activeDelta' => 0.0,
    'abnormalDelta' => 0.0,
    'pendingDelta' => 0.0
);
$overviewDeltas = isset($dashboardOverviewDeltas) && is_array($dashboardOverviewDeltas) ? $dashboardOverviewDeltas : array();
$overviewDeltas = array_merge($deltaDefaults, $overviewDeltas);

$httpsDefaults = array(
    'valid' => 0,
    'expiring' => 0,
    'invalid' => 0,
    'trend' => array()
);
$httpsSummary = isset($dashboardHttpsSummary) && is_array($dashboardHttpsSummary) ? $dashboardHttpsSummary : array();
$httpsSummary = array_merge($httpsDefaults, $httpsSummary);
$httpsTrend = isset($httpsSummary['trend']) && is_array($httpsSummary['trend']) ? $httpsSummary['trend'] : array();
$httpsTrendJson = Sanitize::html(json_encode($httpsTrend, JSON_UNESCAPED_UNICODE) ?: '[]');

$spiderDefaults = array(
    'total' => 0,
    'last24h' => 0,
    'activeBots' => 0,
    'topSources' => array(),
    'trend' => array()
);
$spiderSummary = isset($dashboardSpiderSummary) && is_array($dashboardSpiderSummary) ? $dashboardSpiderSummary : array();
$spiderSummary = array_merge($spiderDefaults, $spiderSummary);
$spiderTopSources = isset($spiderSummary['topSources']) && is_array($spiderSummary['topSources']) ? $spiderSummary['topSources'] : array();
$spiderTrend = isset($spiderSummary['trend']) && is_array($spiderSummary['trend']) ? $spiderSummary['trend'] : array();
$spiderTrendJson = Sanitize::html(json_encode($spiderTrend, JSON_UNESCAPED_UNICODE) ?: '[]');
$spiderLatestRecords = isset($dashboardSpiderLatest) && is_array($dashboardSpiderLatest) ? $dashboardSpiderLatest : array();

$tasksSummaryDefaults = array(
    'date' => '',
    'success' => 0,
    'failed' => 0,
    'total' => 0,
    'successRate' => 0.0,
    'failureRate' => 0.0
);
$tasksSummary = isset($dashboardTasksSummary) && is_array($dashboardTasksSummary) ? $dashboardTasksSummary : array();
$tasksSummary = array_merge($tasksSummaryDefaults, $tasksSummary);
if ((int)$tasksSummary['total'] === 0) {
    $tasksSummary['total'] = (int)$tasksSummary['success'] + (int)$tasksSummary['failed'];
}
$tasksBreakdown = array(
    array('label' => 'success', 'value' => (int)$tasksSummary['success']),
    array('label' => 'failed', 'value' => (int)$tasksSummary['failed'])
);
$tasksBreakdownJson = Sanitize::html(json_encode($tasksBreakdown, JSON_UNESCAPED_UNICODE) ?: '[]');

$taskStatusDefaults = array(
    'counts' => array(
        'success' => 0,
        'warning' => 0,
        'failed' => 0,
        'running' => 0,
        'pending' => 0,
        'other' => 0
    ),
    'total' => 0
);
$taskStatusBreakdown = isset($dashboardTaskStatusBreakdown) && is_array($dashboardTaskStatusBreakdown) ? $dashboardTaskStatusBreakdown : array();
$taskStatusBreakdown = array_merge($taskStatusDefaults, $taskStatusBreakdown);
$taskStatusCounts = isset($taskStatusBreakdown['counts']) && is_array($taskStatusBreakdown['counts']) ? array_merge($taskStatusDefaults['counts'], $taskStatusBreakdown['counts']) : $taskStatusDefaults['counts'];
$taskStatusTotal = isset($taskStatusBreakdown['total']) && (int)$taskStatusBreakdown['total'] > 0 ? (int)$taskStatusBreakdown['total'] : array_sum($taskStatusCounts);

$siteStatusDefaults = array(
    'buckets' => array(
        'active' => 0,
        'abnormal' => 0,
        'pending' => 0
    ),
    'total' => 0
);
$siteStatusBreakdown = isset($dashboardSiteStatusBreakdown) && is_array($dashboardSiteStatusBreakdown) ? $dashboardSiteStatusBreakdown : array();
$siteStatusBreakdown = array_merge($siteStatusDefaults, $siteStatusBreakdown);
$siteStatusBuckets = isset($siteStatusBreakdown['buckets']) && is_array($siteStatusBreakdown['buckets']) ? array_merge($siteStatusDefaults['buckets'], $siteStatusBreakdown['buckets']) : $siteStatusDefaults['buckets'];
$siteStatusTotal = isset($siteStatusBreakdown['total']) && (int)$siteStatusBreakdown['total'] > 0 ? (int)$siteStatusBreakdown['total'] : array_sum($siteStatusBuckets);

$latestSites = isset($dashboardLatestSites) && is_array($dashboardLatestSites) ? $dashboardLatestSites : array();
$runningTasks = isset($dashboardRunningTasks) && is_array($dashboardRunningTasks) ? $dashboardRunningTasks : array();
$recommendations = isset($dashboardRecommendations) && is_array($dashboardRecommendations) ? $dashboardRecommendations : array();
$systemAlerts = isset($dashboardSystemAlerts) && is_array($dashboardSystemAlerts) ? $dashboardSystemAlerts : array();

$authorizationDefaults = array(
    'license' => '',
    'status' => '',
    'supportLevel' => '',
    'expiresAt' => '',
    'lastChecked' => '',
    'authorizedTo' => array()
);
$authorization = isset($dashboardAuthorization) && is_array($dashboardAuthorization) ? $dashboardAuthorization : array();
$authorization = array_merge($authorizationDefaults, $authorization);

$metadataDefaults = array(
    'generatedAt' => '',
    'source' => '',
    'notes' => ''
);
$dashboardMetadata = isset($dashboardMetadata) && is_array($dashboardMetadata) ? $dashboardMetadata : array();
$metadata = array_merge($metadataDefaults, $dashboardMetadata);

$batchDefaults = array(
    'latestBatch' => '',
    'totalGroups' => 0,
    'activeGroups' => 0,
    'abnormalGroups' => 0,
    'pendingGroups' => 0
);
$dashboardBatchOverview = isset($dashboardBatchOverview) && is_array($dashboardBatchOverview) ? $dashboardBatchOverview : array();
$dashboardBatchOverview = array_merge($batchDefaults, $dashboardBatchOverview);
$latestBatch = (string)$dashboardBatchOverview['latestBatch'];

// -----------------------------------------------------------------------------
// User context
// -----------------------------------------------------------------------------
$username = $login->username();
$user = new User($username);
$displayName = '';
if ($user->nickname()) {
    $displayName = $user->nickname();
} elseif ($user->firstName()) {
    $displayName = $user->firstName();
} elseif ($user->username()) {
    $displayName = $user->username();
} else {
    $displayName = $username;
}

// -----------------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------------
if (!function_exists('mgwDashboardTranslate')) {
    function mgwDashboardTranslate($key, $fallback = '')
    {
        global $L;
        if (!is_object($L) || !method_exists($L, 'g')) {
            return $fallback;
        }

        $value = $L->g($key);
        if (!is_string($value) || $value === '' || $value === $key) {
            return $fallback;
        }

        return $value;
    }
}

if (!function_exists('mgwDashboardStatusBadgeClass')) {
    function mgwDashboardStatusBadgeClass($status)
    {
        $status = strtolower((string)$status);
        if (in_array($status, array('online', 'active', 'success', 'running', 'normal'), true)) {
            return 'badge rounded-pill text-bg-success';
        }
        if (in_array($status, array('warning', 'maintenance', 'pending', 'queued', 'standby'), true)) {
            return 'badge rounded-pill text-bg-warning text-dark';
        }
        if (in_array($status, array('error', 'failed', 'offline', 'inactive', 'critical'), true)) {
            return 'badge rounded-pill text-bg-danger';
        }
        return 'badge rounded-pill text-bg-secondary';
    }
}

if (!function_exists('mgwDashboardRecommendationBadgeClass')) {
    function mgwDashboardRecommendationBadgeClass($severity)
    {
        $severity = strtolower((string)$severity);
        if ($severity === 'high' || $severity === 'critical') {
            return 'badge rounded-pill text-bg-danger';
        }
        if ($severity === 'medium') {
            return 'badge rounded-pill text-bg-warning text-dark';
        }
        if ($severity === 'low') {
            return 'badge rounded-pill text-bg-info';
        }
        return 'badge rounded-pill text-bg-secondary';
    }
}

if (!function_exists('mgwDashboardAlertBadgeClass')) {
    function mgwDashboardAlertBadgeClass($level)
    {
        $level = strtolower((string)$level);
        if (in_array($level, array('critical', 'severe', 'high'), true)) {
            return 'badge rounded-pill text-bg-danger';
        }
        if (in_array($level, array('warning', 'medium'), true)) {
            return 'badge rounded-pill text-bg-warning text-dark';
        }
        return 'badge rounded-pill text-bg-info';
    }
}

if (!function_exists('mgwDashboardDeltaClass')) {
    function mgwDashboardDeltaClass($value)
    {
        $value = (float)$value;
        if ($value > 0) {
            return 'text-success';
        }
        if ($value < 0) {
            return 'text-danger';
        }
        return 'text-muted';
    }
}

if (!function_exists('mgwDashboardFormatDelta')) {
    function mgwDashboardFormatDelta($value)
    {
        $value = (float)$value;
        if (abs($value) < 0.05) {
            return '0%';
        }

        $formatted = number_format($value, 1);
        if ($value > 0) {
            $formatted = '+' . $formatted;
        }

        return $formatted . '%';
    }
}

// -----------------------------------------------------------------------------
// Derived datasets
// -----------------------------------------------------------------------------
$kpiCards = array(
    array(
        'icon' => 'bi-diagram-3',
    'title' => mgwDashboardTranslate('dashboard-kpi-total-sites'),
        'value' => number_format((int)$overviewSummary['totalSites']),
        'delta' => (float)$overviewDeltas['totalDelta'],
    'description' => mgwDashboardTranslate('dashboard-kpi-total-sites-desc'),
        'link' => HTML_PATH_ADMIN_ROOT . 'site-list',
    'linkLabel' => mgwDashboardTranslate('dashboard-see-all-groups')
    ),
    array(
        'icon' => 'bi-activity',
    'title' => mgwDashboardTranslate('dashboard-kpi-active-sites'),
        'value' => number_format((int)$overviewSummary['activeSites']),
        'delta' => (float)$overviewDeltas['activeDelta'],
    'description' => mgwDashboardTranslate('dashboard-kpi-active-sites-desc'),
        'link' => HTML_PATH_ADMIN_ROOT . 'site-list',
    'linkLabel' => mgwDashboardTranslate('dashboard-filter-active')
    ),
    array(
        'icon' => 'bi-exclamation-triangle',
    'title' => mgwDashboardTranslate('dashboard-kpi-abnormal-sites'),
        'value' => number_format((int)$overviewSummary['abnormalSites']),
        'delta' => (float)$overviewDeltas['abnormalDelta'],
    'description' => mgwDashboardTranslate('dashboard-kpi-abnormal-sites-desc'),
        'link' => HTML_PATH_ADMIN_ROOT . 'site-list',
    'linkLabel' => mgwDashboardTranslate('dashboard-filter-abnormal')
    ),
    array(
        'icon' => 'bi-hourglass-split',
    'title' => mgwDashboardTranslate('dashboard-kpi-pending-sites'),
        'value' => number_format((int)$overviewSummary['pendingSites']),
        'delta' => (float)$overviewDeltas['pendingDelta'],
    'description' => mgwDashboardTranslate('dashboard-kpi-pending-sites-desc'),
        'link' => HTML_PATH_ADMIN_ROOT . 'site-list',
    'linkLabel' => mgwDashboardTranslate('dashboard-filter-pending')
    )
);

$taskStatusDataset = array();
foreach ($taskStatusCounts as $statusKey => $countValue) {
    $taskStatusDataset[] = array(
        'label' => mgwDashboardTranslate('dashboard-task-status-' . $statusKey, ucfirst((string)$statusKey)),
        'value' => (int)$countValue
    );
}
$taskStatusJson = Sanitize::html(json_encode($taskStatusDataset, JSON_UNESCAPED_UNICODE) ?: '[]');

$siteStatusDataset = array();
foreach ($siteStatusBuckets as $bucketKey => $bucketValue) {
    $siteStatusDataset[] = array(
        'label' => mgwDashboardTranslate('dashboard-site-status-' . $bucketKey, ucfirst((string)$bucketKey)),
        'value' => (int)$bucketValue
    );
}
$siteStatusJson = Sanitize::html(json_encode($siteStatusDataset, JSON_UNESCAPED_UNICODE) ?: '[]');

$totalCertificates = (int)$httpsSummary['valid'] + (int)$httpsSummary['expiring'] + (int)$httpsSummary['invalid'];
$certificateBase = $totalCertificates > 0 ? $totalCertificates : 1;
$httpsValidPercent = $totalCertificates > 0 ? round(((int)$httpsSummary['valid'] / $certificateBase) * 100) : 0;
$httpsExpiringPercent = $totalCertificates > 0 ? round(((int)$httpsSummary['expiring'] / $certificateBase) * 100) : 0;
$httpsInvalidPercent = $totalCertificates > 0 ? round(((int)$httpsSummary['invalid'] / $certificateBase) * 100) : 0;
?>
<div id="dashboard" class="dashboard-command-center container-fluid px-3 px-md-4 pb-5">
    <div class="row gy-4">
        <div class="col-12">
            <div class="row g-3 align-items-stretch">
                <div class="col-xl-8">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                            <div>
                                <h2 id="dashboard-hello"
                                    class="mb-2 fw-semibold"
                                    data-username="<?php echo Sanitize::html($displayName); ?>"
                                    data-morning="<?php echo Sanitize::html($L->g('good-morning')); ?>"
                                    data-afternoon="<?php echo Sanitize::html($L->g('good-afternoon')); ?>"
                                    data-evening="<?php echo Sanitize::html($L->g('good-evening')); ?>"
                                    data-night="<?php echo Sanitize::html($L->g('good-night')); ?>">
                                    <span class="bi bi-sun"></span>
                                    <span><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-command-center-title')); ?></span>
                                </h2>
                                <p class="text-muted mb-0">
                                    <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-command-center-subtitle')); ?>
                                </p>
                            </div>
                            <div class="text-lg-end small text-muted">
                                <?php if ($metadata['generatedAt'] !== ''): ?>
                                    <div>
                                        <span class="bi bi-clock-history me-1"></span>
                                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-data-snapshot')); ?>:
                                        <strong><?php echo Sanitize::html($metadata['generatedAt']); ?></strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($metadata['source'] !== ''): ?>
                                    <div>
                                        <span class="bi bi-diagram-3 me-1"></span>
                                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-data-source')); ?>:
                                        <strong><?php echo Sanitize::html($metadata['source']); ?></strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($latestBatch !== ''): ?>
                                    <div>
                                        <span class="bi bi-calendar-event me-1"></span>
                                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-latest-batch')); ?>:
                                        <strong><?php echo Sanitize::html($latestBatch); ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header border-0 bg-transparent pb-0">
                            <h3 class="h6 mb-0">
                                <span class="bi bi-command me-1"></span>
                                <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-quick-actions')); ?>
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-quick-actions-help')); ?>
                            </p>
                <select id="jsclippy"
                    class="form-select"
                    name="state"
                    data-placeholder="<?php echo Sanitize::html(mgwDashboardTranslate('dashboard-quick-search-placeholder')); ?>"
                    data-view-label="<?php echo Sanitize::html(mgwDashboardTranslate('dashboard-quick-search-view')); ?>"
                    data-edit-label="<?php echo Sanitize::html(mgwDashboardTranslate('dashboard-quick-search-edit')); ?>">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-0 bg-transparent d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h3 class="h5 mb-0"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-operations-overview')); ?></h3>
                        <div class="text-muted small">
                            <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-operations-overview-subtitle')); ?>
                        </div>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo HTML_PATH_ADMIN_ROOT . 'site-list'; ?>">
                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-see-all-groups')); ?>
                    </a>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-3">
                        <?php foreach ($kpiCards as $card): ?>
                            <div class="col-sm-6 col-xl-3">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="fw-semibold text-nowrap"><?php echo Sanitize::html($card['title']); ?></span>
                                        <span class="text-primary"><span class="bi <?php echo Sanitize::html($card['icon']); ?>"></span></span>
                                    </div>
                                    <div class="display-6 fw-semibold mb-2"><?php echo Sanitize::html($card['value']); ?></div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted small"><?php echo Sanitize::html($card['description']); ?></span>
                                        <span class="small <?php echo Sanitize::html(mgwDashboardDeltaClass($card['delta'])); ?>">
                                            <span class="bi <?php echo Sanitize::html($card['delta'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right'); ?>"></span>
                                            <?php echo Sanitize::html(mgwDashboardFormatDelta($card['delta'])); ?>
                                        </span>
                                    </div>
                                    <div class="mt-3">
                                        <a class="small text-decoration-none" href="<?php echo Sanitize::html($card['link']); ?>">
                                            <?php echo Sanitize::html($card['linkLabel']); ?>
                                            <span class="bi bi-arrow-right ms-1"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 bg-transparent d-flex align-items-center justify-content-between">
                    <h3 class="h6 mb-0"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-task-automation')); ?></h3>
                    <a class="small text-decoration-none" href="<?php echo HTML_PATH_ADMIN_ROOT . 'scheduled-tasks'; ?>">
                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-view-tasks')); ?>
                        <span class="bi bi-arrow-right ms-1"></span>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted small mb-1">
                                    <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-last-run')); ?>:
                                    <strong><?php echo $tasksSummary['date'] !== '' ? Sanitize::html($tasksSummary['date']) : Sanitize::html(mgwDashboardTranslate('dashboard-no-data')); ?></strong>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <div class="fw-semibold text-success"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-tasks-success')); ?></div>
                                        <div class="display-6 fw-semibold"><?php echo Sanitize::html(number_format((int)$tasksSummary['success'])); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold text-danger"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-tasks-failed')); ?></div>
                                        <div class="display-6 fw-semibold"><?php echo Sanitize::html(number_format((int)$tasksSummary['failed'])); ?></div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-success-rate')); ?></span>
                                    <span class="fw-semibold text-success"><?php echo Sanitize::html(number_format((float)$tasksSummary['successRate'], 1)); ?>%</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-failure-rate')); ?></span>
                                    <span class="fw-semibold text-danger"><?php echo Sanitize::html(number_format((float)$tasksSummary['failureRate'], 1)); ?>%</span>
                                </div>
                                <?php
                                $successPercent = $tasksSummary['total'] > 0 ? min(100, max(0, (($tasksSummary['success'] / $tasksSummary['total']) * 100))) : 0;
                                $failedPercent = $tasksSummary['total'] > 0 ? min(100, max(0, (($tasksSummary['failed'] / $tasksSummary['total']) * 100))) : 0;
                                ?>
                                <div class="mt-3">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success rounded-start" role="progressbar" style="width: <?php echo Sanitize::html(number_format($successPercent, 2)); ?>%;"></div>
                                        <div class="progress-bar bg-danger rounded-end" role="progressbar" style="width: <?php echo Sanitize::html(number_format($failedPercent, 2)); ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-semibold"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-task-status-breakdown')); ?></span>
                                    <span class="badge bg-light text-dark border"><?php echo Sanitize::html($taskStatusTotal); ?></span>
                                </div>
                                <div class="dashboard-chart"
                                     data-dashboard-chart
                                     data-chart-type="horizontal-bar"
                                     data-chart-points="<?php echo $taskStatusJson; ?>">
                                </div>
                                <div class="mt-3">
                                    <?php foreach ($taskStatusDataset as $item): ?>
                                        <div class="d-flex justify-content-between small text-muted">
                                            <span><?php echo Sanitize::html($item['label']); ?></span>
                                            <span><?php echo Sanitize::html((int)$item['value']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 bg-transparent d-flex align-items-center justify-content-between">
                    <h3 class="h6 mb-0"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-site-health')); ?></h3>
                    <a class="small text-decoration-none" href="<?php echo HTML_PATH_ADMIN_ROOT . 'site-list'; ?>">
                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-view-sites')); ?>
                        <span class="bi bi-arrow-right ms-1"></span>
                    </a>
                </div>
                <div class="card-body">
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-site-status-distribution')); ?></span>
                            <span class="badge bg-light text-dark border"><?php echo Sanitize::html($siteStatusTotal); ?></span>
                        </div>
                        <div class="dashboard-chart"
                             data-dashboard-chart
                             data-chart-type="donut"
                             data-chart-points="<?php echo $siteStatusJson; ?>">
                        </div>
                        <div class="mt-3">
                            <?php foreach ($siteStatusDataset as $item): ?>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span><?php echo Sanitize::html($item['label']); ?></span>
                                    <span><?php echo Sanitize::html((int)$item['value']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-https-insights')); ?></span>
                            <span class="badge bg-light text-dark border"><?php echo Sanitize::html($totalCertificates); ?></span>
                        </div>
                        <div class="mb-3">
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: <?php echo Sanitize::html($httpsValidPercent); ?>%;"></div>
                                <div class="progress-bar bg-warning text-dark" style="width: <?php echo Sanitize::html($httpsExpiringPercent); ?>%;"></div>
                                <div class="progress-bar bg-danger" style="width: <?php echo Sanitize::html($httpsInvalidPercent); ?>%;"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mt-2">
                                <span><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-https-valid')); ?></span>
                                <span><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-https-expiring')); ?></span>
                                <span><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-https-invalid')); ?></span>
                            </div>
                        </div>
                        <div class="dashboard-chart"
                             data-dashboard-chart
                             data-chart-type="stacked-bar"
                             data-chart-points="<?php echo $httpsTrendJson; ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 bg-transparent d-flex align-items-center justify-content-between">
                    <h3 class="h6 mb-0"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-spider-activity')); ?></h3>
                    <a class="small text-decoration-none" href="<?php echo HTML_PATH_ADMIN_ROOT . 'spider-log'; ?>">
                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-view-logs')); ?>
                        <span class="bi bi-arrow-right ms-1"></span>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted small mb-1"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-last-24h')); ?></div>
                                <div class="display-5 fw-semibold mb-2"><?php echo Sanitize::html(number_format((int)$spiderSummary['last24h'])); ?></div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-total-hits')); ?></span>
                                    <span><?php echo Sanitize::html(number_format((int)$spiderSummary['total'])); ?></span>
                                </div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-active-bots')); ?></span>
                                    <span><?php echo Sanitize::html(number_format((int)$spiderSummary['activeBots'])); ?></span>
                                </div>
                                <?php if (!empty($spiderTopSources)): ?>
                                    <div class="mt-3">
                                        <?php foreach (array_slice($spiderTopSources, 0, 4) as $source): ?>
                                            <div class="d-flex justify-content-between small">
                                                <span class="text-truncate pe-2"><?php echo Sanitize::html($source['name']); ?></span>
                                                <span class="fw-semibold"><?php echo Sanitize::html(number_format((int)$source['hits'])); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="fw-semibold mb-2"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-spider-trend')); ?></div>
                                <div class="dashboard-chart"
                                     data-dashboard-chart
                                     data-chart-type="line"
                                     data-chart-points="<?php echo $spiderTrendJson; ?>">
                                </div>
                                <div class="mt-3">
                                    <?php foreach ($spiderTrend as $item): ?>
                                        <div class="d-flex justify-content-between small text-muted">
                                            <span><?php echo Sanitize::html($item['label']); ?></span>
                                            <span><?php echo Sanitize::html(number_format((int)$item['value'])); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($spiderTrend)): ?>
                                        <div class="text-muted small"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-no-data')); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 bg-transparent">
                    <h3 class="h6 mb-0"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-system-alerts')); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($systemAlerts)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($systemAlerts as $alert): ?>
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="<?php echo Sanitize::html(mgwDashboardAlertBadgeClass($alert['level'] ?? '')); ?>">
                                            <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-alert-level-' . strtolower((string)($alert['level'] ?? '')), ucfirst((string)($alert['level'] ?? '')))); ?>
                                        </span>
                                        <?php if (!empty($alert['timestamp'])): ?>
                                            <span class="small text-muted"><?php echo Sanitize::html($alert['timestamp']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($alert['title'])): ?>
                                        <div class="fw-semibold mt-2"><?php echo Sanitize::html($alert['title']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($alert['message'])): ?>
                                        <div class="text-muted small mt-1"><?php echo Sanitize::html($alert['message']); ?></div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted small"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-no-alerts')); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header border-0 bg-transparent">
                    <h3 class="h6 mb-0"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-recommendations')); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($recommendations)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recommendations as $recommendation): ?>
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="<?php echo Sanitize::html(mgwDashboardRecommendationBadgeClass($recommendation['severity'] ?? '')); ?>">
                                            <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-recommendation-severity-' . strtolower((string)($recommendation['severity'] ?? '')), ucfirst((string)($recommendation['severity'] ?? '')))); ?>
                                        </span>
                                        <?php if (!empty($recommendation['owner'])): ?>
                                            <span class="small text-muted">
                                                <span class="bi bi-person-circle me-1"></span>
                                                <?php echo Sanitize::html($recommendation['owner']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($recommendation['title'])): ?>
                                        <div class="fw-semibold mt-2"><?php echo Sanitize::html($recommendation['title']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($recommendation['description'])): ?>
                                        <div class="text-muted small mt-1"><?php echo Sanitize::html($recommendation['description']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($recommendation['link'])): ?>
                                        <div class="mt-2">
                                            <a class="small text-decoration-none" href="<?php echo Sanitize::html($recommendation['link']); ?>">
                                                <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-action-review')); ?>
                                                <span class="bi bi-box-arrow-up-right ms-1"></span>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted small"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-no-recommendations')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 bg-transparent d-flex align-items-center justify-content-between">
                    <h3 class="h6 mb-0"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-running-tasks')); ?></h3>
                    <a class="small text-decoration-none" href="<?php echo HTML_PATH_ADMIN_ROOT . 'scheduled-tasks'; ?>">
                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-manage-tasks')); ?>
                        <span class="bi bi-arrow-right ms-1"></span>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($runningTasks)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($runningTasks as $task): ?>
                                <?php
                                $taskTitle = '';
                                if (!empty($task['title'])) {
                                    $taskTitle = $task['title'];
                                } elseif (!empty($task['name'])) {
                                    $taskTitle = $task['name'];
                                } else {
                                    $taskTitle = mgwDashboardTranslate('dashboard-task-untitled');
                                }
                                $taskStatus = isset($task['status']) ? $task['status'] : '';
                                $taskOwner = isset($task['owner']) ? $task['owner'] : '';
                                $taskSchedule = '';
                                if (!empty($task['schedule'])) {
                                    $taskSchedule = $task['schedule'];
                                } elseif (!empty($task['frequency'])) {
                                    $taskSchedule = $task['frequency'];
                                }
                                $taskNextRun = isset($task['nextRun']) ? $task['nextRun'] : '';
                                $taskLastRun = isset($task['lastRun']) ? $task['lastRun'] : '';
                                $taskLink = isset($task['link']) ? $task['link'] : '';
                                $taskDescription = isset($task['description']) ? $task['description'] : '';
                                ?>
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <?php if ($taskLink !== ''): ?>
                                                <a class="fw-semibold text-decoration-none" href="<?php echo Sanitize::html($taskLink); ?>">
                                                    <?php echo Sanitize::html($taskTitle); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="fw-semibold"><?php echo Sanitize::html($taskTitle); ?></span>
                                            <?php endif; ?>
                                            <?php if ($taskDescription !== ''): ?>
                                                <div class="text-muted small mt-1"><?php echo Sanitize::html($taskDescription); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="<?php echo Sanitize::html(mgwDashboardStatusBadgeClass($taskStatus)); ?>">
                                            <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-task-status-' . strtolower((string)$taskStatus), ucfirst((string)$taskStatus))); ?>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 small text-muted mt-2">
                                        <?php if ($taskOwner !== ''): ?>
                                            <span><span class="bi bi-person me-1"></span><?php echo Sanitize::html($taskOwner); ?></span>
                                        <?php endif; ?>
                                        <?php if ($taskSchedule !== ''): ?>
                                            <span><span class="bi bi-arrow-repeat me-1"></span><?php echo Sanitize::html($taskSchedule); ?></span>
                                        <?php endif; ?>
                                        <?php if ($taskNextRun !== ''): ?>
                                            <span><span class="bi bi-skip-forward-circle me-1"></span><?php echo Sanitize::html($taskNextRun); ?></span>
                                        <?php endif; ?>
                                        <?php if ($taskLastRun !== ''): ?>
                                            <span><span class="bi bi-clock me-1"></span><?php echo Sanitize::html($taskLastRun); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted small"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-no-running-tasks')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header border-0 bg-transparent d-flex align-items-center justify-content-between">
                    <h3 class="h6 mb-0"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-spider-latest')); ?></h3>
                    <a class="small text-decoration-none" href="<?php echo HTML_PATH_ADMIN_ROOT . 'spider-log'; ?>">
                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-open-spider-log')); ?>
                        <span class="bi bi-arrow-right ms-1"></span>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($spiderLatestRecords)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="text-muted small text-uppercase">
                                    <tr>
                                        <th><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-spider-source')); ?></th>
                                        <th><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-spider-target')); ?></th>
                                        <th><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-spider-status')); ?></th>
                                        <th class="text-end"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-spider-time')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($spiderLatestRecords as $record): ?>
                                        <tr>
                                            <td class="text-truncate" style="max-width: 140px;">
                                                <?php echo Sanitize::html($record['source'] ?? ($record['agent'] ?? '')); ?>
                                            </td>
                                            <td class="text-truncate" style="max-width: 180px;">
                                                <?php echo Sanitize::html($record['target'] ?? ($record['page'] ?? '')); ?>
                                            </td>
                                            <td>
                                                <span class="<?php echo Sanitize::html(mgwDashboardStatusBadgeClass($record['status'] ?? '')); ?>">
                                                    <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-spider-status-' . strtolower((string)($record['status'] ?? '')), ucfirst((string)($record['status'] ?? '')))); ?>
                                                </span>
                                            </td>
                                            <td class="text-end text-muted small">
                                                <?php echo Sanitize::html($record['timestamp'] ?? ($record['time'] ?? '')); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-muted small"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-no-spider-records')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 bg-transparent d-flex align-items-center justify-content-between">
                    <h3 class="h6 mb-0"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-latest-sites')); ?></h3>
                    <a class="small text-decoration-none" href="<?php echo HTML_PATH_ADMIN_ROOT . 'site-list'; ?>">
                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-all-sites')); ?>
                        <span class="bi bi-arrow-right ms-1"></span>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($latestSites)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($latestSites as $site): ?>
                                <?php
                                $siteTitle = '';
                                if (!empty($site['title'])) {
                                    $siteTitle = $site['title'];
                                } elseif (!empty($site['name'])) {
                                    $siteTitle = $site['name'];
                                } elseif (!empty($site['domain'])) {
                                    $siteTitle = $site['domain'];
                                } else {
                                    $siteTitle = mgwDashboardTranslate('dashboard-site-untitled');
                                }
                                $siteLink = isset($site['link']) ? $site['link'] : '';
                                $siteStatus = isset($site['status']) ? $site['status'] : '';
                                $siteGroup = isset($site['group']) ? $site['group'] : '';
                                $siteUpdated = '';
                                if (!empty($site['updatedAt'])) {
                                    $siteUpdated = $site['updatedAt'];
                                } elseif (!empty($site['createdAt'])) {
                                    $siteUpdated = $site['createdAt'];
                                }
                                ?>
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <?php if ($siteLink !== ''): ?>
                                                <a class="fw-semibold text-decoration-none" href="<?php echo Sanitize::html($siteLink); ?>">
                                                    <?php echo Sanitize::html($siteTitle); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="fw-semibold"><?php echo Sanitize::html($siteTitle); ?></span>
                                            <?php endif; ?>
                                            <div class="d-flex flex-wrap gap-3 small text-muted mt-1">
                                                <?php if ($siteGroup !== ''): ?>
                                                    <span><span class="bi bi-diagram-3 me-1"></span><?php echo Sanitize::html($siteGroup); ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($site['domain'])): ?>
                                                    <span><span class="bi bi-globe me-1"></span><?php echo Sanitize::html($site['domain']); ?></span>
                                                <?php endif; ?>
                                                <?php if ($siteUpdated !== ''): ?>
                                                    <span><span class="bi bi-clock me-1"></span><?php echo Sanitize::html($siteUpdated); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                            <span class="<?php echo Sanitize::html(mgwDashboardStatusBadgeClass($siteStatus)); ?>">
                                                <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-site-status-' . strtolower((string)$siteStatus), ucfirst((string)$siteStatus))); ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted small"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-no-latest-sites')); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header border-0 bg-transparent">
                    <h3 class="h6 mb-0"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-authorization')); ?></h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
                        <span class="<?php echo Sanitize::html(mgwDashboardStatusBadgeClass($authorization['status'])); ?>">
                            <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-license-status-' . strtolower((string)$authorization['status']), ucfirst((string)$authorization['status']))); ?>
                        </span>
                        <?php if ($authorization['supportLevel'] !== ''): ?>
                            <span class="badge bg-info-subtle text-info rounded-pill border">
                                <?php echo Sanitize::html($authorization['supportLevel']); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($authorization['expiresAt'] !== ''): ?>
                            <span class="small text-muted">
                                <span class="bi bi-hourglass-split me-1"></span>
                                <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-license-expires')); ?>:
                                <strong><?php echo Sanitize::html($authorization['expiresAt']); ?></strong>
                            </span>
                        <?php endif; ?>
                    </div>
                    <dl class="row small mb-0">
                        <?php if ($authorization['license'] !== ''): ?>
                            <dt class="col-5 text-muted"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-license-key')); ?></dt>
                            <dd class="col-7 fw-semibold text-break"><?php echo Sanitize::html($authorization['license']); ?></dd>
                        <?php endif; ?>
                        <?php if ($authorization['lastChecked'] !== ''): ?>
                            <dt class="col-5 text-muted"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-license-checked')); ?></dt>
                            <dd class="col-7"><?php echo Sanitize::html($authorization['lastChecked']); ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($authorization['authorizedTo']) && is_array($authorization['authorizedTo'])): ?>
                            <dt class="col-5 text-muted"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-license-assigned')); ?></dt>
                            <dd class="col-7">
                                <ul class="list-unstyled mb-0 small">
                                    <?php foreach ($authorization['authorizedTo'] as $recipient): ?>
                                        <li><span class="bi bi-dot me-1"></span><?php echo Sanitize::html($recipient); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-0 bg-transparent d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h3 class="h6 mb-0"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-infrastructure-summary')); ?></h3>
                    <span class="small text-muted">
                        <span class="bi bi-hdd-network me-1"></span>
                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-live-coverage')); ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="fw-semibold mb-2"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-group-coverage')); ?></div>
                                <ul class="list-unstyled small mb-0 text-muted">
                                    <li>
                                        <span class="bi bi-collection me-1"></span>
                                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-groups-total')); ?>:
                                        <strong><?php echo Sanitize::html(number_format((int)$overviewSummary['totalGroups'])); ?></strong>
                                    </li>
                                    <li>
                                        <span class="bi bi-check-circle me-1"></span>
                                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-groups-active')); ?>:
                                        <strong><?php echo Sanitize::html(number_format((int)$overviewSummary['activeGroups'])); ?></strong>
                                    </li>
                                    <li>
                                        <span class="bi bi-exclamation-triangle me-1"></span>
                                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-groups-abnormal')); ?>:
                                        <strong><?php echo Sanitize::html(number_format((int)$overviewSummary['abnormalGroups'])); ?></strong>
                                    </li>
                                    <li>
                                        <span class="bi bi-hourglass-split me-1"></span>
                                        <?php echo Sanitize::html(mgwDashboardTranslate('dashboard-groups-pending')); ?>:
                                        <strong><?php echo Sanitize::html(number_format((int)$overviewSummary['pendingGroups'])); ?></strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="fw-semibold mb-2"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-operations-notes')); ?></div>
                                <?php if ($metadata['notes'] !== ''): ?>
                                    <div class="text-muted small"><?php echo Sanitize::html($metadata['notes']); ?></div>
                                <?php else: ?>
                                    <div class="text-muted small"><?php echo Sanitize::html(mgwDashboardTranslate('dashboard-no-operations-notes')); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
