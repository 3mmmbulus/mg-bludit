<?php defined('MAIGEWAN') or die('Maigewan CMS.');

$tasksLanguageDir = PATH_LANGUAGES.'pages'.DS.'scheduled-tasks'.DS;
$tasksLanguageFiles = array();

$defaultTasksLanguage = $tasksLanguageDir.DEFAULT_LANGUAGE_FILE;
if (Sanitize::pathFile($defaultTasksLanguage)) {
    $tasksLanguageFiles[] = $defaultTasksLanguage;
}

$currentTasksLanguage = $tasksLanguageDir.$L->currentLanguage().'.json';
if ($currentTasksLanguage !== $defaultTasksLanguage && Sanitize::pathFile($currentTasksLanguage)) {
    $tasksLanguageFiles[] = $currentTasksLanguage;
}

if (!empty($tasksLanguageFiles)) {
    $tasksDictionary = array();
    foreach ($tasksLanguageFiles as $languageFile) {
        $languageData = new dbJSON($languageFile, false);
        if (!empty($languageData->db) && is_array($languageData->db)) {
            $tasksDictionary = array_merge($tasksDictionary, $languageData->db);
        }
    }

    if (!empty($tasksDictionary)) {
        $L->add($tasksDictionary);
    }
}

$layout['title'] .= ' - '.$L->g('scheduled-tasks-page-title');

$taskCategories = array(
    'content' => array('label' => $L->g('scheduled-tasks-category-content'), 'icon' => 'journal-text'),
    'site' => array('label' => $L->g('scheduled-tasks-category-site'), 'icon' => 'grid-3x3-gap'),
    'security' => array('label' => $L->g('scheduled-tasks-category-security'), 'icon' => 'shield-lock'),
    'backup' => array('label' => $L->g('scheduled-tasks-category-backup'), 'icon' => 'hdd-stack'),
    'automation' => array('label' => $L->g('scheduled-tasks-category-automation'), 'icon' => 'cpu')
);

$taskSites = array(
    array('id' => 'all', 'label' => $L->g('scheduled-tasks-site-all'), 'tag' => 'GLOBAL'),
    array('id' => '1dun-co', 'label' => '1dun.co', 'tag' => 'PROD'),
    array('id' => '1dun-net', 'label' => '1dun.net', 'tag' => 'PROD'),
    array('id' => 'www-1dun-net', 'label' => 'www.1dun.net', 'tag' => 'EDGE'),
    array('id' => 'sandbox', 'label' => $L->g('scheduled-tasks-site-sandbox'), 'tag' => 'LAB')
);

$taskOverview = array(
    array(
        'label' => $L->g('scheduled-tasks-metric-runs-today'),
        'value' => 128,
        'delta' => '+12%',
        'deltaVariant' => 'up'
    ),
    array(
        'label' => $L->g('scheduled-tasks-metric-running'),
        'value' => 7,
        'delta' => $L->g('scheduled-tasks-metric-running-meta'),
        'deltaVariant' => 'steady'
    ),
    array(
        'label' => $L->g('scheduled-tasks-metric-queued'),
        'value' => 19,
        'delta' => '-4',
        'deltaVariant' => 'down'
    ),
    array(
        'label' => $L->g('scheduled-tasks-metric-failed'),
        'value' => 2,
        'delta' => $L->g('scheduled-tasks-metric-failed-meta'),
        'deltaVariant' => 'alert'
    )
);

$queueMetrics = array(
    'maxConcurrency' => 5,
    'currentConcurrency' => 3,
    'queueDepth' => 19,
    'averageLatency' => 14,
    'retryPolicy' => array('maxRetries' => 5, 'backoff' => 'exponential')
);

$alerts = array(
    'hasAlerts' => true,
    'items' => array(
        array(
            'level' => 'danger',
            'title' => $L->g('scheduled-tasks-alert-failures-title'),
            'message' => $L->g('scheduled-tasks-alert-failures-message'),
            'meta' => 'content-sync-global x3'
        ),
        array(
            'level' => 'warning',
            'title' => $L->g('scheduled-tasks-alert-latency-title'),
            'message' => $L->g('scheduled-tasks-alert-latency-message'),
            'meta' => 'site-index-regeneration'
        )
    )
);

