<?php defined('MAIGEWAN') or die('Maigewan CMS.');

echo Bootstrap::pageTitle(array('title' => $L->g('scheduled-tasks-page-title'), 'icon' => 'clock-history'));

if (!function_exists('mgwScheduledLang')) {
    function mgwScheduledLang($key, $default = '')
    {
        global $L;
        $value = $L->g($key);
        return $value !== $key ? $value : $default;
    }
}

if (!function_exists('mgwScheduledFormatDateTime')) {
    function mgwScheduledFormatDateTime($timestamp)
    {
        if (empty($timestamp)) {
            return mgwScheduledLang('scheduled-tasks-value-never', '--');
        }

        try {
            if (!is_numeric($timestamp)) {
                $timestamp = strtotime((string) $timestamp);
            }
            if (!$timestamp) {
                return mgwScheduledLang('scheduled-tasks-value-never', '--');
            }
            $date = new DateTime('@' . (int) $timestamp);
            $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
            return Date::translate($date->format('Y-m-d H:i'));
        } catch (Exception $e) {
            return mgwScheduledLang('scheduled-tasks-value-never', '--');
        }
    }
}

if (!function_exists('mgwScheduledFormatDuration')) {
    function mgwScheduledFormatDuration($seconds)
    {
        if ($seconds === null || $seconds === false) {
            return mgwScheduledLang('scheduled-tasks-value-empty', '--');
        }

        $seconds = (int) $seconds;
        if ($seconds <= 0) {
            return mgwScheduledLang('scheduled-tasks-value-empty', '--');
        }

        if ($seconds < 60) {
            return $seconds . ' ' . mgwScheduledLang('scheduled-tasks-unit-seconds', 's');
        }

        $minutes = (int) floor($seconds / 60);
        $remaining = $seconds % 60;
        $label = $minutes . ' ' . mgwScheduledLang('scheduled-tasks-unit-minutes', 'min');
        if ($remaining > 0) {
            $label .= ' ' . $remaining . ' ' . mgwScheduledLang('scheduled-tasks-unit-seconds', 's');
        }
        return $label;
    }
}

if (!function_exists('mgwScheduledMetricVariant')) {
    function mgwScheduledMetricVariant($variant)
    {
        switch ($variant) {
            case 'up':
                return array('icon' => 'arrow-up-right', 'class' => 'text-success');
            case 'down':
                return array('icon' => 'arrow-down-right', 'class' => 'text-danger');
            case 'alert':
                return array('icon' => 'exclamation-triangle', 'class' => 'text-danger');
            default:
                return array('icon' => 'dash-lg', 'class' => 'text-muted');
        }
    }
}

if (!function_exists('mgwScheduledStatusMeta')) {
    function mgwScheduledStatusMeta($code, $statuses)
    {
        if (isset($statuses[$code])) {
            return $statuses[$code];
        }
        return array('label' => ucfirst((string) $code), 'badge' => 'secondary');
    }
}

if (!function_exists('mgwScheduledPriorityMeta')) {
    function mgwScheduledPriorityMeta($code, $priorities)
    {
        if (isset($priorities[$code])) {
            return $priorities[$code];
        }
        return array('label' => ucfirst((string) $code), 'badge' => 'secondary');
    }
}

$taskOverview = isset($taskOverview) && is_array($taskOverview) ? $taskOverview : array();
$queueMetrics = isset($queueMetrics) && is_array($queueMetrics) ? $queueMetrics : array();
$alerts = isset($alerts) && is_array($alerts) ? $alerts : array('hasAlerts' => false, 'items' => array());
$taskList = isset($taskList) && is_array($taskList) ? $taskList : array();
$taskCategories = isset($taskCategories) && is_array($taskCategories) ? $taskCategories : array();
$taskSites = isset($taskSites) && is_array($taskSites) ? $taskSites : array();
$taskStatuses = isset($taskStatuses) && is_array($taskStatuses) ? $taskStatuses : array();
$taskPriorities = isset($taskPriorities) && is_array($taskPriorities) ? $taskPriorities : array();
$taskFilters = isset($taskFilters) && is_array($taskFilters) ? $taskFilters : array();
$taskActions = isset($taskActions) && is_array($taskActions) ? $taskActions : array();
$taskLogs = isset($taskLogs) && is_array($taskLogs) ? $taskLogs : array();
$logOverview = isset($logOverview) && is_array($logOverview) ? $logOverview : array();
$taskTimeline = isset($taskTimeline) && is_array($taskTimeline) ? $taskTimeline : array();
$cronPresets = isset($cronPresets) && is_array($cronPresets) ? $cronPresets : array();
$taskParameterSchemas = isset($taskParameterSchemas) && is_array($taskParameterSchemas) ? $taskParameterSchemas : array();
$taskTriggers = isset($taskTriggers) && is_array($taskTriggers) ? $taskTriggers : array();
$retryBackoffLabels = isset($retryBackoffLabels) && is_array($retryBackoffLabels) ? $retryBackoffLabels : array();

