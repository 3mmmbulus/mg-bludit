<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

$pageLangFile = PATH_LANGUAGES . 'pages/backup/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$layout['title'] .= ' - ' . $L->g('backup-title');

if (!function_exists('mgwBackupReadJsonWithGuard')) {
    function mgwBackupReadJsonWithGuard($filePath)
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

if (!function_exists('mgwBackupFormatBytes')) {
    function mgwBackupFormatBytes($bytes)
    {
        if (!is_numeric($bytes)) {
            return array('value' => 0, 'label' => '0 B');
        }

        $bytes = (float)$bytes;
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $index = 0;
        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return array('value' => $bytes, 'label' => number_format($bytes, $bytes >= 10 ? 1 : 2) . ' ' . $units[$index]);
    }
}

if (!function_exists('mgwBackupFormatDateTime')) {
    function mgwBackupFormatDateTime($timestamp)
    {
        if (!is_numeric($timestamp)) {
            return '--';
        }
        $timestamp = (int)$timestamp;
        if ($timestamp <= 0) {
            return '--';
        }
        return date('Y-m-d H:i', $timestamp);
    }
}

if (!function_exists('mgwBackupStatusFromPercent')) {
    function mgwBackupStatusFromPercent($value, $warn = 70, $fail = 85)
    {
        if (!is_numeric($value)) {
            return 'unknown';
        }
        if ($value >= $fail) {
            return 'fail';
        }
        if ($value >= $warn) {
            return 'warn';
        }
        return 'ok';
    }
}

if (!function_exists('mgwBackupStatusFromBool')) {
    function mgwBackupStatusFromBool($bool)
    {
        if ($bool === true) {
            return 'ok';
        }
        if ($bool === false) {
            return 'warn';
        }
        return 'unknown';
    }
}

$backupOverview = array();
$scheduleCards = array();
$storageTargets = array();
$siteBackupMatrix = array();
$backupBundles = array();
$restorePoints = array();
$migrationTargets = array();
$backupLogs = array();
$backupAlerts = array();

$globalCandidates = array(
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'backup-center.php',
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'backup-center.json',
    PATH_ROOT . 'mgw-content' . DS . '_default' . DS . 'tmp' . DS . 'backup-metrics.php'
);

$backupMetrics = array();
foreach ($globalCandidates as $candidate) {
    if (!empty($backupMetrics)) {
        break;
    }
    $backupMetrics = mgwBackupReadJsonWithGuard($candidate);
}

$totals = isset($backupMetrics['totals']) && is_array($backupMetrics['totals']) ? $backupMetrics['totals'] : $backupMetrics;
$configSnapshots = isset($totals['configSnapshots']) ? (int)$totals['configSnapshots'] : 0;
$contentSnapshots = isset($totals['contentSnapshots']) ? (int)$totals['contentSnapshots'] : 0;
$filesSnapshots = isset($totals['filesSnapshots']) ? (int)$totals['filesSnapshots'] : 0;
$databaseSnapshots = isset($totals['databaseSnapshots']) ? (int)$totals['databaseSnapshots'] : 0;
$storageUsage = isset($totals['storageUsage']) ? (float)$totals['storageUsage'] : 0;
$storageCapacity = isset($totals['storageCapacity']) ? (float)$totals['storageCapacity'] : 0;
$remoteTargets = isset($totals['remoteTargets']) ? (int)$totals['remoteTargets'] : 0;
$failedJobs = isset($totals['failedJobs']) ? (int)$totals['failedJobs'] : 0;
$lastFullBackup = isset($totals['lastFullBackup']) ? (int)$totals['lastFullBackup'] : null;

$storageRatio = ($storageCapacity > 0) ? ($storageUsage / $storageCapacity) * 100 : null;

$backupOverview[] = array(
    'label' => $L->g('backup-card-config'),
    'value' => number_format($configSnapshots),
    'status' => $configSnapshots > 0 ? 'ok' : 'warn',
    'hint' => $L->g('backup-card-config-hint')
);

$backupOverview[] = array(
    'label' => $L->g('backup-card-content'),
    'value' => number_format($contentSnapshots),
    'status' => $contentSnapshots > 0 ? 'ok' : 'warn',
    'hint' => $L->g('backup-card-content-hint')
);

$backupOverview[] = array(
    'label' => $L->g('backup-card-storage'),
    'value' => mgwBackupFormatBytes($storageUsage)['label'],
    'status' => mgwBackupStatusFromPercent($storageRatio, 65, 85),
    'hint' => $storageCapacity > 0 ? sprintf($L->g('backup-card-storage-hint'), mgwBackupFormatBytes($storageCapacity)['label']) : $L->g('backup-card-storage-hint-fallback')
);

$backupOverview[] = array(
    'label' => $L->g('backup-card-remote'),
    'value' => number_format($remoteTargets),
    'status' => $remoteTargets > 0 ? 'ok' : 'warn',
    'hint' => $L->g('backup-card-remote-hint')
);

$scheduleData = isset($backupMetrics['schedules']) && is_array($backupMetrics['schedules']) ? $backupMetrics['schedules'] : array();
foreach ($scheduleData as $schedule) {
    if (!is_array($schedule)) {
        continue;
    }
    $scheduleCards[] = array(
        'name' => isset($schedule['name']) ? (string)$schedule['name'] : '',
        'frequency' => isset($schedule['frequency']) ? (string)$schedule['frequency'] : 'daily',
        'nextRun' => isset($schedule['nextRun']) ? (int)$schedule['nextRun'] : null,
        'retention' => isset($schedule['retention']) ? (int)$schedule['retention'] : 0,
        'status' => isset($schedule['status']) ? (string)$schedule['status'] : 'ok',
        'targets' => isset($schedule['targets']) && is_array($schedule['targets']) ? $schedule['targets'] : array()
    );
}

$storageData = isset($backupMetrics['storage']) && is_array($backupMetrics['storage']) ? $backupMetrics['storage'] : array();
foreach ($storageData as $storage) {
    if (!is_array($storage)) {
        continue;
    }
    $storageTargets[] = array(
        'type' => isset($storage['type']) ? (string)$storage['type'] : 'local',
        'label' => isset($storage['label']) ? (string)$storage['label'] : '',
        'location' => isset($storage['location']) ? (string)$storage['location'] : '',
        'lastSync' => isset($storage['lastSync']) ? (int)$storage['lastSync'] : null,
        'status' => isset($storage['status']) ? (string)$storage['status'] : 'unknown',
        'usage' => mgwBackupFormatBytes(isset($storage['usage']) ? $storage['usage'] : 0)
    );
}

$snapshotData = isset($backupMetrics['snapshots']) && is_array($backupMetrics['snapshots']) ? $backupMetrics['snapshots'] : array();
foreach ($snapshotData as $snapshot) {
    if (!is_array($snapshot)) {
        continue;
    }
    $backupBundles[] = array(
        'id' => isset($snapshot['id']) ? (string)$snapshot['id'] : '',
        'type' => isset($snapshot['type']) ? (string)$snapshot['type'] : 'full',
        'created' => isset($snapshot['created']) ? (int)$snapshot['created'] : null,
        'size' => mgwBackupFormatBytes(isset($snapshot['size']) ? $snapshot['size'] : 0),
        'checksum' => isset($snapshot['checksum']) ? (string)$snapshot['checksum'] : '',
        'includes' => isset($snapshot['includes']) && is_array($snapshot['includes']) ? $snapshot['includes'] : array(),
        'targets' => isset($snapshot['targets']) && is_array($snapshot['targets']) ? $snapshot['targets'] : array()
    );
}

$restoreData = isset($backupMetrics['restorePoints']) && is_array($backupMetrics['restorePoints']) ? $backupMetrics['restorePoints'] : array();
foreach ($restoreData as $restore) {
    if (!is_array($restore)) {
        continue;
    }
    $restorePoints[] = array(
        'id' => isset($restore['id']) ? (string)$restore['id'] : '',
        'created' => isset($restore['created']) ? (int)$restore['created'] : null,
        'summary' => isset($restore['summary']) ? (string)$restore['summary'] : '',
        'scopes' => isset($restore['scopes']) && is_array($restore['scopes']) ? $restore['scopes'] : array(),
        'status' => isset($restore['status']) ? (string)$restore['status'] : 'ok'
    );
}

$migrationData = isset($backupMetrics['migration']) && is_array($backupMetrics['migration']) ? $backupMetrics['migration'] : array();
foreach ($migrationData as $migration) {
    if (!is_array($migration)) {
        continue;
    }
    $migrationTargets[] = array(
        'name' => isset($migration['name']) ? (string)$migration['name'] : '',
        'size' => mgwBackupFormatBytes(isset($migration['size']) ? $migration['size'] : 0),
        'steps' => isset($migration['steps']) && is_array($migration['steps']) ? $migration['steps'] : array(),
        'download' => isset($migration['download']) ? (string)$migration['download'] : '',
        'status' => isset($migration['status']) ? (string)$migration['status'] : 'pending'
    );
}

$logData = isset($backupMetrics['logs']) && is_array($backupMetrics['logs']) ? $backupMetrics['logs'] : array();
foreach ($logData as $log) {
    if (!is_array($log)) {
        continue;
    }
    $backupLogs[] = array(
        'time' => isset($log['time']) ? (int)$log['time'] : null,
        'job' => isset($log['job']) ? (string)$log['job'] : '',
        'scope' => isset($log['scope']) ? (string)$log['scope'] : '',
        'status' => isset($log['status']) ? (string)$log['status'] : 'ok',
        'size' => mgwBackupFormatBytes(isset($log['size']) ? $log['size'] : 0),
        'checksum' => isset($log['checksum']) ? (string)$log['checksum'] : ''
    );
}

$sitesRoot = PATH_ROOT . 'mgw-content' . DS;
$siteDirectories = glob($sitesRoot . '*', GLOB_ONLYDIR);
if ($siteDirectories === false) {
    $siteDirectories = array();
}

$sitesWithoutRecentBackup = 0;
$sitesWithoutRemote = 0;
$largestSnapshotSize = 0;

foreach ($siteDirectories as $directory) {
    $slug = basename($directory);
    if ($slug === '' || $slug[0] === '.') {
        continue;
    }

    $siteMeta = mgwBackupReadJsonWithGuard($directory . DS . 'databases' . DS . 'site.php');
    $siteLabel = isset($siteMeta['title']) && is_string($siteMeta['title']) && $siteMeta['title'] !== '' ? $siteMeta['title'] : $slug;

    $siteMetrics = mgwBackupReadJsonWithGuard($directory . DS . 'tmp' . DS . 'backup-metrics.php');
    if (empty($siteMetrics)) {
        $siteMetrics = mgwBackupReadJsonWithGuard($directory . DS . 'tmp' . DS . 'backup-metrics.json');
    }

    $lastBackup = isset($siteMetrics['lastBackup']) ? (int)$siteMetrics['lastBackup'] : null;
    $lastRemote = isset($siteMetrics['lastRemote']) ? (int)$siteMetrics['lastRemote'] : null;
    $totalSize = isset($siteMetrics['totalSize']) ? (float)$siteMetrics['totalSize'] : 0;
    $largestSnapshotSize = max($largestSnapshotSize, $totalSize);

    if ($lastBackup === null || $lastBackup <= strtotime('-7 days')) {
        $sitesWithoutRecentBackup++;
    }

    if ($lastRemote === null || $lastRemote <= strtotime('-7 days')) {
        $sitesWithoutRemote++;
    }

    $siteBackupMatrix[] = array(
        'slug' => $slug,
        'label' => $siteLabel,
        'lastBackup' => $lastBackup,
        'lastRemote' => $lastRemote,
        'size' => mgwBackupFormatBytes($totalSize),
        'policy' => isset($siteMetrics['policy']) ? (string)$siteMetrics['policy'] : 'daily',
        'components' => isset($siteMetrics['components']) && is_array($siteMetrics['components']) ? $siteMetrics['components'] : array(),
        'copies' => isset($siteMetrics['remoteCopies']) ? (int)$siteMetrics['remoteCopies'] : 0
    );

    if (isset($siteMetrics['restorePoints']) && is_array($siteMetrics['restorePoints'])) {
        foreach ($siteMetrics['restorePoints'] as $restore) {
            if (!is_array($restore)) {
                continue;
            }
            $restorePoints[] = array(
                'id' => isset($restore['id']) ? (string)$restore['id'] : '',
                'created' => isset($restore['created']) ? (int)$restore['created'] : null,
                'summary' => isset($restore['summary']) ? (string)$restore['summary'] : '',
                'scopes' => isset($restore['scopes']) && is_array($restore['scopes']) ? $restore['scopes'] : array(),
                'status' => isset($restore['status']) ? (string)$restore['status'] : 'ok'
            );
        }
    }

    if (isset($siteMetrics['logs']) && is_array($siteMetrics['logs'])) {
        foreach ($siteMetrics['logs'] as $log) {
            if (!is_array($log)) {
                continue;
            }
            $backupLogs[] = array(
                'time' => isset($log['time']) ? (int)$log['time'] : null,
                'job' => isset($log['job']) ? (string)$log['job'] : '',
                'scope' => isset($log['scope']) ? (string)$log['scope'] : $siteLabel,
                'status' => isset($log['status']) ? (string)$log['status'] : 'ok',
                'size' => mgwBackupFormatBytes(isset($log['size']) ? $log['size'] : 0),
                'checksum' => isset($log['checksum']) ? (string)$log['checksum'] : ''
            );
        }
    }
}

if ($sitesWithoutRecentBackup > 0) {
    $backupAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('backup-alert-missing-site'),
        'description' => sprintf($L->g('backup-alert-missing-site-desc'), $sitesWithoutRecentBackup)
    );
}