$taskList = array(
    array(
        'id' => 'content-sync-global',
        'name' => $L->g('scheduled-tasks-task-content-sync'),
        'type' => 'content',
        'site' => 'all',
        'siteLabel' => $L->g('scheduled-tasks-site-all'),
        'frequency' => '0 */1 * * *',
        'frequencyLabel' => $L->g('scheduled-tasks-frequency-hourly'),
        'lastRun' => strtotime('-12 minutes'),
        'nextRun' => strtotime('+48 minutes'),
        'status' => 'running',
        'priority' => 'high',
        'retries' => 1,
        'maxRetries' => 5,
        'queuePosition' => null,
        'duration' => 92,
        'owner' => 'automation@maigewan.com'
    ),
    array(
        'id' => 'site-cache-warm-1dun',
        'name' => $L->g('scheduled-tasks-task-cache-warm'),
        'type' => 'site',
        'site' => '1dun-co',
        'siteLabel' => '1dun.co',
        'frequency' => '30 2 * * *',
        'frequencyLabel' => $L->g('scheduled-tasks-frequency-daily'),
        'lastRun' => strtotime('yesterday 02:30'),
        'nextRun' => strtotime('tomorrow 02:30'),
        'status' => 'queued',
        'priority' => 'medium',
        'retries' => 0,
        'maxRetries' => 3,
        'queuePosition' => 4,
        'duration' => 215,
        'owner' => 'ops@maigewan.com'
    ),
    array(
        'id' => 'security-scan-sandbox',
        'name' => $L->g('scheduled-tasks-task-security-scan'),
        'type' => 'security',
        'site' => 'sandbox',
        'siteLabel' => $L->g('scheduled-tasks-site-sandbox'),
        'frequency' => '0 */4 * * *',
        'frequencyLabel' => $L->g('scheduled-tasks-frequency-every-4h'),
        'lastRun' => strtotime('-5 hours'),
        'nextRun' => strtotime('-1 hours'),
        'status' => 'failed',
        'priority' => 'high',
        'retries' => 3,
        'maxRetries' => 3,
        'queuePosition' => null,
        'duration' => 301,
        'owner' => 'security@maigewan.com'
    ),
    array(
        'id' => 'backup-nightly-www',
        'name' => $L->g('scheduled-tasks-task-backup-nightly'),
        'type' => 'backup',
        'site' => 'www-1dun-net',
        'siteLabel' => 'www.1dun.net',
        'frequency' => '0 3 * * *',
        'frequencyLabel' => $L->g('scheduled-tasks-frequency-nightly'),
        'lastRun' => strtotime('today 03:00'),
        'nextRun' => strtotime('tomorrow 03:00'),
        'status' => 'success',
        'priority' => 'medium',
        'retries' => 0,
        'maxRetries' => 5,
        'queuePosition' => null,
        'duration' => 612,
        'owner' => 'backup@maigewan.com'
    ),
    array(
        'id' => 'content-audit-compare',
        'name' => $L->g('scheduled-tasks-task-content-audit'),
        'type' => 'content',
        'site' => '1dun-net',
        'siteLabel' => '1dun.net',
        'frequency' => '15 */6 * * *',
        'frequencyLabel' => $L->g('scheduled-tasks-frequency-every-6h'),
        'lastRun' => strtotime('-3 hours 15 minutes'),
        'nextRun' => strtotime('+2 hours 45 minutes'),
        'status' => 'paused',
        'priority' => 'low',
        'retries' => 0,
        'maxRetries' => 2,
        'queuePosition' => null,
        'duration' => 154,
        'owner' => 'editorial@maigewan.com'
    )
);