$taskSitesMap = array();
foreach ($taskSites as $siteEntry) {
    if (isset($siteEntry['id'])) {
        $taskSitesMap[$siteEntry['id']] = $siteEntry;
    }
}

$taskCount = count($taskList);
$logCount = count($taskLogs);

$schemasJson = Sanitize::html(json_encode($taskParameterSchemas));
$actionsJson = Sanitize::html(json_encode($taskActions));
$statusesJson = Sanitize::html(json_encode($taskStatuses));
$prioritiesJson = Sanitize::html(json_encode($taskPriorities));
$logsJson = Sanitize::html(json_encode($taskLogs));
$cronPresetsJson = Sanitize::html(json_encode($cronPresets));
$sitesJson = Sanitize::html(json_encode($taskSites));
$categoriesJson = Sanitize::html(json_encode($taskCategories));
$triggersJson = Sanitize::html(json_encode($taskTriggers));
$retryBackoffJson = Sanitize::html(json_encode($retryBackoffLabels));
$jsTranslations = array(
    'scheduled-tasks-duration-label' => $L->g('scheduled-tasks-duration-label'),
    'scheduled-tasks-unit-seconds' => $L->g('scheduled-tasks-unit-seconds'),
    'scheduled-tasks-unit-minutes' => $L->g('scheduled-tasks-unit-minutes'),
    'scheduled-tasks-log-run-count' => $L->g('scheduled-tasks-log-run-count'),
    'scheduled-tasks-log-last-execution' => $L->g('scheduled-tasks-log-last-execution'),
    'scheduled-tasks-logs-empty-task' => $L->g('scheduled-tasks-logs-empty-task')
);
$translationsJson = Sanitize::html(json_encode($jsTranslations));

?>
<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>scheduled-tasks.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">

