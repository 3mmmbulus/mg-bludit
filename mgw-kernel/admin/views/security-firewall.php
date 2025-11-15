<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$firewallOverview = isset($firewallOverview) && is_array($firewallOverview) ? $firewallOverview : array();
$ipWhitelistEntries = isset($ipWhitelistEntries) && is_array($ipWhitelistEntries) ? $ipWhitelistEntries : array();
$ipBlacklistEntries = isset($ipBlacklistEntries) && is_array($ipBlacklistEntries) ? $ipBlacklistEntries : array();
$rateLimitMatrix = isset($rateLimitMatrix) && is_array($rateLimitMatrix) ? $rateLimitMatrix : array();
$geoAccessMatrix = isset($geoAccessMatrix) && is_array($geoAccessMatrix) ? $geoAccessMatrix : array();
$uaRuleMatrix = isset($uaRuleMatrix) && is_array($uaRuleMatrix) ? $uaRuleMatrix : array();
$pathGuardMatrix = isset($pathGuardMatrix) && is_array($pathGuardMatrix) ? $pathGuardMatrix : array();
$webhookMatrix = isset($webhookMatrix) && is_array($webhookMatrix) ? $webhookMatrix : array();
$firewallAlerts = isset($firewallAlerts) && is_array($firewallAlerts) ? $firewallAlerts : array();
$firewallRecommendations = isset($firewallRecommendations) && is_array($firewallRecommendations) ? $firewallRecommendations : array();

if (!function_exists('securityFirewallStatusBadgeClass')) {
    function securityFirewallStatusBadgeClass($status)
    {
        switch ($status) {
            case 'ok':
                return 'bg-success-subtle text-success';
            case 'fail':
                return 'bg-danger-subtle text-danger';
            case 'warn':
            default:
                return 'bg-warning-subtle text-warning';
        }
    }
}

if (!function_exists('securityFirewallStatusLabel')) {
    function securityFirewallStatusLabel($status, $language)
    {
        $key = 'security-firewall-status-' . $status;
        $label = $language->g($key);
        return $label !== false ? $label : strtoupper((string)$status);
    }
}

if (!function_exists('securityFirewallScopeLabel')) {
    function securityFirewallScopeLabel($scope, $language)
    {
        if ($scope === 'global') {
            return $language->g('security-firewall-scope-global');
        }
        if ($scope === 'admin') {
            return $language->g('security-firewall-scope-admin');
        }
        return $scope;
    }
}
?>