$taskLogs = array(
    array(
        'taskId' => 'security-scan-sandbox',
        'taskName' => $L->g('scheduled-tasks-task-security-scan'),
        'siteLabel' => $L->g('scheduled-tasks-site-sandbox'),
        'status' => 'failed',
        'startedAt' => strtotime('-1 hours 12 minutes'),
        'finishedAt' => strtotime('-1 hours 7 minutes'),
        'duration' => 300,
        'trigger' => 'automatic',
        'logExcerpt' => $L->g('scheduled-tasks-log-security-failure')
    ),
    array(
        'taskId' => 'content-sync-global',
        'taskName' => $L->g('scheduled-tasks-task-content-sync'),
        'siteLabel' => $L->g('scheduled-tasks-site-all'),
        'status' => 'success',
        'startedAt' => strtotime('-15 minutes'),
        'finishedAt' => strtotime('-13 minutes'),
        'duration' => 120,
        'trigger' => 'automatic',
        'logExcerpt' => $L->g('scheduled-tasks-log-content-sync-success')
    ),
    array(
        'taskId' => 'backup-nightly-www',
        'taskName' => $L->g('scheduled-tasks-task-backup-nightly'),
        'siteLabel' => 'www.1dun.net',
        'status' => 'success',
        'startedAt' => strtotime('today 03:00'),
        'finishedAt' => strtotime('today 03:10'),
        'duration' => 600,
        'trigger' => 'automatic',
        'logExcerpt' => $L->g('scheduled-tasks-log-backup-success')
    ),
    array(
        'taskId' => 'content-audit-compare',
        'taskName' => $L->g('scheduled-tasks-task-content-audit'),
        'siteLabel' => '1dun.net',
        'status' => 'paused',
        'startedAt' => strtotime('-1 day 3 hours'),
        'finishedAt' => strtotime('-1 day 2 hours 55 minutes'),
        'duration' => 300,
        'trigger' => 'manual',
        'logExcerpt' => $L->g('scheduled-tasks-log-content-audit-paused')
    )
);

$cronPresets = array(
    array('label' => $L->g('scheduled-tasks-frequency-every-15m'), 'value' => '*/15 * * * *'),
    array('label' => $L->g('scheduled-tasks-frequency-hourly'), 'value' => '0 * * * *'),
    array('label' => $L->g('scheduled-tasks-frequency-every-4h'), 'value' => '0 */4 * * *'),
    array('label' => $L->g('scheduled-tasks-frequency-daily'), 'value' => '0 2 * * *'),
    array('label' => $L->g('scheduled-tasks-frequency-nightly'), 'value' => '0 3 * * *'),
    array('label' => $L->g('scheduled-tasks-frequency-weekly'), 'value' => '0 2 * * 1')
);