<div class="mgw-scheduled" data-scheduled-root
    data-task-schemas="<?= $schemasJson ?>"
    data-task-actions="<?= $actionsJson ?>"
    data-task-statuses="<?= $statusesJson ?>"
    data-task-priorities="<?= $prioritiesJson ?>"
    data-task-logs="<?= $logsJson ?>"
    data-cron-presets="<?= $cronPresetsJson ?>"
    data-task-sites="<?= $sitesJson ?>"
    data-task-categories="<?= $categoriesJson ?>"
    data-task-triggers="<?= $triggersJson ?>"
    data-retry-backoff="<?= $retryBackoffJson ?>"
    data-translations="<?= $translationsJson ?>">
    <p class="mgw-scheduled-subtitle"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-page-subtitle', 'Central orchestration for every Maigewan site cluster.')) ?></p>
    <div class="mgw-scheduled-toolbar">
        <div class="mgw-scheduled-toolbar-info">
            <span class="badge text-bg-light text-primary fw-semibold me-2"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-toolbar-badge', 'Multi-site scheduler')) ?></span>
            <span class="text-muted small"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-toolbar-caption', 'Monitor queues, recover failures, and launch jobs on demand.')) ?></span>
        </div>
        <div class="mgw-scheduled-toolbar-actions">
            <button type="button" class="btn btn-outline-secondary" data-task-alert-config>
                <span class="bi bi-bell me-2"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-button-alert-config', 'Alert settings')) ?>
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mgwScheduledTaskModal" data-task-create>
                <span class="bi bi-plus-lg me-2"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-button-create', 'Create task')) ?>
            </button>
        </div>
    </div>

    <?php if (!empty($alerts['hasAlerts']) && !empty($alerts['items']) && is_array($alerts['items'])): ?>
    <div class="card border-0 shadow-sm mgw-scheduled-alerts">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-alerts-heading', 'Scheduler alerts')) ?></h5>
                    <p class="text-muted small mb-0"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-alerts-subheading', 'Recent failures and latency spikes that require attention.')) ?></p>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" data-task-alert-clear>
                    <span class="bi bi-x-circle me-1"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-alerts-dismiss', 'Mark all resolved')) ?>
                </button>
            </div>
            <?php foreach ($alerts['items'] as $alert):
                $level = isset($alert['level']) ? (string) $alert['level'] : 'warning';
                $alertClass = 'warning';
                if ($level === 'danger') {
                    $alertClass = 'danger';
                } elseif ($level === 'info') {
                    $alertClass = 'info';
                }
            ?>
            <div class="alert alert-<?= Sanitize::html($alertClass) ?> d-flex align-items-start justify-content-between" role="alert">
                <div>
                    <div class="fw-semibold"><?= Sanitize::html($alert['title'] ?? '') ?></div>
                    <div class="small"><?= Sanitize::html($alert['message'] ?? '') ?></div>
                    <?php if (!empty($alert['meta'])): ?>
                    <div class="small text-muted mt-1"><?= Sanitize::html($alert['meta']) ?></div>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endforeach; ?>
            <div class="text-end">
                <a href="#" class="small" data-task-alert-config><span class="bi bi-gear me-1"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-alerts-manage', 'Manage alert rules')) ?></a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <ul class="nav nav-pills mgw-scheduled-nav" id="mgwScheduledTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="mgw-scheduled-center-tab" data-bs-toggle="tab" data-bs-target="#mgw-scheduled-center" type="button" role="tab" aria-controls="mgw-scheduled-center" aria-selected="true">
                <span class="bi bi-hdd-network me-1"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-tab-center', 'Task hub')) ?>
            </button>
        </div>
    </ul>

    <div class="tab-content" id="mgwScheduledTabContent">
        <div class="tab-pane fade show active" id="mgw-scheduled-center" role="tabpanel" aria-labelledby="mgw-scheduled-center-tab">
            <div class="row g-3 mt-1">
                <div class="col-xxl-3 col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body mgw-scheduled-filters">
                            <h5 class="mb-1"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-filters-title', 'Filters')) ?></h5>
                            <p class="text-muted small mb-3"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-filters-subtitle', 'Narrow tasks by state, type, and site.')) ?></p>
                            <div class="mb-3">
                                <label class="form-label" for="mgw-scheduled-filter-search"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-filters-search', 'Search tasks')) ?></label>
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-search"></span></span>
                                    <input type="search" class="form-control" id="mgw-scheduled-filter-search" placeholder="<?= Sanitize::html(mgwScheduledLang('scheduled-tasks-search-placeholder', 'Search by name or ID')) ?>" data-filter-search>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="mgw-scheduled-filter-status"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-filters-status', 'Status')) ?></label>
                                <select class="form-select" id="mgw-scheduled-filter-status" data-filter-status>
                                    <option value=""><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-filter-any', 'All statuses')) ?></option>
                                    <?php if (!empty($taskFilters['statuses'])): ?>
                                        <?php foreach ($taskFilters['statuses'] as $statusOption): ?>
                                            <option value="<?= Sanitize::html($statusOption['value']) ?>"><?= Sanitize::html($statusOption['label']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="mgw-scheduled-filter-type"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-filters-type', 'Task type')) ?></label>
                                <select class="form-select" id="mgw-scheduled-filter-type" data-filter-type>
                                    <option value=""><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-filter-any-type', 'All types')) ?></option>
                                    <?php if (!empty($taskFilters['types'])): ?>
                                        <?php foreach ($taskFilters['types'] as $typeOption): ?>
                                            <option value="<?= Sanitize::html($typeOption['value']) ?>"><?= Sanitize::html($typeOption['label']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="mgw-scheduled-filter-site"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-filters-site', 'Site')) ?></label>
                                <select class="form-select" id="mgw-scheduled-filter-site" data-filter-site>
                                    <option value=""><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-filter-any-site', 'All sites')) ?></option>
                                    <?php if (!empty($taskFilters['sites'])): ?>
                                        <?php foreach ($taskFilters['sites'] as $siteOption): ?>
                                            <option value="<?= Sanitize::html($siteOption['id']) ?>"><?= Sanitize::html($siteOption['label']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-filter-reset>
                                <span class="bi bi-arrow-counterclockwise me-1"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-filters-reset', 'Reset filters')) ?>
                            </button>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mgw-scheduled-queue-card mt-3">
                        <div class="card-body">
                            <h6 class="text-uppercase text-muted fw-semibold mb-2"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-queue-title', 'Queue health')) ?></h6>
                            <p class="text-muted small mb-3"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-queue-subtitle', 'Real-time capacity and retry policy snapshot.')) ?></p>
                            <?php
                            $maxConcurrency = isset($queueMetrics['maxConcurrency']) ? (int) $queueMetrics['maxConcurrency'] : 0;
                            $currentConcurrency = isset($queueMetrics['currentConcurrency']) ? (int) $queueMetrics['currentConcurrency'] : 0;
                            $concurrencyPercent = $maxConcurrency > 0 ? (int) min(100, round(($currentConcurrency / $maxConcurrency) * 100)) : 0;
                            $queueDepth = isset($queueMetrics['queueDepth']) ? (int) $queueMetrics['queueDepth'] : 0;
                            $averageLatency = isset($queueMetrics['averageLatency']) ? (int) $queueMetrics['averageLatency'] : null;
                            $retryPolicy = isset($queueMetrics['retryPolicy']) && is_array($queueMetrics['retryPolicy']) ? $queueMetrics['retryPolicy'] : array();
                            $retryMax = isset($retryPolicy['maxRetries']) ? (int) $retryPolicy['maxRetries'] : 0;
                            $retryBackoffKey = isset($retryPolicy['backoff']) ? (string) $retryPolicy['backoff'] : '';
                            $retryBackoffLabel = isset($retryBackoffLabels[$retryBackoffKey]) ? $retryBackoffLabels[$retryBackoffKey] : $retryBackoffKey;
                            ?>
                            <div class="metric-line mb-2">
                                <span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-queue-concurrency', 'Concurrent workers')) ?></span>
                                <span class="fw-semibold"><?= Sanitize::html($currentConcurrency . ' / ' . ($maxConcurrency ?: 0)) ?></span>
                            </div>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= (int) $concurrencyPercent ?>%;" aria-valuenow="<?= (int) $concurrencyPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="metric-line mb-2">
                                <span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-queue-depth', 'Queue depth')) ?></span>
                                <span class="fw-semibold"><?= Sanitize::html((string) $queueDepth) ?></span>
                            </div>
                            <div class="metric-line mb-2">
                                <span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-queue-latency', 'Average wait')) ?></span>
                                <span class="fw-semibold"><?= Sanitize::html(mgwScheduledFormatDuration($averageLatency)) ?></span>
                            </div>
                            <div class="metric-line mb-3">
                                <span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-queue-retry', 'Retry policy')) ?></span>
                                <span class="fw-semibold text-primary"><?= Sanitize::html(sprintf(mgwScheduledLang('scheduled-tasks-queue-retry-policy', 'Max %1$d · %2$s'), $retryMax, $retryBackoffLabel ?: mgwScheduledLang('scheduled-tasks-queue-retry-unknown', 'n/a'))) ?></span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100" data-task-queue-config>
                                <span class="bi bi-sliders me-1"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-queue-manage', 'Adjust orchestration')) ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-9 col-lg-8">
                    <?php if (!empty($taskOverview)): ?>
                    <div class="row g-3 mgw-scheduled-kpis">
                        <?php foreach ($taskOverview as $metric):
                            $metricVariant = mgwScheduledMetricVariant($metric['deltaVariant'] ?? 'steady');
                        ?>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <span class="mgw-scheduled-kpi-label"><?= Sanitize::html($metric['label'] ?? '') ?></span>
                                    <div class="h3 fw-semibold mb-2"><?= Sanitize::html((string) ($metric['value'] ?? '0')) ?></div>
                                    <?php if (!empty($metric['delta'])): ?>
                                    <span class="mgw-scheduled-kpi-delta <?= Sanitize::html($metricVariant['class']) ?>">
                                        <span class="bi bi-<?= Sanitize::html($metricVariant['icon']) ?>"></span>
                                        <?= Sanitize::html((string) $metric['delta']) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="mb-0"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-category-heading', 'Task categories')) ?></h5>
                                <span class="text-muted small"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-category-helper', 'Focus the grid by workload family.')) ?></span>
                            </div>
                            <div class="nav mgw-scheduled-category-nav" role="tablist">
                                <button type="button" class="nav-link active" data-task-category="all" data-task-category-link>
                                    <span class="bi bi-collection me-1"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-category-all', 'All tasks')) ?>
                                </button>
                                <?php foreach ($taskCategories as $categoryKey => $categoryMeta): ?>
                                <button type="button" class="nav-link" data-task-category="<?= Sanitize::html($categoryKey) ?>" data-task-category-link>
                                    <span class="bi bi-<?= Sanitize::html($categoryMeta['icon'] ?? 'circle') ?> me-1"></span><?= Sanitize::html($categoryMeta['label'] ?? ucfirst($categoryKey)) ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card mgw-scheduled-table-card mt-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-1"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-table-heading', 'Scheduled tasks')) ?></h5>
                                    <p class="text-muted small mb-0"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-table-subheading', 'Live view of distributed cron jobs across every site.')) ?></p>
                                </div>
                                <div class="text-muted small">
                                    <span class="bi bi-list-ul me-1"></span><span data-task-count><?= Sanitize::html((string) $taskCount) ?></span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle mgw-scheduled-table" data-task-table>
                                    <thead>
                                        <tr>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-table-header-name', 'Task')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-table-header-type', 'Type')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-table-header-site', 'Site')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-table-header-frequency', 'Frequency')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-table-header-last-run', 'Last run')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-table-header-next-run', 'Next run')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-table-header-status', 'Status')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-table-header-priority', 'Priority')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-table-header-retries', 'Retry / queue')) ?></th>
                                            <th scope="col" class="text-end"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-table-header-actions', 'Actions')) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($taskList as $task):
                                            $taskId = $task['id'] ?? '';
                                            $taskName = $task['name'] ?? $taskId;
                                            $taskType = $task['type'] ?? '';
                                            $taskSite = $task['site'] ?? '';
                                            $taskStatusCode = $task['status'] ?? 'queued';
                                            $taskStatus = mgwScheduledStatusMeta($taskStatusCode, $taskStatuses);
                                            $taskPriorityCode = $task['priority'] ?? 'medium';
                                            $taskPriority = mgwScheduledPriorityMeta($taskPriorityCode, $taskPriorities);
                                            $siteMeta = isset($taskSitesMap[$taskSite]) ? $taskSitesMap[$taskSite] : null;
                                            $siteLabel = $task['siteLabel'] ?? ($siteMeta['label'] ?? $taskSite);
                                            $siteTag = $siteMeta['tag'] ?? '';
                                            $frequencyLabel = $task['frequencyLabel'] ?? '';
                                            $frequencyCron = $task['frequency'] ?? '';
                                            $lastRun = isset($task['lastRun']) ? mgwScheduledFormatDateTime($task['lastRun']) : mgwScheduledLang('scheduled-tasks-value-never', '--');
                                            $nextRun = isset($task['nextRun']) ? mgwScheduledFormatDateTime($task['nextRun']) : mgwScheduledLang('scheduled-tasks-value-unscheduled', 'Paused');
                                            $priorityBadge = 'text-bg-' . Sanitize::html($taskPriority['badge']);
                                            $statusBadge = 'text-bg-' . Sanitize::html($taskStatus['badge']);
                                            $durationHuman = isset($task['duration']) ? mgwScheduledFormatDuration($task['duration']) : mgwScheduledLang('scheduled-tasks-value-empty', '--');
                                            $queuePosition = isset($task['queuePosition']) ? $task['queuePosition'] : null;
                                            $owner = $task['owner'] ?? '';
                                            $retries = isset($task['retries']) ? (int) $task['retries'] : 0;
                                            $maxRetries = isset($task['maxRetries']) ? (int) $task['maxRetries'] : 0;
                                            $toggleAction = $taskStatusCode === 'paused' ? 'resume' : 'pause';
                                            $toggleLabel = $taskStatusCode === 'paused' ? mgwScheduledLang('scheduled-tasks-action-resume', 'Resume') : mgwScheduledLang('scheduled-tasks-action-pause', 'Pause');
                                            $toggleIcon = $taskStatusCode === 'paused' ? 'play-fill' : 'pause-circle';
                                        ?>
                                        <tr data-task-row data-task-id="<?= Sanitize::html($taskId) ?>" data-task-status="<?= Sanitize::html($taskStatusCode) ?>" data-task-category="<?= Sanitize::html($taskType) ?>" data-task-site="<?= Sanitize::html($taskSite) ?>">
                                            <td>
                                                <div class="task-name" data-task-name><?= Sanitize::html($taskName) ?></div>
                                                <div class="task-meta">
                                                    <span class="text-muted">#<?= Sanitize::html($taskId) ?></span>
                                                    <?php if ($owner): ?> · <span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-owner-label', 'Owner')) ?>: <?= Sanitize::html($owner) ?></span><?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge text-bg-light text-secondary fw-semibold">
                                                    <span class="bi bi-<?= Sanitize::html($taskCategories[$taskType]['icon'] ?? 'circle') ?> me-1"></span><?= Sanitize::html($taskCategories[$taskType]['label'] ?? ucfirst($taskType)) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-break"><?= Sanitize::html($siteLabel) ?></div>
                                                <?php if ($siteTag): ?>
                                                <div class="small text-muted"><?= Sanitize::html($siteTag) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= Sanitize::html($frequencyLabel) ?></div>
                                                <div class="small text-muted"><code><?= Sanitize::html($frequencyCron) ?></code></div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-break"><?= Sanitize::html($lastRun) ?></div>
                                                <div class="small text-muted"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-duration-label', 'Duration')) ?>: <?= Sanitize::html($durationHuman) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-break"><?= Sanitize::html($nextRun) ?></div>
                                                <?php if ($queuePosition !== null): ?>
                                                <div class="small text-muted"><?= Sanitize::html(sprintf(mgwScheduledLang('scheduled-tasks-queue-position-label', 'Queue #%s'), $queuePosition)) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $statusBadge ?>"><?= Sanitize::html($taskStatus['label']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $priorityBadge ?>"><?= Sanitize::html($taskPriority['label']) ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= Sanitize::html(sprintf(mgwScheduledLang('scheduled-tasks-retry-format', '%1$d / %2$d'), $retries, $maxRetries)) ?></div>
                                                <?php if ($queuePosition !== null): ?>
                                                <div class="small text-muted"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-retry-queue', 'Waiting in queue')) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-task-action="run">
                                                        <span class="bi bi-play-circle me-1"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-action-run-now', 'Run now')) ?>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-task-action="<?= Sanitize::html($toggleAction) ?>">
                                                        <span class="bi bi-<?= Sanitize::html($toggleIcon) ?> me-1"></span><?= Sanitize::html($toggleLabel) ?>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-task-action="logs">
                                                        <span class="bi bi-list-stars me-1"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-action-view-logs', 'Logs')) ?>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <span class="visually-hidden">Toggle Dropdown</span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><button class="dropdown-item" type="button" data-task-action="edit"><span class="bi bi-pencil-square me-2"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-action-edit', 'Edit')) ?></button></li>
                                                        <li><button class="dropdown-item" type="button" data-task-action="duplicate"><span class="bi bi-files me-2"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-action-duplicate', 'Duplicate')) ?></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button class="dropdown-item text-danger" type="button" data-task-action="delete"><span class="bi bi-trash3 me-2"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-action-delete', 'Delete')) ?></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="mgw-scheduled-empty d-none" data-task-empty>
                                            <td colspan="10">
                                                <div class="mgw-scheduled-empty-state">
                                                    <span class="bi bi-calendar-x"></span>
                                                    <h6 class="mt-2"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-empty-title', 'No tasks match the filters')) ?></h6>
                                                    <p class="text-muted small mb-3"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-empty-subtitle', 'Adjust filters or create a new scheduled job for your sites.')) ?></p>
                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mgwScheduledTaskModal">
                                                        <span class="bi bi-plus-lg me-1"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-empty-button', 'Create task')) ?>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="mgw-scheduled-logs" role="tabpanel" aria-labelledby="mgw-scheduled-logs-tab">
            <div class="row g-3 mt-1">
                <div class="col-xl-4 order-xl-2">
                    <div class="card mgw-scheduled-log-card">
                        <div class="card-body">
                            <h6 class="text-uppercase text-muted fw-semibold mb-2"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-overview-heading', 'Performance snapshot')) ?></h6>
                            <div class="mb-3">
                                <span class="fw-semibold h4 me-2"><?= Sanitize::html(isset($logOverview['successRate']) ? number_format((float) $logOverview['successRate'], 1) . '%' : '0%') ?></span>
                                <span class="text-muted small"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-overview-success', 'Success rate')) ?></span>
                            </div>
                            <div class="mb-2">
                                <span class="fw-semibold"><?= Sanitize::html(mgwScheduledFormatDuration($logOverview['averageDuration'] ?? null)) ?></span>
                                <span class="text-muted small ms-1"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-overview-duration', 'Average duration')) ?></span>
                            </div>
                            <div class="mb-3 text-muted small"><?= Sanitize::html(sprintf(mgwScheduledLang('scheduled-tasks-log-overview-window', 'Window: %s'), $logOverview['failureWindow'] ?? '--')) ?></div>
                            <h6 class="text-uppercase text-muted fw-semibold mb-2"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-timeline-heading', 'Live timeline')) ?></h6>
                            <?php if (!empty($taskTimeline)): ?>
                            <ul class="mgw-scheduled-timeline">
                                <?php foreach ($taskTimeline as $timelineEntry):
                                    $timelineStatus = mgwScheduledStatusMeta($timelineEntry['status'] ?? 'queued', $taskStatuses);
                                    $timelineColor = '#6c757d';
                                    if ($timelineEntry['status'] === 'success') {
                                        $timelineColor = '#198754';
                                    } elseif ($timelineEntry['status'] === 'failed') {
                                        $timelineColor = '#dc3545';
                                    } elseif ($timelineEntry['status'] === 'running') {
                                        $timelineColor = '#0d6efd';
                                    }
                                ?>
                                <li class="mgw-scheduled-timeline-item">
                                    <span class="mgw-scheduled-timeline-marker" style="background: <?= Sanitize::html($timelineColor) ?>;"></span>
                                    <div class="mgw-scheduled-timeline-content">
                                        <div class="fw-semibold"><?= Sanitize::html($timelineEntry['taskName'] ?? '') ?></div>
                                        <div class="small text-muted mb-1"><?= Sanitize::html(mgwScheduledFormatDateTime($timelineEntry['time'] ?? null)) ?> · <?= Sanitize::html($timelineEntry['siteLabel'] ?? '') ?></div>
                                        <span class="badge text-bg-<?= Sanitize::html($timelineStatus['badge']) ?>"><?= Sanitize::html($timelineStatus['label']) ?></span>
                                        <?php if (!empty($timelineEntry['duration'])): ?>
                                        <span class="small text-muted ms-2"><?= Sanitize::html(mgwScheduledFormatDuration($timelineEntry['duration'])) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php else: ?>
                            <p class="text-muted small mb-0"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-timeline-empty', 'Timeline will populate as soon as tasks execute.')) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 order-xl-1">
                    <div class="card mgw-scheduled-log-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-1"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-table-heading', 'Execution history')) ?></h5>
                                    <p class="text-muted small mb-0"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-table-subheading', 'Inspect successes, failures, and manual triggers.')) ?></p>
                                </div>
                                <div class="d-flex gap-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><span class="bi bi-search"></span></span>
                                        <input type="search" class="form-control" placeholder="<?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-search-placeholder', 'Search logs')) ?>" data-log-search>
                                    </div>
                                    <select class="form-select form-select-sm" data-log-status>
                                        <option value=""><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-filter-any', 'All statuses')) ?></option>
                                        <?php foreach ($taskStatuses as $statusCode => $statusMeta): ?>
                                        <option value="<?= Sanitize::html($statusCode) ?>"><?= Sanitize::html($statusMeta['label']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-log-export>
                                        <span class="bi bi-download me-1"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-export', 'Export CSV')) ?>
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle mgw-scheduled-log-table" data-log-table>
                                    <thead>
                                        <tr>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-header-time', 'Time')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-header-task', 'Task')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-header-site', 'Site')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-header-status', 'Status')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-header-duration', 'Duration')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-header-trigger', 'Trigger')) ?></th>
                                            <th scope="col"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-header-message', 'Message')) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($taskLogs as $log):
                                            $logStatus = mgwScheduledStatusMeta($log['status'] ?? 'queued', $taskStatuses);
                                            $logTriggerKey = $log['trigger'] ?? '';
                                            $logTriggerLabel = isset($taskTriggers[$logTriggerKey]) ? $taskTriggers[$logTriggerKey] : ucfirst((string) $logTriggerKey);
                                        ?>
                                        <tr data-log-row data-log-status="<?= Sanitize::html($log['status'] ?? '') ?>" data-log-task="<?= Sanitize::html($log['taskId'] ?? '') ?>">
                                            <td><?= Sanitize::html(mgwScheduledFormatDateTime($log['startedAt'] ?? null)) ?></td>
                                            <td>
                                                <div class="fw-semibold"><?= Sanitize::html($log['taskName'] ?? '') ?></div>
                                                <div class="small text-muted">#<?= Sanitize::html($log['taskId'] ?? '') ?></div>
                                            </td>
                                            <td><?= Sanitize::html($log['siteLabel'] ?? '') ?></td>
                                            <td><span class="badge text-bg-<?= Sanitize::html($logStatus['badge']) ?>"><?= Sanitize::html($logStatus['label']) ?></span></td>
                                            <td><?= Sanitize::html(mgwScheduledFormatDuration($log['duration'] ?? null)) ?></td>
                                            <td><?= Sanitize::html($logTriggerLabel) ?></td>
                                            <td class="text-break"><?= Sanitize::html($log['logExcerpt'] ?? '') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="d-none" data-log-empty>
                                            <td colspan="7" class="text-center py-4 text-muted"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-logs-empty', 'No log entries match the filters.')) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-muted small"><span class="bi bi-journal-text me-1"></span><span data-log-count><?= Sanitize::html((string) $logCount) ?></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade mgw-scheduled-modal" id="mgwScheduledTaskModal" tabindex="-1" aria-labelledby="mgwScheduledTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mgwScheduledTaskModalLabel"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-title', 'Create scheduled task')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mgw-scheduled-modal-section">
                    <h6><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-section-basics', 'Basics')) ?></h6>
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label" for="mgw-task-name"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-name-label', 'Task name')) ?></label>
                            <input type="text" class="form-control" id="mgw-task-name" placeholder="<?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-name-placeholder', 'Describe the job purpose')) ?>" data-task-input="name">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label" for="mgw-task-type"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-type-label', 'Task type')) ?></label>
                            <select class="form-select" id="mgw-task-type" data-task-input="type">
                                <?php foreach ($taskCategories as $categoryKey => $categoryMeta): ?>
                                <option value="<?= Sanitize::html($categoryKey) ?>"><?= Sanitize::html($categoryMeta['label'] ?? ucfirst($categoryKey)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label" for="mgw-task-site"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-site-label', 'Target site')) ?></label>
                            <select class="form-select" id="mgw-task-site" data-task-input="site">
                                <?php foreach ($taskSites as $siteOption): ?>
                                <option value="<?= Sanitize::html($siteOption['id']) ?>"><?= Sanitize::html($siteOption['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mgw-scheduled-modal-section">
                    <h6><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-section-schedule', 'Schedule')) ?></h6>
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label" for="mgw-task-cron"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-cron-label', 'Cron expression')) ?></label>
                            <input type="text" class="form-control" id="mgw-task-cron" placeholder="<?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-cron-placeholder', '*/5 * * * *')) ?>" data-task-input="cron">
                            <div class="form-text"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-cron-help', 'Use standard cron format or pick a preset below.')) ?></div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-preset-label', 'Presets')) ?></label>
                            <div class="mgw-scheduled-preset-group">
                                <?php foreach ($cronPresets as $preset): ?>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-cron-preset="<?= Sanitize::html($preset['value']) ?>"><?= Sanitize::html($preset['label']) ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mgw-scheduled-modal-section">
                    <h6><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-section-parameters', 'Parameters')) ?></h6>
                    <p class="text-muted small mb-3"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-parameters-hint', 'Fields change depending on the task type you choose.')) ?></p>
                    <div data-task-parameters>
                        <div class="alert alert-light mb-0" data-task-parameters-empty><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-parameters-empty', 'Select a task type to configure specific parameters.')) ?></div>
                        <div data-task-parameters-list></div>
                    </div>
                </div>

                <div class="mgw-scheduled-modal-section">
                    <h6><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-section-advanced', 'Advanced')) ?></h6>
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label" for="mgw-task-priority"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-priority-label', 'Priority')) ?></label>
                            <select class="form-select" id="mgw-task-priority" data-task-input="priority">
                                <?php foreach ($taskPriorities as $priorityKey => $priorityMeta): ?>
                                <option value="<?= Sanitize::html($priorityKey) ?>"><?= Sanitize::html($priorityMeta['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-priority-help', 'Higher priority tasks preempt lower priority queue entries.')) ?></div>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label" for="mgw-task-retries"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-retry-label', 'Max retries')) ?></label>
                            <input type="number" class="form-control" id="mgw-task-retries" min="0" value="3" data-task-input="maxRetries">
                            <div class="form-text"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-retry-help', 'Retries are scheduled with exponential backoff.')) ?></div>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label" for="mgw-task-concurrency"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-concurrency-label', 'Max concurrency')) ?></label>
                            <input type="number" class="form-control" id="mgw-task-concurrency" min="1" value="1" data-task-input="concurrency">
                            <div class="form-text"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-concurrency-help', 'Limit simultaneous workers for this task.')) ?></div>
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="mgw-task-run-after" data-task-input="runAfterSave">
                        <label class="form-check-label" for="mgw-task-run-after">
                            <?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-run-after-save', 'Run immediately after saving')) ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-cancel', 'Cancel')) ?></button>
                <button type="button" class="btn btn-primary" data-task-submit><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-modal-save', 'Save task')) ?></button>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end mgw-scheduled-logs-offcanvas" tabindex="-1" id="mgwScheduledLogsOffcanvas" aria-labelledby="mgwScheduledLogsOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mgwScheduledLogsOffcanvasLabel" data-log-offcanvas-title><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-offcanvas-title', 'Task activity log')) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="text-muted small" data-log-offcanvas-meta></div>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-log-offcanvas-export><span class="bi bi-download me-1"></span><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-log-export', 'Export CSV')) ?></button>
        </div>
        <div class="list-group" data-log-offcanvas-list></div>
        <div class="alert alert-light mt-3 d-none" data-log-offcanvas-empty><?= Sanitize::html(mgwScheduledLang('scheduled-tasks-logs-empty-task', 'No executions recorded for this task yet.')) ?></div>
    </div>
</div>

document.addEventListener('DOMContentLoaded', function () {