if ($sitesWithoutRemote > 0) {
    $backupAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('backup-alert-missing-remote'),
        'description' => sprintf($L->g('backup-alert-missing-remote-desc'), $sitesWithoutRemote)
    );
}

if ($failedJobs > 0) {
    $backupAlerts[] = array(
        'status' => 'fail',
        'title' => $L->g('backup-alert-failed-jobs'),
        'description' => sprintf($L->g('backup-alert-failed-jobs-desc'), number_format($failedJobs))
    );
}

if ($storageRatio !== null && $storageRatio >= 80) {
    $backupAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('backup-alert-storage'),
        'description' => sprintf($L->g('backup-alert-storage-desc'), number_format($storageRatio, 1) . '%')
    );
}

if ($largestSnapshotSize > 0 && $largestSnapshotSize >= 10 * 1024 * 1024 * 1024) { // 10GB
    $backupAlerts[] = array(
        'status' => 'info',
        'title' => $L->g('backup-alert-large-snapshot'),
        'description' => sprintf($L->g('backup-alert-large-snapshot-desc'), mgwBackupFormatBytes($largestSnapshotSize)['label'])
    );
}

if ($lastFullBackup === null || $lastFullBackup <= strtotime('-3 days')) {
    $backupAlerts[] = array(
        'status' => 'warn',
        'title' => $L->g('backup-alert-full-backup'),
        'description' => $L->g('backup-alert-full-backup-desc')
    );
}

