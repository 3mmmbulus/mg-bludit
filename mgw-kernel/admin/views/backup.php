<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$backupOverview = isset($backupOverview) && is_array($backupOverview) ? $backupOverview : array();
$scheduleCards = isset($scheduleCards) && is_array($scheduleCards) ? $scheduleCards : array();
$storageTargets = isset($storageTargets) && is_array($storageTargets) ? $storageTargets : array();
$siteBackupMatrix = isset($siteBackupMatrix) && is_array($siteBackupMatrix) ? $siteBackupMatrix : array();
$backupBundles = isset($backupBundles) && is_array($backupBundles) ? $backupBundles : array();
$restorePoints = isset($restorePoints) && is_array($restorePoints) ? $restorePoints : array();
$migrationTargets = isset($migrationTargets) && is_array($migrationTargets) ? $migrationTargets : array();
$backupLogs = isset($backupLogs) && is_array($backupLogs) ? $backupLogs : array();
$backupAlerts = isset($backupAlerts) && is_array($backupAlerts) ? $backupAlerts : array();

if (!function_exists('backupStatusBadgeClass')) {
    function backupStatusBadgeClass($status)
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

if (!function_exists('backupStatusIcon')) {
    function backupStatusIcon($status)
    {
        switch ($status) {
            case 'ok':
                return 'bi-check-circle';
            case 'warn':
                return 'bi-exclamation-triangle';
            case 'fail':
                return 'bi-x-octagon';
            case 'info':
                return 'bi-info-circle';
            default:
                return 'bi-question-circle';
        }
    }
}

if (!function_exists('backupFormatDate')) {
    function backupFormatDate($timestamp)
    {
        if (!is_numeric($timestamp) || $timestamp <= 0) {
            return '--';
        }
        return date('Y-m-d H:i', (int)$timestamp);
    }
}

if (!function_exists('backupFormatFrequency')) {
    function backupFormatFrequency($frequency, $language)
    {
        $key = 'backup-frequency-' . strtolower((string)$frequency);
        $label = $language->g($key);
        return $label ?: strtoupper((string)$frequency);
    }
}

if (!function_exists('backupFormatBytesLabel')) {
    function backupFormatBytesLabel($bytes)
    {
        if (is_array($bytes) && isset($bytes['label'])) {
            return $bytes['label'];
        }
        if (!is_numeric($bytes)) {
            return '0 B';
        }
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $value = (float)$bytes;
        $idx = 0;
        while ($value >= 1024 && $idx < count($units) - 1) {
            $value /= 1024;
            $idx++;
        }
        return number_format($value, $value >= 10 ? 1 : 2) . ' ' . $units[$idx];
    }
}
?>

<div class="backup-page container-fluid px-0">
    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1 d-flex align-items-center gap-2">
                <span class="bi bi-hdd-network"></span>
                <span><?php echo $L->g('backup-title'); ?></span>
            </h2>
            <p class="text-muted mb-0 small"><?php echo $L->g('backup-subtitle'); ?></p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="#" class="btn btn-primary">
                <span class="bi bi-cloud-arrow-up"></span>
                <span class="ms-1"><?php echo $L->g('backup-action-new'); ?></span>
            </a>
            <a href="#" class="btn btn-outline-primary">
                <span class="bi bi-upload"></span>
                <span class="ms-1"><?php echo $L->g('backup-action-import'); ?></span>
            </a>
            <a href="#" class="btn btn-outline-secondary">
                <span class="bi bi-clock-history"></span>
                <span class="ms-1"><?php echo $L->g('backup-action-schedule'); ?></span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($backupOverview as $card): ?>
                    <div class="col-sm-6 col-xl-3">
                        <div class="backup-overview-card border rounded-3 h-100 p-3">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold"><?php echo Sanitize::html($card['label']); ?></div>
                                    <div class="fs-3 fw-semibold mt-2" title="<?php echo Sanitize::html($card['value']); ?>"><?php echo Sanitize::html($card['value']); ?></div>
                                </div>
                                <span class="badge <?php echo backupStatusBadgeClass(isset($card['status']) ? $card['status'] : 'unknown'); ?>">
                                    <span class="bi <?php echo backupStatusIcon(isset($card['status']) ? $card['status'] : 'unknown'); ?> me-1"></span>
                                    <?php echo $L->g('backup-status-' . (isset($card['status']) ? $card['status'] : 'unknown')); ?>
                                </span>
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
        <div class="col-xxl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('backup-schedule-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('backup-schedule-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($scheduleCards)): ?>
                        <div class="row g-3">
                            <?php foreach ($scheduleCards as $schedule): ?>
                                <div class="col-md-6">
                                    <div class="backup-schedule-card border rounded-3 p-3 h-100">
                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                            <div>
                                                <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($schedule['name']); ?>"><?php echo Sanitize::html($schedule['name']); ?></div>
                                                <div class="text-muted small mt-1"><?php echo backupFormatFrequency($schedule['frequency'], $L); ?></div>
                                            </div>
                                            <span class="badge <?php echo backupStatusBadgeClass(isset($schedule['status']) ? $schedule['status'] : 'unknown'); ?>">
                                                <?php echo $L->g('backup-status-' . (isset($schedule['status']) ? $schedule['status'] : 'unknown')); ?>
                                            </span>
                                        </div>
                                        <div class="mt-3 small text-muted">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span><?php echo $L->g('backup-schedule-next'); ?></span>
                                                <span class="fw-semibold"><?php echo backupFormatDate(isset($schedule['nextRun']) ? $schedule['nextRun'] : null); ?></span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-2">
                                                <span><?php echo $L->g('backup-schedule-retention'); ?></span>
                                                <span class="badge bg-light text-dark border"><?php echo number_format(isset($schedule['retention']) ? $schedule['retention'] : 0); ?> <?php echo $L->g('backup-schedule-days'); ?></span>
                                            </div>
                                            <?php if (!empty($schedule['targets'])): ?>
                                                <div class="mt-2 text-truncate" title="<?php echo Sanitize::html(implode(', ', $schedule['targets'])); ?>">
                                                    <span class="bi bi-send-check me-1"></span>
                                                    <?php echo Sanitize::html(implode(', ', $schedule['targets'])); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-3 d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary flex-grow-1" type="button"><?php echo $L->g('backup-schedule-run-now'); ?></button>
                                            <button class="btn btn-sm btn-outline-secondary" type="button"><?php echo $L->g('backup-schedule-edit'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0"><?php echo $L->g('backup-schedule-empty'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xxl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('backup-storage-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('backup-storage-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($storageTargets)): ?>
                        <div class="backup-storage-stack">
                            <?php foreach ($storageTargets as $target): ?>
                                <div class="backup-storage-item border rounded-3 p-3">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <div class="small text-uppercase text-muted fw-semibold"><?php echo Sanitize::html($target['type']); ?></div>
                                            <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($target['label']); ?>"><?php echo Sanitize::html($target['label']); ?></div>
                                            <div class="text-muted small text-truncate" title="<?php echo Sanitize::html($target['location']); ?>"><?php echo Sanitize::html($target['location']); ?></div>
                                        </div>
                                        <span class="badge <?php echo backupStatusBadgeClass(isset($target['status']) ? $target['status'] : 'unknown'); ?>">
                                            <?php echo $L->g('backup-status-' . (isset($target['status']) ? $target['status'] : 'unknown')); ?>
                                        </span>
                                    </div>
                                    <div class="mt-3 small text-muted d-flex align-items-center justify-content-between">
                                        <span><?php echo $L->g('backup-storage-usage'); ?></span>
                                        <span class="fw-semibold"><?php echo backupFormatBytesLabel(isset($target['usage']) ? $target['usage'] : 0); ?></span>
                                    </div>
                                    <div class="mt-2 small text-muted d-flex align-items-center justify-content-between">
                                        <span><?php echo $L->g('backup-storage-last-sync'); ?></span>
                                        <span class="fw-semibold"><?php echo backupFormatDate(isset($target['lastSync']) ? $target['lastSync'] : null); ?></span>
                                    </div>
                                    <div class="mt-3 d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-secondary flex-grow-1" type="button"><?php echo $L->g('backup-storage-sync'); ?></button>
                                        <button class="btn btn-sm btn-outline-danger" type="button"><?php echo $L->g('backup-storage-remove'); ?></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0"><?php echo $L->g('backup-storage-empty'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('backup-sites-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('backup-sites-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('backup-sites-table-site'); ?></th>
                            <th style="width: 160px;" class="text-center"><?php echo $L->g('backup-sites-table-last-local'); ?></th>
                            <th style="width: 160px;" class="text-center"><?php echo $L->g('backup-sites-table-last-remote'); ?></th>
                            <th style="width: 160px;" class="text-center"><?php echo $L->g('backup-sites-table-size'); ?></th>
                            <th style="width: 180px;" class="text-center"><?php echo $L->g('backup-sites-table-components'); ?></th>
                            <th style="width: 120px;" class="text-center"><?php echo $L->g('backup-sites-table-copies'); ?></th>
                            <th style="width: 160px;" class="text-center"><?php echo $L->g('backup-sites-table-policy'); ?></th>
                            <th style="width: 120px;" class="text-end"><?php echo $L->g('backup-sites-table-actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($siteBackupMatrix)): ?>
                            <?php foreach ($siteBackupMatrix as $row): ?>
                                <tr>
                                    <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html($row['label']); ?>"><?php echo Sanitize::html($row['label']); ?></td>
                                    <td class="text-center small text-muted"><?php echo backupFormatDate(isset($row['lastBackup']) ? $row['lastBackup'] : null); ?></td>
                                    <td class="text-center small text-muted"><?php echo backupFormatDate(isset($row['lastRemote']) ? $row['lastRemote'] : null); ?></td>
                                    <td class="text-center small fw-semibold"><?php echo backupFormatBytesLabel(isset($row['size']) ? $row['size'] : 0); ?></td>
                                    <td class="text-center small text-muted">
                                        <?php if (!empty($row['components'])): ?>
                                            <span class="badge bg-light text-dark border text-uppercase">
                                                <?php echo Sanitize::html(implode(' | ', $row['components'])); ?>
                                            </span>
                                        <?php else: ?>
                                            --
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center small fw-semibold"><?php echo number_format(isset($row['copies']) ? $row['copies'] : 0); ?></td>
                                    <td class="text-center small text-muted text-uppercase"><?php echo Sanitize::html(isset($row['policy']) ? $row['policy'] : '--'); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary" type="button" title="<?php echo Sanitize::html($L->g('backup-sites-action-backup')); ?>">
                                                <span class="bi bi-cloud-arrow-up"></span>
                                            </button>
                                            <button class="btn btn-outline-secondary" type="button" title="<?php echo Sanitize::html($L->g('backup-sites-action-restore')); ?>">
                                                <span class="bi bi-arrow-counterclockwise"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4"><?php echo $L->g('backup-sites-empty'); ?></td>
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
                        <h5 class="mb-0"><?php echo $L->g('backup-bundles-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('backup-bundles-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('backup-bundles-table-id'); ?></th>
                                    <th style="width: 120px;" class="text-center"><?php echo $L->g('backup-bundles-table-type'); ?></th>
                                    <th style="width: 180px;" class="text-center"><?php echo $L->g('backup-bundles-table-created'); ?></th>
                                    <th style="width: 140px;" class="text-center"><?php echo $L->g('backup-bundles-table-size'); ?></th>
                                    <th style="width: 200px;" class="text-center"><?php echo $L->g('backup-bundles-table-includes'); ?></th>
                                    <th style="width: 140px;" class="text-center"><?php echo $L->g('backup-bundles-table-checksum'); ?></th>
                                    <th style="width: 160px;" class="text-end"><?php echo $L->g('backup-bundles-table-actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($backupBundles)): ?>
                                    <?php foreach ($backupBundles as $bundle): ?>
                                        <tr>
                                            <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html($bundle['id']); ?>"><?php echo Sanitize::html($bundle['id']); ?></td>
                                            <td class="text-center text-uppercase small"><?php echo Sanitize::html($bundle['type']); ?></td>
                                            <td class="text-center small text-muted"><?php echo backupFormatDate(isset($bundle['created']) ? $bundle['created'] : null); ?></td>
                                            <td class="text-center fw-semibold small"><?php echo backupFormatBytesLabel(isset($bundle['size']) ? $bundle['size'] : 0); ?></td>
                                            <td class="text-center small text-muted">
                                                <?php if (!empty($bundle['includes'])): ?>
                                                    <span class="badge bg-primary-subtle text-primary text-uppercase"><?php echo Sanitize::html(implode(' | ', $bundle['includes'])); ?></span>
                                                <?php else: ?>
                                                    --
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center small text-monospace text-muted" title="<?php echo Sanitize::html(isset($bundle['checksum']) ? $bundle['checksum'] : ''); ?>"><?php echo Sanitize::html(isset($bundle['checksum']) ? $bundle['checksum'] : '--'); ?></td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-outline-secondary" type="button" title="<?php echo Sanitize::html($L->g('backup-bundles-action-download')); ?>"><span class="bi bi-download"></span></button>
                                                    <button class="btn btn-outline-primary" type="button" title="<?php echo Sanitize::html($L->g('backup-bundles-action-restore')); ?>"><span class="bi bi-arrow-counterclockwise"></span></button>
                                                    <button class="btn btn-outline-danger" type="button" title="<?php echo Sanitize::html($L->g('backup-bundles-action-delete')); ?>"><span class="bi bi-trash"></span></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4"><?php echo $L->g('backup-bundles-empty'); ?></td>
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
                        <h5 class="mb-0"><?php echo $L->g('backup-restore-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('backup-restore-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($restorePoints)): ?>
                        <div class="backup-restore-timeline">
                            <?php foreach (array_slice($restorePoints, 0, 8) as $restore): ?>
                                <div class="backup-restore-item border rounded-3 p-3">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($restore['id']); ?>"><?php echo Sanitize::html($restore['id']); ?></div>
                                            <div class="small text-muted mt-1"><?php echo backupFormatDate(isset($restore['created']) ? $restore['created'] : null); ?></div>
                                            <?php if (!empty($restore['summary'])): ?>
                                                <div class="small text-muted mt-2"><?php echo Sanitize::html($restore['summary']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($restore['scopes'])): ?>
                                                <div class="mt-2">
                                                    <?php foreach ($restore['scopes'] as $scope): ?>
                                                        <span class="badge bg-light text-dark border text-uppercase me-1"><?php echo Sanitize::html($scope); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge <?php echo backupStatusBadgeClass(isset($restore['status']) ? $restore['status'] : 'unknown'); ?>"><?php echo $L->g('backup-status-' . (isset($restore['status']) ? $restore['status'] : 'unknown')); ?></span>
                                    </div>
                                    <div class="mt-3 d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary flex-grow-1" type="button"><?php echo $L->g('backup-restore-apply'); ?></button>
                                        <button class="btn btn-sm btn-outline-secondary" type="button"><?php echo $L->g('backup-restore-download'); ?></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0"><?php echo $L->g('backup-restore-empty'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('backup-migration-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('backup-migration-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($migrationTargets)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($migrationTargets as $migration): ?>
                                <div class="list-group-item">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($migration['name']); ?>"><?php echo Sanitize::html($migration['name']); ?></div>
                                            <div class="small text-muted mt-1"><?php echo backupFormatBytesLabel(isset($migration['size']) ? $migration['size'] : 0); ?></div>
                                            <?php if (!empty($migration['steps'])): ?>
                                                <ol class="backup-migration-steps mt-2 mb-0">
                                                    <?php foreach ($migration['steps'] as $step): ?>
                                                        <li><?php echo Sanitize::html($step); ?></li>
                                                    <?php endforeach; ?>
                                                </ol>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge <?php echo backupStatusBadgeClass(isset($migration['status']) ? $migration['status'] : 'info'); ?>"><?php echo $L->g('backup-status-' . (isset($migration['status']) ? $migration['status'] : 'info')); ?></span>
                                    </div>
                                    <div class="mt-3 d-flex gap-2">
                                        <a href="<?php echo Sanitize::html(isset($migration['download']) ? $migration['download'] : '#'); ?>" class="btn btn-sm btn-outline-secondary flex-grow-1">
                                            <span class="bi bi-download me-1"></span><?php echo $L->g('backup-migration-download'); ?>
                                        </a>
                                        <button class="btn btn-sm btn-outline-primary" type="button"><?php echo $L->g('backup-migration-import'); ?></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0"><?php echo $L->g('backup-migration-empty'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('backup-logs-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('backup-logs-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 180px;"><?php echo $L->g('backup-logs-table-time'); ?></th>
                                    <th><?php echo $L->g('backup-logs-table-job'); ?></th>
                                    <th style="width: 160px;" class="text-center"><?php echo $L->g('backup-logs-table-scope'); ?></th>
                                    <th style="width: 140px;" class="text-center"><?php echo $L->g('backup-logs-table-size'); ?></th>
                                    <th style="width: 160px;" class="text-center"><?php echo $L->g('backup-logs-table-checksum'); ?></th>
                                    <th style="width: 100px;" class="text-center"><?php echo $L->g('backup-logs-table-status'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($backupLogs)): ?>
                                    <?php foreach (array_slice($backupLogs, 0, 12) as $log): ?>
                                        <tr>
                                            <td class="small text-muted"><?php echo backupFormatDate(isset($log['time']) ? $log['time'] : null); ?></td>
                                            <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html($log['job']); ?>"><?php echo Sanitize::html($log['job']); ?></td>
                                            <td class="text-center small text-muted text-uppercase"><?php echo Sanitize::html(isset($log['scope']) ? $log['scope'] : '--'); ?></td>
                                            <td class="text-center small fw-semibold"><?php echo backupFormatBytesLabel(isset($log['size']) ? $log['size'] : 0); ?></td>
                                            <td class="text-center small text-monospace text-muted" title="<?php echo Sanitize::html(isset($log['checksum']) ? $log['checksum'] : ''); ?>"><?php echo Sanitize::html(isset($log['checksum']) ? $log['checksum'] : '--'); ?></td>
                                            <td class="text-center">
                                                <span class="badge <?php echo backupStatusBadgeClass(isset($log['status']) ? $log['status'] : 'unknown'); ?>">
                                                    <?php echo $L->g('backup-status-' . (isset($log['status']) ? $log['status'] : 'unknown')); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4"><?php echo $L->g('backup-logs-empty'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('backup-alerts-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('backup-alerts-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($backupAlerts)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($backupAlerts as $alert): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge <?php echo backupStatusBadgeClass(isset($alert['status']) ? $alert['status'] : 'unknown'); ?>">
                                    <span class="bi <?php echo backupStatusIcon(isset($alert['status']) ? $alert['status'] : 'unknown'); ?>"></span>
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
                    <span><?php echo $L->g('backup-alerts-empty'); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>backup.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">