<div class="security-firewall-page container-fluid px-0">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1 d-flex align-items-center gap-2">
                <span class="bi bi-fire"></span>
                <span><?php echo $L->g('security-firewall-title'); ?></span>
            </h2>
            <p class="text-muted mb-0 small"><?php echo $L->g('security-firewall-subtitle'); ?></p>
        </div>
        <div>
            <a href="<?php echo Sanitize::html(HTML_PATH_ADMIN_ROOT . 'security-firewall'); ?>" class="btn btn-outline-primary">
                <span class="bi bi-arrow-repeat"></span>
                <span class="ms-1"><?php echo $L->g('security-firewall-refresh'); ?></span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('security-firewall-overview-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-firewall-overview-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($firewallOverview)): ?>
                <div class="row g-3">
                    <?php foreach ($firewallOverview as $card): ?>
                        <div class="col-md-6 col-xl-3">
                            <div class="firewall-overview-card border rounded-3 p-3 h-100">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div class="pe-2">
                                        <div class="fw-semibold" title="<?php echo Sanitize::html($card['title']); ?>">
                                            <?php echo Sanitize::html($card['title']); ?>
                                        </div>
                                        <div class="small text-muted mt-2">
                                            <?php echo Sanitize::html($card['message']); ?>
                                        </div>
                                    </div>
                                    <span class="badge <?php echo securityFirewallStatusBadgeClass($card['status']); ?>">
                                        <?php echo Sanitize::html(securityFirewallStatusLabel($card['status'], $L)); ?>
                                    </span>
                                </div>
                                <?php if (!empty($card['hint'])): ?>
                                    <div class="small text-muted mt-3"><?php echo Sanitize::html($card['hint']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($card['action']) && !empty($card['action']['url']) && !empty($card['action']['label'])): ?>
                                    <a href="<?php echo Sanitize::html($card['action']['url']); ?>" class="btn btn-link btn-sm px-0 mt-2">
                                        <?php echo Sanitize::html($card['action']['label']); ?>
                                        <span class="bi bi-arrow-up-right ms-1"></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0"><?php echo Sanitize::html($L->g('security-firewall-overview-empty')); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('security-firewall-section-ip'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-firewall-section-ip-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-12 col-xl-6">
                    <h6 class="fw-semibold mb-3"><?php echo $L->g('security-firewall-whitelist-title'); ?></h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('security-firewall-table-scope'); ?></th>
                                    <th><?php echo $L->g('security-firewall-table-ip'); ?></th>
                                    <th><?php echo $L->g('security-firewall-table-label'); ?></th>
                                    <th><?php echo $L->g('security-firewall-table-note'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ipWhitelistEntries)): ?>
                                    <?php foreach ($ipWhitelistEntries as $entry): ?>
                                        <tr>
                                            <td class="text-muted small"><?php echo Sanitize::html(securityFirewallScopeLabel($entry['scope'], $L)); ?></td>
                                            <td><code><?php echo Sanitize::html($entry['ip']); ?></code></td>
                                            <td><?php echo Sanitize::html($entry['label'] ?? ''); ?></td>
                                            <td class="text-muted small"><?php echo Sanitize::html($entry['note'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4"><?php echo $L->g('security-firewall-whitelist-empty'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <h6 class="fw-semibold mb-3"><?php echo $L->g('security-firewall-blacklist-title'); ?></h6>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $L->g('security-firewall-table-scope'); ?></th>
                                    <th><?php echo $L->g('security-firewall-table-ip'); ?></th>
                                    <th class="text-center" style="width: 120px;">#</th>
                                    <th class="text-center" style="width: 200px;"><?php echo $L->g('security-firewall-table-last-seen'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ipBlacklistEntries)): ?>
                                    <?php foreach ($ipBlacklistEntries as $entry): ?>
                                        <tr>
                                            <td class="text-muted small"><?php echo Sanitize::html(securityFirewallScopeLabel($entry['scope'], $L)); ?></td>
                                            <td><code><?php echo Sanitize::html($entry['ip']); ?></code></td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border"><?php echo isset($entry['failures']) ? (int)$entry['failures'] : 0; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($entry['lastFailure'])): ?>
                                                    <span class="small text-muted"><?php echo Sanitize::html(mgwSecurityFirewallFormatTimestamp($entry['lastFailure'])); ?></span>
                                                <?php else: ?>
                                                    <span class="small text-muted"><?php echo $L->g('security-firewall-not-available'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4"><?php echo $L->g('security-firewall-blacklist-empty'); ?></td>
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
                <h5 class="mb-0"><?php echo $L->g('security-firewall-section-rate'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-firewall-section-rate-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('security-firewall-table-site'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('security-firewall-table-allowed'); ?></th>
                            <th class="text-center" style="width: 160px;"><?php echo $L->g('security-firewall-table-window'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('security-firewall-table-blocked'); ?></th>
                            <th class="text-center" style="width: 200px;"><?php echo $L->g('security-firewall-table-last-event'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('security-firewall-table-status'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rateLimitMatrix)): ?>
                            <?php foreach ($rateLimitMatrix as $row): ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo Sanitize::html($row['site']); ?></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?php echo (int)$row['failuresAllowed']; ?></span></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?php echo (int)$row['minutesBlocked']; ?></span></td>
                                    <td class="text-center"><span class="badge <?php echo $row['blockedCount'] > 0 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success'; ?>"><?php echo (int)$row['blockedCount']; ?></span></td>
                                    <td class="text-center">
                                        <?php if (!empty($row['lastBlocked'])): ?>
                                            <span class="small text-muted"><?php echo Sanitize::html(mgwSecurityFirewallFormatTimestamp($row['lastBlocked'])); ?></span>
                                        <?php else: ?>
                                            <span class="small text-muted"><?php echo $L->g('security-firewall-not-available'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo securityFirewallStatusBadgeClass($row['status']); ?>"><?php echo Sanitize::html(securityFirewallStatusLabel($row['status'], $L)); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5"><?php echo $L->g('security-firewall-rate-empty'); ?></td>
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
                <h5 class="mb-0"><?php echo $L->g('security-firewall-section-geo'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-firewall-section-geo-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('security-firewall-table-scope'); ?></th>
                            <th><?php echo $L->g('security-firewall-table-mode'); ?></th>
                            <th><?php echo $L->g('security-firewall-table-blocked-countries'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('security-firewall-table-status'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($geoAccessMatrix)): ?>
                            <?php foreach ($geoAccessMatrix as $row): ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo Sanitize::html(securityFirewallScopeLabel($row['scope'], $L)); ?></td>
                                    <td><?php echo Sanitize::html($row['mode'] ?? $L->g('security-firewall-not-configured')); ?></td>
                                    <td>
                                        <?php if (!empty($row['blockedCountries'])): ?>
                                            <span class="badge bg-light text-dark border"><?php echo (int)$row['blockedCount']; ?></span>
                                            <span class="text-muted small ms-2"><?php echo Sanitize::html(implode(', ', (array)$row['blockedCountries'])); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small"><?php echo $L->g('security-firewall-not-configured'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo securityFirewallStatusBadgeClass($row['status'] ?? 'warn'); ?>"><?php echo Sanitize::html(securityFirewallStatusLabel($row['status'] ?? 'warn', $L)); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5"><?php echo $L->g('security-firewall-geo-empty'); ?></td>
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
                <h5 class="mb-0"><?php echo $L->g('security-firewall-section-ua'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-firewall-section-ua-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('security-firewall-table-scope'); ?></th>
                            <th><?php echo $L->g('security-firewall-table-pattern'); ?></th>
                            <th><?php echo $L->g('security-firewall-table-action'); ?></th>
                            <th><?php echo $L->g('security-firewall-table-note'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($uaRuleMatrix)): ?>
                            <?php foreach ($uaRuleMatrix as $row): ?>
                                <tr>
                                    <td class="text-muted small"><?php echo Sanitize::html(securityFirewallScopeLabel($row['scope'], $L)); ?></td>
                                    <td><code><?php echo Sanitize::html($row['pattern']); ?></code></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo Sanitize::html($row['action']); ?></span></td>
                                    <td class="text-muted small"><?php echo Sanitize::html($row['note'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4"><?php echo $L->g('security-firewall-ua-empty'); ?></td>
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
                <h5 class="mb-0"><?php echo $L->g('security-firewall-section-paths'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-firewall-section-paths-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('security-firewall-table-scope'); ?></th>
                            <th><?php echo $L->g('security-firewall-table-path'); ?></th>
                            <th><?php echo $L->g('security-firewall-table-guard'); ?></th>
                            <th><?php echo $L->g('security-firewall-table-note'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('security-firewall-table-status'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pathGuardMatrix)): ?>
                            <?php foreach ($pathGuardMatrix as $row): ?>
                                <tr>
                                    <td class="text-muted small"><?php echo Sanitize::html(securityFirewallScopeLabel($row['scope'], $L)); ?></td>
                                    <td><code><?php echo Sanitize::html($row['path']); ?></code></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo Sanitize::html($row['action']); ?></span></td>
                                    <td class="text-muted small"><?php echo Sanitize::html($row['note'] ?? ''); ?></td>
                                    <td class="text-center">
                                        <span class="badge <?php echo securityFirewallStatusBadgeClass($row['status']); ?>"><?php echo Sanitize::html(securityFirewallStatusLabel($row['status'], $L)); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4"><?php echo $L->g('security-firewall-paths-empty'); ?></td>
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
                <h5 class="mb-0"><?php echo $L->g('security-firewall-section-webhooks'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-firewall-section-webhooks-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('security-firewall-table-scope'); ?></th>
                            <th><?php echo $L->g('security-firewall-table-endpoint'); ?></th>
                            <th><?php echo $L->g('security-firewall-table-events'); ?></th>
                            <th><?php echo $L->g('security-firewall-table-secret'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($webhookMatrix)): ?>
                            <?php foreach ($webhookMatrix as $row): ?>
                                <tr>
                                    <td class="text-muted small"><?php echo Sanitize::html(securityFirewallScopeLabel($row['scope'], $L)); ?></td>
                                    <td>
                                        <a href="<?php echo Sanitize::html($row['endpoint']); ?>" target="_blank" rel="noopener" class="text-decoration-none">
                                            <?php echo Sanitize::html($row['endpoint']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['events'])): ?>
                                            <span class="badge bg-light text-dark border"><?php echo count((array)$row['events']); ?></span>
                                            <span class="text-muted small ms-2"><?php echo Sanitize::html(implode(', ', (array)$row['events'])); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small"><?php echo $L->g('security-firewall-not-configured'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['secret'])): ?>
                                            <code><?php echo Sanitize::html($row['secret']); ?></code>
                                        <?php else: ?>
                                            <span class="text-muted small"><?php echo $L->g('security-firewall-not-available'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4"><?php echo $L->g('security-firewall-webhooks-empty'); ?></td>
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
                <h5 class="mb-0"><?php echo $L->g('security-firewall-section-alerts'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-firewall-section-alerts-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($firewallAlerts)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($firewallAlerts as $alert): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
                                <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center">
                                    <span class="badge <?php echo securityFirewallStatusBadgeClass($alert['status']); ?>"><?php echo Sanitize::html(securityFirewallStatusLabel($alert['status'], $L)); ?></span>
                                    <div>
                                        <div class="fw-semibold">
                                            <?php echo Sanitize::html($alert['title']); ?>
                                            <span class="text-muted">· <?php echo Sanitize::html($alert['site']); ?></span>
                                        </div>
                                        <?php if (!empty($alert['notes'])): ?>
                                            <div class="small text-muted mt-1"><?php echo Sanitize::html($alert['notes']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-lg-end">
                                    <?php if (!empty($alert['method'])): ?>
                                        <span class="badge bg-light text-dark border me-2"><?php echo Sanitize::html(strtoupper($alert['method'])); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($alert['date'])): ?>
                                        <span class="small text-muted"><?php echo Sanitize::html($alert['date']); ?></span>
                                    <?php else: ?>
                                        <span class="small text-muted"><?php echo $L->g('security-firewall-not-available'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-success mb-0 d-flex align-items-center gap-2">
                    <span class="bi bi-check-circle"></span>
                    <span><?php echo $L->g('security-firewall-alerts-empty'); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('security-firewall-section-recommendations'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-firewall-section-recommendations-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($firewallRecommendations)): ?>
                <div class="row g-3">
                    <?php foreach ($firewallRecommendations as $recommendation): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="recommendation-card border rounded-3 h-100 p-3 status-<?php echo Sanitize::html($recommendation['status']); ?>">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="recommendation-icon rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <span class="bi <?php echo Sanitize::html($recommendation['icon']); ?>"></span>
                                    </span>
                                    <div>
                                        <div class="fw-semibold mb-1"><?php echo Sanitize::html($recommendation['title']); ?></div>
                                        <p class="small text-muted mb-0"><?php echo Sanitize::html($recommendation['body']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0"><?php echo Sanitize::html($L->g('security-firewall-overview-empty')); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>security-firewall.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">