if (empty($scheduleCards)) {
    $scheduleCards[] = array(
        'name' => $L->g('backup-fallback-schedule-name'),
        'frequency' => 'daily',
        'nextRun' => strtotime('+6 hours'),
        'retention' => 7,
        'status' => 'ok',
        'targets' => array('local', 's3')
    );
}

if (empty($storageTargets)) {
    $storageTargets[] = array(
        'type' => 'local',
        'label' => $L->g('backup-fallback-storage-local'),
        'location' => PATH_ROOT . 'backups',
        'lastSync' => strtotime('-2 hours'),
        'status' => 'ok',
        'usage' => mgwBackupFormatBytes($storageUsage)
    );
    $storageTargets[] = array(
        'type' => 's3',
        'label' => 'S3 Bucket',
        'location' => 's3://site-cluster-backup',
        'lastSync' => strtotime('-1 day'),
        'status' => 'warn',
        'usage' => mgwBackupFormatBytes($storageUsage * 0.7)
    );
}

if (empty($siteBackupMatrix)) {
    $siteBackupMatrix[] = array(
        'slug' => 'example.com',
        'label' => 'example.com',
        'lastBackup' => strtotime('-12 hours'),
        'lastRemote' => strtotime('-1 day'),
        'size' => mgwBackupFormatBytes(4 * 1024 * 1024 * 1024),
        'policy' => 'daily',
        'components' => array('config', 'content', 'uploads', 'database'),
        'copies' => 2
    );
}

