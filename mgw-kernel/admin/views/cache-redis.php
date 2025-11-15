<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$redisOverview = isset($redisOverview) && is_array($redisOverview) ? $redisOverview : array();
$redisPerformanceCards = isset($redisPerformanceCards) && is_array($redisPerformanceCards) ? $redisPerformanceCards : array();
$redisSlowlog = isset($redisSlowlog) && is_array($redisSlowlog) ? $redisSlowlog : array();
$localCacheSummary = isset($localCacheSummary) && is_array($localCacheSummary) ? $localCacheSummary : array();
$localCacheTools = isset($localCacheTools) && is_array($localCacheTools) ? $localCacheTools : array();
$siteCacheMatrix = isset($siteCacheMatrix) && is_array($siteCacheMatrix) ? $siteCacheMatrix : array();
$prewarmJobs = isset($prewarmJobs) && is_array($prewarmJobs) ? $prewarmJobs : array();
$expirationPolicies = isset($expirationPolicies) && is_array($expirationPolicies) ? $expirationPolicies : array();
$cacheKeySamples = isset($cacheKeySamples) && is_array($cacheKeySamples) ? $cacheKeySamples : array();
$cacheAlerts = isset($cacheAlerts) && is_array($cacheAlerts) ? $cacheAlerts : array();
$cacheHeatmap = isset($cacheHeatmap) && is_array($cacheHeatmap) ? $cacheHeatmap : array();
$namespaceBreakdown = isset($namespaceBreakdown) && is_array($namespaceBreakdown) ? $namespaceBreakdown : array();