$taskParameterSchemas = array(
    'content' => array(
        array(
            'name' => 'contentScope',
            'type' => 'select',
            'label' => $L->g('scheduled-tasks-param-contentScope-label'),
            'help' => $L->g('scheduled-tasks-param-contentScope-help'),
            'options' => array(
                array('value' => 'all-content', 'label' => $L->g('scheduled-tasks-param-contentScope-option-all')),
                array('value' => 'staged-only', 'label' => $L->g('scheduled-tasks-param-contentScope-option-staged')),
                array('value' => 'featured-only', 'label' => $L->g('scheduled-tasks-param-contentScope-option-featured'))
            )
        ),
        array(
            'name' => 'includeMedia',
            'type' => 'checkbox',
            'label' => $L->g('scheduled-tasks-param-includeMedia-label'),
            'help' => $L->g('scheduled-tasks-param-includeMedia-help'),
            'default' => true
        ),
        array(
            'name' => 'notifyEditor',
            'type' => 'checkbox',
            'label' => $L->g('scheduled-tasks-param-notifyEditor-label'),
            'help' => $L->g('scheduled-tasks-param-notifyEditor-help'),
            'default' => false
        )
    ),
    'site' => array(
        array(
            'name' => 'targetEnvironment',
            'type' => 'select',
            'label' => $L->g('scheduled-tasks-param-targetEnvironment-label'),
            'help' => $L->g('scheduled-tasks-param-targetEnvironment-help'),
            'options' => array(
                array('value' => 'production', 'label' => $L->g('scheduled-tasks-param-targetEnvironment-option-production')),
                array('value' => 'staging', 'label' => $L->g('scheduled-tasks-param-targetEnvironment-option-staging'))
            )
        ),
        array(
            'name' => 'purgeCache',
            'type' => 'checkbox',
            'label' => $L->g('scheduled-tasks-param-purgeCache-label'),
            'help' => $L->g('scheduled-tasks-param-purgeCache-help'),
            'default' => true
        ),
        array(
            'name' => 'rebuildIndex',
            'type' => 'checkbox',
            'label' => $L->g('scheduled-tasks-param-rebuildIndex-label'),
            'help' => $L->g('scheduled-tasks-param-rebuildIndex-help'),
            'default' => true
        )
    ),
    'security' => array(
        array(
            'name' => 'scanDepth',
            'type' => 'select',
            'label' => $L->g('scheduled-tasks-param-scanDepth-label'),
            'help' => $L->g('scheduled-tasks-param-scanDepth-help'),
            'options' => array(
                array('value' => 'quick', 'label' => $L->g('scheduled-tasks-param-scanDepth-option-quick')),
                array('value' => 'standard', 'label' => $L->g('scheduled-tasks-param-scanDepth-option-standard')),
                array('value' => 'deep', 'label' => $L->g('scheduled-tasks-param-scanDepth-option-deep'))
            )
        ),
        array(
            'name' => 'notifyOps',
            'type' => 'checkbox',
            'label' => $L->g('scheduled-tasks-param-notifyOps-label'),
            'help' => $L->g('scheduled-tasks-param-notifyOps-help'),
            'default' => true
        ),
        array(
            'name' => 'autoMitigate',
            'type' => 'checkbox',
            'label' => $L->g('scheduled-tasks-param-autoMitigate-label'),
            'help' => $L->g('scheduled-tasks-param-autoMitigate-help'),
            'default' => false
        )
    ),
    'backup' => array(
        array(
            'name' => 'includeDatabase',
            'type' => 'checkbox',
            'label' => $L->g('scheduled-tasks-param-includeDatabase-label'),
            'help' => $L->g('scheduled-tasks-param-includeDatabase-help'),
            'default' => true
        ),
        array(
            'name' => 'includeUploads',
            'type' => 'checkbox',
            'label' => $L->g('scheduled-tasks-param-includeUploads-label'),
            'help' => $L->g('scheduled-tasks-param-includeUploads-help'),
            'default' => true
        ),
        array(
            'name' => 'retentionDays',
            'type' => 'number',
            'label' => $L->g('scheduled-tasks-param-retentionDays-label'),
            'help' => $L->g('scheduled-tasks-param-retentionDays-help'),
            'default' => 14
        )
    ),
    'automation' => array(
        array(
            'name' => 'payloadUrl',
            'type' => 'text',
            'label' => $L->g('scheduled-tasks-param-payloadUrl-label'),
            'help' => $L->g('scheduled-tasks-param-payloadUrl-help'),
            'placeholder' => $L->g('scheduled-tasks-param-payloadUrl-placeholder')
        ),
        array(
            'name' => 'httpMethod',
            'type' => 'select',
            'label' => $L->g('scheduled-tasks-param-httpMethod-label'),
            'help' => $L->g('scheduled-tasks-param-httpMethod-help'),
            'options' => array(
                array('value' => 'GET', 'label' => $L->g('scheduled-tasks-param-httpMethod-option-get')),
                array('value' => 'POST', 'label' => $L->g('scheduled-tasks-param-httpMethod-option-post')),
                array('value' => 'PUT', 'label' => $L->g('scheduled-tasks-param-httpMethod-option-put'))
            )
        ),
        array(
            'name' => 'requestBody',
            'type' => 'textarea',
            'label' => $L->g('scheduled-tasks-param-requestBody-label'),
            'help' => $L->g('scheduled-tasks-param-requestBody-help')
        )
    )
);