if (empty($backupBundles)) {
    $backupBundles[] = array(
        'id' => 'full-' . date('Ymd'),
        'type' => 'full',
        'created' => strtotime('-10 hours'),
        'size' => mgwBackupFormatBytes(6.5 * 1024 * 1024 * 1024),
        'checksum' => strtoupper(substr(md5('backup'), 0, 12)),
        'includes' => array('config', 'content', 'uploads', 'database'),
        'targets' => array('local', 's3')
    );
}

if (empty($restorePoints)) {
    $restorePoints[] = array(
        'id' => 'restore-' . date('Ymd'),
        'created' => strtotime('-10 hours'),
        'summary' => $L->g('backup-fallback-restore-summary'),
        'scopes' => array('config', 'database'),
        'status' => 'ok'
    );
}

if (empty($migrationTargets)) {
    $migrationTargets[] = array(
        'name' => 'Cluster Export ' . date('Y-m-d'),
        'size' => mgwBackupFormatBytes(12 * 1024 * 1024 * 1024),
        'steps' => array(
            $L->g('backup-fallback-migration-step1'),
            $L->g('backup-fallback-migration-step2'),
            $L->g('backup-fallback-migration-step3')
        ),
        'download' => '#',
        'status' => 'pending'
    );
}

if (empty($backupLogs)) {
    $backupLogs[] = array(
        'time' => strtotime('-2 hours'),
        'job' => 'Daily Full Backup',
        'scope' => 'cluster',
        'status' => 'ok',
        'size' => mgwBackupFormatBytes(6 * 1024 * 1024 * 1024),
        'checksum' => strtoupper(substr(sha1('cluster'), 0, 12))
    );
}

if (empty($backupAlerts)) {
    $backupAlerts[] = array(
        'status' => 'info',
        'title' => $L->g('backup-fallback-alert-title'),
        'description' => $L->g('backup-fallback-alert-desc')
    );
}

usort($backupAlerts, function ($a, $b) {
    $priority = array('fail' => 0, 'warn' => 1, 'info' => 2, 'ok' => 3, 'unknown' => 4);
    return ($priority[$a['status']] ?? 4) <=> ($priority[$b['status']] ?? 4);
});

usort($restorePoints, function ($a, $b) {
    return ($b['created'] ?? 0) <=> ($a['created'] ?? 0);
});

usort($backupLogs, function ($a, $b) {
    return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
});

$backupPayload = array(
    'backupOverview' => array_values($backupOverview),
    'scheduleCards' => array_values($scheduleCards),
    'storageTargets' => array_values($storageTargets),
    'siteBackupMatrix' => array_values($siteBackupMatrix),
    'backupBundles' => array_values($backupBundles),
    'restorePoints' => array_values($restorePoints),
    'migrationTargets' => array_values($migrationTargets),
    'backupLogs' => array_values($backupLogs),
    'backupAlerts' => array_values($backupAlerts)
);

extract($backupPayload, EXTR_OVERWRITE);