if (!function_exists('cacheRedisStatusBadgeClass')) {
    function cacheRedisStatusBadgeClass($status)
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

if (!function_exists('cacheRedisStatusLabel')) {
    function cacheRedisStatusLabel($status, $language)
    {
        $key = 'cache-redis-status-' . $status;
        return $language->g($key) ?: strtoupper($status);
    }
}

if (!function_exists('cacheRedisFormatPercent')) {
    function cacheRedisFormatPercent($value)
    {
        if (!is_numeric($value)) {
            return '--';
        }
        return number_format((float)$value, 1) . '%';
    }
}

if (!function_exists('cacheRedisFormatBytesLabel')) {
    function cacheRedisFormatBytesLabel($bytes)
    {
        if (is_array($bytes) && isset($bytes['label'])) {
            return $bytes['label'];
        }
        if (!is_numeric($bytes)) {
            return '--';
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

if (!function_exists('cacheRedisRenderSparkline')) {
    function cacheRedisRenderSparkline($values)
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

if (!function_exists('cacheRedisFormatDuration')) {
    function cacheRedisFormatDuration($microseconds)
    {
        if (!is_numeric($microseconds)) {
            return '--';
        }
        $ms = (float)$microseconds;
        if ($ms >= 1000) {
            return number_format($ms / 1000, 2) . ' ms';
        }
        return number_format($ms, 2) . ' μs';
    }
}

if (!function_exists('cacheRedisFormatTtl')) {
    function cacheRedisFormatTtl($ttl)
    {
        if (!is_numeric($ttl)) {
            return '--';
        }
        $ttl = (int)$ttl;
        if ($ttl <= 0) {
            return '∞';
        }
        if ($ttl < 60) {
            return $ttl . ' s';
        }
        if ($ttl < 3600) {
            return round($ttl / 60) . ' min';
        }
        if ($ttl < 86400) {
            return round($ttl / 3600, 1) . ' h';
        }
        return round($ttl / 86400, 1) . ' d';
    }
}
?>

<div class="cache-redis-page container-fluid px-0">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1 d-flex align-items-center gap-2">
                <span class="bi bi-memory"></span>
                <span><?php echo $L->g('cache-redis-title'); ?></span>
            </h2>
            <p class="text-muted mb-0 small"><?php echo $L->g('cache-redis-subtitle'); ?></p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?php echo Sanitize::html(HTML_PATH_ADMIN_ROOT . 'cache-redis'); ?>" class="btn btn-outline-primary">
                <span class="bi bi-arrow-repeat"></span>
                <span class="ms-1"><?php echo $L->g('cache-redis-refresh'); ?></span>
            </a>
            <a href="#" class="btn btn-primary">
                <span class="bi bi-lightning"></span>
                <span class="ms-1"><?php echo $L->g('cache-redis-flush-all'); ?></span>
            </a>
            <a href="#" class="btn btn-outline-danger">
                <span class="bi bi-fire"></span>
                <span class="ms-1"><?php echo $L->g('cache-redis-flush-redis'); ?></span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <?php if (!empty($redisOverview)): ?>
                    <?php foreach ($redisOverview as $card): ?>
                        <div class="col-sm-6 col-lg-3">
                            <div class="cache-overview-card border rounded-3 h-100 p-3">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <div class="text-muted small text-uppercase fw-semibold"><?php echo Sanitize::html($card['label']); ?></div>
                                        <div class="fs-3 fw-semibold mt-2 text-truncate" title="<?php echo Sanitize::html($card['value']); ?>"><?php echo Sanitize::html($card['value']); ?></div>
                                    </div>
                                    <span class="badge <?php echo cacheRedisStatusBadgeClass(isset($card['status']) ? $card['status'] : 'unknown'); ?>">
                                        <?php echo Sanitize::html(cacheRedisStatusLabel(isset($card['status']) ? $card['status'] : 'unknown', $L)); ?>
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
                        <div class="alert alert-warning mb-0"><?php echo Sanitize::html($L->g('cache-redis-overview-empty')); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('cache-redis-performance-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('cache-redis-performance-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($redisPerformanceCards)): ?>
                        <div class="row g-3">
                            <?php foreach ($redisPerformanceCards as $card): ?>
                                <div class="col-sm-6">
                                    <div class="cache-performance-card border rounded-3 p-3 h-100">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div>
                                                <div class="text-muted small mb-1"><?php echo Sanitize::html($card['label']); ?></div>
                                                <div class="fs-4 fw-semibold"><?php echo Sanitize::html($card['value']); ?></div>
                                            </div>
                                            <span class="badge <?php echo cacheRedisStatusBadgeClass(isset($card['status']) ? $card['status'] : 'unknown'); ?>">
                                                <?php echo Sanitize::html(cacheRedisStatusLabel(isset($card['status']) ? $card['status'] : 'unknown', $L)); ?>
                                            </span>
                                        </div>
                                        <div class="mt-3">
                                            <?php echo cacheRedisRenderSparkline(isset($card['trend']) ? $card['trend'] : array()); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0"><?php echo Sanitize::html($L->g('cache-redis-performance-empty')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('cache-redis-namespaces-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('cache-redis-namespaces-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($namespaceBreakdown)): ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($namespaceBreakdown as $entry): ?>
                                <li class="d-flex align-items-center justify-content-between gap-2 py-2">
                                    <span class="text-truncate" title="<?php echo Sanitize::html($entry['namespace']); ?>"><?php echo Sanitize::html($entry['namespace']); ?></span>
                                    <span class="badge bg-light text-dark border"><?php echo number_format((int)$entry['keys']); ?> <?php echo $L->g('cache-redis-keys'); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-info mb-0"><?php echo Sanitize::html($L->g('cache-redis-namespaces-empty')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('cache-redis-slowlog-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('cache-redis-slowlog-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 90px;">#</th>
                            <th><?php echo $L->g('cache-redis-slowlog-command'); ?></th>
                            <th style="width: 160px;"><?php echo $L->g('cache-redis-slowlog-duration'); ?></th>
                            <th style="width: 180px;"><?php echo $L->g('cache-redis-slowlog-at'); ?></th>
                            <th style="width: 140px;"><?php echo $L->g('cache-redis-slowlog-database'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($redisSlowlog)): ?>
                            <?php foreach (array_slice($redisSlowlog, 0, 12) as $idx => $entry): ?>
                                <tr>
                                    <td class="text-muted">#<?php echo $idx + 1; ?></td>
                                    <td class="small">
                                        <code><?php echo Sanitize::html(is_array(isset($entry['command']) ? $entry['command'] : (isset($entry['args']) ? $entry['args'] : array())) ? implode(' ', (array)(isset($entry['command']) ? $entry['command'] : $entry['args'])) : (isset($entry['command']) ? $entry['command'] : '')); ?></code>
                                    </td>
                                    <td class="fw-semibold text-danger">
                                        <?php echo cacheRedisFormatDuration(isset($entry['duration']) ? $entry['duration'] : (isset($entry['duration_us']) ? $entry['duration_us'] : null)); ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php $ts = isset($entry['time']) ? $entry['time'] : (isset($entry['start_time']) ? $entry['start_time'] : null); ?>
                                        <?php echo $ts ? date('Y-m-d H:i:s', (int)$ts) : '--'; ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo isset($entry['database']) ? Sanitize::html((string)$entry['database']) : (isset($entry['db']) ? Sanitize::html((string)$entry['db']) : '0'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <span class="bi bi-speedometer2 fs-1 d-block mb-2"></span>
                                    <span><?php echo $L->g('cache-redis-slowlog-empty'); ?></span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('cache-redis-local-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('cache-redis-local-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach (array('file', 'object', 'opcode') as $type): ?>
                            <?php if (!isset($localCacheSummary[$type])) { continue; } ?>
                            <?php $data = $localCacheSummary[$type]; ?>
                            <div class="col-12">
                                <div class="cache-local-card border rounded-3 p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('cache-redis-local-' . $type); ?></div>
                                            <div class="fs-5 fw-semibold mt-2"><?php echo cacheRedisFormatBytesLabel(isset($data['size']) ? $data['size'] : 0); ?></div>
                                        </div>
                                        <div class="text-end small text-muted">
                                            <?php if (isset($data['items'])): ?>
                                                <div><?php echo $L->g('cache-redis-items'); ?>: <span class="fw-semibold"><?php echo number_format((int)$data['items']); ?></span></div>
                                            <?php endif; ?>
                                            <?php if ($type === 'opcode' && isset($data['hitRate'])): ?>
                                                <div><?php echo $L->g('cache-redis-hit-rate'); ?>: <span class="fw-semibold"><?php echo cacheRedisFormatPercent($data['hitRate']); ?></span></div>
                                            <?php endif; ?>
                                            <?php if ($type === 'file' && !empty($data['path'])): ?>
                                                <div class="text-truncate" title="<?php echo Sanitize::html($data['path']); ?>"><?php echo Sanitize::html($data['path']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pt-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($localCacheTools as $tool): ?>
                            <div class="list-group-item d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold"><?php echo Sanitize::html($tool['label']); ?></div>
                                    <div class="small text-muted"><?php echo Sanitize::html($tool['description']); ?></div>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm" type="button"><?php echo $L->g('cache-redis-run-tool'); ?></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('cache-redis-site-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('cache-redis-site-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('cache-redis-site-table-site'); ?></th>
                                    <th><?php echo $L->g('cache-redis-site-table-namespace'); ?></th>
                                    <th class="text-center" style="width: 140px;"><?php echo $L->g('cache-redis-site-table-keys'); ?></th>
                                    <th class="text-center" style="width: 160px;"><?php echo $L->g('cache-redis-site-table-size'); ?></th>
                                    <th class="text-center" style="width: 140px;"><?php echo $L->g('cache-redis-site-table-hit'); ?></th>
                                    <th class="text-center" style="width: 120px;"><?php echo $L->g('cache-redis-site-table-actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($siteCacheMatrix)): ?>
                                    <?php foreach ($siteCacheMatrix as $row): ?>
                                        <tr>
                                            <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html($row['label']); ?>"><?php echo Sanitize::html($row['label']); ?></td>
                                            <td class="text-muted small text-truncate" title="<?php echo Sanitize::html($row['redisNamespace']); ?>"><?php echo Sanitize::html($row['redisNamespace']); ?></td>
                                            <td class="text-center fw-semibold"><?php echo number_format((int)$row['redisKeys']); ?></td>
                                            <td class="text-center text-muted small"><?php echo cacheRedisFormatBytesLabel($row['localSize']); ?></td>
                                            <td class="text-center">
                                                <?php if (isset($row['pageCacheHit'])): ?>
                                                    <span class="badge <?php echo cacheRedisStatusBadgeClass($row['pageCacheHit'] >= 80 ? 'ok' : ($row['pageCacheHit'] >= 60 ? 'warn' : 'fail')); ?>"><?php echo cacheRedisFormatPercent($row['pageCacheHit']); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary">--</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-outline-primary" type="button" title="<?php echo Sanitize::html($L->g('cache-redis-site-warm')); ?>">
                                                        <span class="bi bi-rocket"></span>
                                                    </button>
                                                    <button class="btn btn-outline-danger" type="button" title="<?php echo Sanitize::html($L->g('cache-redis-site-flush')); ?>">
                                                        <span class="bi bi-trash"></span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4"><?php echo $L->g('cache-redis-site-empty'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="small text-muted">
                        <?php echo $L->g('cache-redis-site-footer'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('cache-redis-prewarm-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('cache-redis-prewarm-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($prewarmJobs)): ?>
                            <?php foreach ($prewarmJobs as $job): ?>
                                <div class="list-group-item">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold text-truncate"><?php echo Sanitize::html($job['site']); ?></div>
                                            <div class="small text-muted mt-1">
                                                <span class="badge bg-light text-dark border me-1"><span class="bi bi-hash"></span> <?php echo Sanitize::html($job['pattern']); ?></span>
                                                <?php if (isset($job['cached'])): ?>
                                                    <span class="badge bg-primary-subtle text-primary">+<?php echo number_format((int)$job['cached']); ?> <?php echo $L->g('cache-redis-prewarm-cached'); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-end small">
                                            <span class="badge <?php echo cacheRedisStatusBadgeClass(isset($job['status']) ? $job['status'] : 'unknown'); ?>">
                                                <?php echo Sanitize::html(cacheRedisStatusLabel(isset($job['status']) ? $job['status'] : 'unknown', $L)); ?>
                                            </span>
                                            <?php if (isset($job['duration'])): ?>
                                                <div class="mt-1 text-muted"><?php echo number_format((float)$job['duration'], 2); ?> s</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center text-muted py-5">
                                <span class="bi bi-lightbulb fs-1 d-block mb-2"></span>
                                <span><?php echo $L->g('cache-redis-prewarm-empty'); ?></span>
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
                        <h5 class="mb-0"><?php echo $L->g('cache-redis-expiration-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('cache-redis-expiration-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('cache-redis-expiration-table-site'); ?></th>
                                    <th><?php echo $L->g('cache-redis-expiration-table-pattern'); ?></th>
                                    <th style="width: 140px;" class="text-center"><?php echo $L->g('cache-redis-expiration-table-ttl'); ?></th>
                                    <th style="width: 160px;" class="text-center"><?php echo $L->g('cache-redis-expiration-table-strategy'); ?></th>
                                    <th><?php echo $L->g('cache-redis-expiration-table-desc'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($expirationPolicies)): ?>
                                    <?php foreach ($expirationPolicies as $policy): ?>
                                        <tr>
                                            <td class="fw-semibold text-truncate"><?php echo Sanitize::html($policy['site']); ?></td>
                                            <td class="text-muted small text-truncate" title="<?php echo Sanitize::html($policy['pattern']); ?>"><?php echo Sanitize::html($policy['pattern']); ?></td>
                                            <td class="text-center fw-semibold"><?php echo cacheRedisFormatTtl($policy['ttl']); ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border"><?php echo Sanitize::html(Text::firstCharUp(isset($policy['strategy']) ? $policy['strategy'] : 'TTL')); ?></span>
                                            </td>
                                            <td class="small text-muted"><?php echo Sanitize::html($policy['description']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4"><?php echo $L->g('cache-redis-expiration-empty'); ?></td>
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
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><?php echo $L->g('cache-redis-keys-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('cache-redis-keys-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($cacheKeySamples)): ?>
                            <?php foreach (array_slice($cacheKeySamples, 0, 12) as $sample): ?>
                                <div class="list-group-item">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-truncate"><?php echo Sanitize::html($sample['key']); ?></div>
                                            <div class="small text-muted mt-1">
                                                <span class="badge bg-light text-dark border me-1"><span class="bi bi-diagram-3"></span> <?php echo Sanitize::html($sample['site']); ?></span>
                                                <span class="badge bg-primary-subtle text-primary me-1"><?php echo Sanitize::html($sample['type']); ?></span>
                                            </div>
                                        </div>
                                        <div class="text-end small text-muted">
                                            <div><?php echo cacheRedisFormatBytesLabel($sample['size']); ?></div>
                                            <div><?php echo cacheRedisFormatTtl($sample['ttl']); ?></div>
                                            <a href="#" class="d-inline-flex align-items-center gap-1 mt-1"><span class="bi bi-trash"></span><?php echo $L->g('cache-redis-keys-delete'); ?></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center text-muted py-5">
                                <span class="bi bi-clipboard-data fs-1 d-block mb-2"></span>
                                <span><?php echo $L->g('cache-redis-keys-empty'); ?></span>
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
                        <h5 class="mb-0"><?php echo $L->g('cache-redis-heatmap-title'); ?></h5>
                        <span class="text-muted small"><?php echo $L->g('cache-redis-heatmap-desc'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($cacheHeatmap)): ?>
                        <div class="cache-heatmap-grid">
                            <?php foreach ($cacheHeatmap as $site => $entries): ?>
                                <div class="cache-heatmap-card">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="fw-semibold text-truncate" title="<?php echo Sanitize::html($site); ?>"><?php echo Sanitize::html($site); ?></span>
                                        <a href="#" class="small text-muted"><?php echo $L->g('cache-redis-heatmap-view'); ?></a>
                                    </div>
                                    <div class="cache-heatmap-line">
                                        <?php if (is_array($entries)): ?>
                                            <?php $max = max(array_map(function ($v) { return is_numeric($v) ? (float)$v : 0; }, $entries)); ?>
                                            <?php $max = $max > 0 ? $max : 1; ?>
                                            <?php foreach ($entries as $value): ?>
                                                <?php $intensity = is_numeric($value) ? min(100, ((float)$value / $max) * 100) : 0; ?>
                                                <span style="--intensity: <?php echo number_format($intensity, 2); ?>%;"></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0"><?php echo Sanitize::html($L->g('cache-redis-heatmap-empty')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('cache-redis-alerts-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('cache-redis-alerts-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($cacheAlerts)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($cacheAlerts as $alert): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge <?php echo cacheRedisStatusBadgeClass($alert['status']); ?>">
                                    <span class="bi <?php echo $alert['status'] === 'fail' ? 'bi-x-octagon' : ($alert['status'] === 'warn' ? 'bi-exclamation-octagon' : 'bi-info-circle'); ?>"></span>
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
                    <span><?php echo $L->g('cache-redis-alerts-empty'); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>cache-redis.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">