$taskStatuses = array(
    'running' => array('label' => $L->g('scheduled-tasks-status-running'), 'badge' => 'success'),
    'success' => array('label' => $L->g('scheduled-tasks-status-success'), 'badge' => 'primary'),
    'failed' => array('label' => $L->g('scheduled-tasks-status-failed'), 'badge' => 'danger'),
    'paused' => array('label' => $L->g('scheduled-tasks-status-paused'), 'badge' => 'warning'),
    'queued' => array('label' => $L->g('scheduled-tasks-status-queued'), 'badge' => 'secondary')
);

$taskPriorities = array(
    'high' => array('label' => $L->g('scheduled-tasks-priority-high'), 'badge' => 'danger'),
    'medium' => array('label' => $L->g('scheduled-tasks-priority-medium'), 'badge' => 'info'),
    'low' => array('label' => $L->g('scheduled-tasks-priority-low'), 'badge' => 'secondary')
);

$taskTriggers = array(
    'automatic' => $L->g('scheduled-tasks-trigger-automatic'),
    'manual' => $L->g('scheduled-tasks-trigger-manual')
);

$retryBackoffLabels = array(
    'exponential' => $L->g('scheduled-tasks-retry-backoff-exponential'),
    'linear' => $L->g('scheduled-tasks-retry-backoff-linear'),
    'fixed' => $L->g('scheduled-tasks-retry-backoff-fixed')
);

$taskFilters = array(
    'statuses' => array(
        array('value' => 'running', 'label' => $taskStatuses['running']['label']),
        array('value' => 'success', 'label' => $taskStatuses['success']['label']),
        array('value' => 'paused', 'label' => $taskStatuses['paused']['label']),
        array('value' => 'failed', 'label' => $taskStatuses['failed']['label']),
        array('value' => 'queued', 'label' => $taskStatuses['queued']['label'])
    ),
    'types' => array_values(array_map(function ($key, $category) {
        return array('value' => $key, 'label' => $category['label']);
    }, array_keys($taskCategories), $taskCategories)),
    'sites' => $taskSites
);

$taskActions = array(
    array('action' => 'run', 'label' => $L->g('scheduled-tasks-action-run-now'), 'icon' => 'play-circle'),
    array('action' => 'pause', 'label' => $L->g('scheduled-tasks-action-pause'), 'icon' => 'pause-circle'),
    array('action' => 'resume', 'label' => $L->g('scheduled-tasks-action-resume'), 'icon' => 'play-fill'),
    array('action' => 'logs', 'label' => $L->g('scheduled-tasks-action-view-logs'), 'icon' => 'file-text'),
    array('action' => 'edit', 'label' => $L->g('scheduled-tasks-action-edit'), 'icon' => 'pencil-square'),
    array('action' => 'duplicate', 'label' => $L->g('scheduled-tasks-action-duplicate'), 'icon' => 'files'),
    array('action' => 'delete', 'label' => $L->g('scheduled-tasks-action-delete'), 'icon' => 'trash3')
);

$logOverview = array(
    'successRate' => 97.6,
    'averageDuration' => 186,
    'failureWindow' => 'last 24h'
);

$taskTimeline = array(
    array(
        'time' => strtotime('-2 hours'),
        'taskName' => $L->g('scheduled-tasks-task-content-sync'),
        'siteLabel' => $L->g('scheduled-tasks-site-all'),
        'status' => 'success',
        'duration' => 110
    ),
    array(
        'time' => strtotime('-90 minutes'),
        'taskName' => $L->g('scheduled-tasks-task-security-scan'),
        'siteLabel' => $L->g('scheduled-tasks-site-sandbox'),
        'status' => 'failed',
        'duration' => 300
    ),
    array(
        'time' => strtotime('-60 minutes'),
        'taskName' => $L->g('scheduled-tasks-task-cache-warm'),
        'siteLabel' => '1dun.co',
        'status' => 'queued',
        'duration' => null
    ),
    array(
        'time' => strtotime('-20 minutes'),
        'taskName' => $L->g('scheduled-tasks-task-content-sync'),
        'siteLabel' => $L->g('scheduled-tasks-site-all'),
        'status' => 'running',
        'duration' => null
    )
);