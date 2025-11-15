<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$securityOverview = isset($securityOverview) && is_array($securityOverview) ? $securityOverview : array();
$siteBruteForceProfiles = isset($siteBruteForceProfiles) && is_array($siteBruteForceProfiles) ? $siteBruteForceProfiles : array();
$blacklistEntries = isset($blacklistEntries) && is_array($blacklistEntries) ? $blacklistEntries : array();
$userSecurityProfiles = isset($userSecurityProfiles) && is_array($userSecurityProfiles) ? $userSecurityProfiles : array();
$securityAlerts = isset($securityAlerts) && is_array($securityAlerts) ? $securityAlerts : array();
$securityRecommendations = isset($securityRecommendations) && is_array($securityRecommendations) ? $securityRecommendations : array();

if (!function_exists('securityLoginStatusBadgeClass')) {
    function securityLoginStatusBadgeClass($status)
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

if (!function_exists('securityLoginStatusLabel')) {
    function securityLoginStatusLabel($status, $language)
    {
        $key = 'security-login-status-' . $status;
        return $language->g($key) ?: strtoupper($status);
    }
}

if (!function_exists('securityLoginRoleLabel')) {
    function securityLoginRoleLabel($role, $language)
    {
        $map = array(
            'admin' => 'security-login-role-admin',
            'editor' => 'security-login-role-editor',
            'author' => 'security-login-role-author',
            'manager' => 'security-login-role-manager'
        );
        $roleKey = strtolower((string)$role);
        return isset($map[$roleKey]) ? $language->g($map[$roleKey]) : Text::firstCharUp($roleKey);
    }
}

if (!function_exists('securityLoginTokenBadgeClass')) {
    function securityLoginTokenBadgeClass($status)
    {
        switch ($status) {
            case 'valid':
                return 'bg-success-subtle text-success';
            case 'expiring':
                return 'bg-warning-subtle text-warning';
            case 'expired':
                return 'bg-danger-subtle text-danger';
            default:
                return 'bg-secondary-subtle text-secondary';
        }
    }
}
?>

<div class="security-login-page container-fluid px-0">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1 d-flex align-items-center gap-2">
                <span class="bi bi-shield-lock"></span>
                <span><?php echo $L->g('security-login-title'); ?></span>
            </h2>
            <p class="text-muted mb-0 small"><?php echo $L->g('security-login-subtitle'); ?></p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?php echo Sanitize::html(HTML_PATH_ADMIN_ROOT . 'security-login'); ?>" class="btn btn-outline-primary">
                <span class="bi bi-arrow-repeat"></span>
                <span class="ms-1"><?php echo $L->g('security-login-refresh'); ?></span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('security-login-overview-title'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-login-overview-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($securityOverview)): ?>
                <div class="row g-3">
                    <?php foreach ($securityOverview as $card): ?>
                        <div class="col-md-6 col-xl-3">
                            <div class="security-overview-card border rounded-3 p-3 h-100">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div class="pe-2">
                                        <div class="fw-semibold text-truncate" title="<?php echo Sanitize::html($card['title']); ?>">
                                            <?php echo Sanitize::html($card['title']); ?>
                                        </div>
                                        <div class="small text-muted mt-2">
                                            <?php echo Sanitize::html($card['message']); ?>
                                        </div>
                                    </div>
                                    <span class="badge <?php echo securityLoginStatusBadgeClass($card['status']); ?>">
                                        <?php echo Sanitize::html(securityLoginStatusLabel($card['status'], $L)); ?>
                                    </span>
                                </div>
                                <?php if (!empty($card['hint'])): ?>
                                    <div class="small text-muted mt-3">
                                        <?php echo Sanitize::html($card['hint']); ?>
                                    </div>
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
                <div class="alert alert-warning mb-0"><?php echo Sanitize::html($L->g('security-login-overview-empty')); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('security-login-section-users'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-login-section-users-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('security-login-users-table-username'); ?></th>
                            <th><?php echo $L->g('security-login-users-table-role'); ?></th>
                            <th><?php echo $L->g('security-login-users-table-email'); ?></th>
                            <th class="text-center" style="width: 160px;"><?php echo $L->g('security-login-users-table-remember'); ?></th>
                            <th class="text-center" style="width: 160px;"><?php echo $L->g('security-login-users-table-token'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('security-login-users-table-status'); ?></th>
                            <th style="min-width: 220px;"><?php echo $L->g('security-login-users-table-issues'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($userSecurityProfiles)): ?>
                            <?php foreach ($userSecurityProfiles as $profile): ?>
                                <tr>
                                    <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html($profile['username']); ?>">
                                        <?php echo Sanitize::html($profile['username']); ?>
                                    </td>
                                    <td><?php echo Sanitize::html(securityLoginRoleLabel($profile['role'], $L)); ?></td>
                                    <td>
                                        <?php if (!empty($profile['email'])): ?>
                                            <a href="mailto:<?php echo Sanitize::html($profile['email']); ?>" class="text-decoration-none">
                                                <?php echo Sanitize::html($profile['email']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted"><?php echo $L->g('security-login-not-available'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $profile['rememberActive'] ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'; ?>">
                                            <?php echo $profile['rememberActive'] ? $L->g('security-login-yes') : $L->g('security-login-no'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $tokenLabel = $profile['tokenAuthTTL'] !== '' ? $profile['tokenAuthTTL'] : $L->g('security-login-not-configured');
                                        $tokenClass = securityLoginTokenBadgeClass($profile['tokenStatus']);
                                        $tokenStatusKey = 'security-login-users-token-status-' . $profile['tokenStatus'];
                                        $tokenStatusText = $L->g($tokenStatusKey);
                                        ?>
                                        <span class="badge <?php echo $tokenClass; ?>" title="<?php echo Sanitize::html($tokenLabel); ?>">
                                            <?php echo Sanitize::html($tokenStatusText); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo securityLoginStatusBadgeClass($profile['status']); ?>">
                                            <?php echo Sanitize::html(securityLoginStatusLabel($profile['status'], $L)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($profile['issues'])): ?>
                                            <ul class="list-unstyled mb-0 small text-muted">
                                                <?php foreach ($profile['issues'] as $issue): ?>
                                                    <li class="d-flex align-items-start gap-2">
                                                        <span class="bi bi-exclamation-triangle text-warning"></span>
                                                        <span><?php echo Sanitize::html($issue); ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <span class="text-muted small"><?php echo $L->g('security-login-users-status-clean'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <span class="bi bi-people fs-1 d-block mb-2"></span>
                                    <span><?php echo $L->g('security-login-users-table-empty'); ?></span>
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
                <h5 class="mb-0"><?php echo $L->g('security-login-section-bruteforce'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-login-section-bruteforce-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('security-login-bruteforce-table-site'); ?></th>
                            <th class="text-center" style="width: 160px;"><?php echo $L->g('security-login-bruteforce-table-failures'); ?></th>
                            <th class="text-center" style="width: 160px;"><?php echo $L->g('security-login-bruteforce-table-minutes'); ?></th>
                            <th class="text-center" style="width: 160px;"><?php echo $L->g('security-login-bruteforce-table-blocked'); ?></th>
                            <th class="text-center" style="width: 200px;"><?php echo $L->g('security-login-bruteforce-table-last'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($siteBruteForceProfiles)): ?>
                            <?php foreach ($siteBruteForceProfiles as $item): ?>
                                <tr>
                                    <td class="fw-semibold text-truncate" title="<?php echo Sanitize::html($item['site']); ?>"><?php echo Sanitize::html($item['site']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?php echo (int)$item['failuresAllowed']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?php echo (int)$item['minutesBlocked']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $item['blockedCount'] > 0 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success'; ?>">
                                            <?php echo (int)$item['blockedCount']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($item['lastBlocked'])): ?>
                                            <span class="small text-muted"><?php echo Sanitize::html(date('Y-m-d H:i:s', (int)$item['lastBlocked'])); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small"><?php echo $L->g('security-login-not-available'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <span class="bi bi-shield fs-1 d-block mb-2"></span>
                                    <span><?php echo $L->g('security-login-bruteforce-empty'); ?></span>
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
                <h5 class="mb-0"><?php echo $L->g('security-login-section-blocklist'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-login-section-blocklist-desc'); ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $L->g('security-login-blocklist-table-ip'); ?></th>
                            <th class="text-center" style="width: 140px;"><?php echo $L->g('security-login-blocklist-table-failures'); ?></th>
                            <th class="text-center" style="width: 200px;"><?php echo $L->g('security-login-blocklist-table-last'); ?></th>
                            <th class="text-center" style="width: 200px;"><?php echo $L->g('security-login-blocklist-table-site'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($blacklistEntries)): ?>
                            <?php foreach ($blacklistEntries as $entry): ?>
                                <tr>
                                    <td class="fw-semibold"><code><?php echo Sanitize::html($entry['ip']); ?></code></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?php echo (int)$entry['failures']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($entry['lastFailureTimestamp'])): ?>
                                            <span class="small text-muted"><?php echo Sanitize::html(date('Y-m-d H:i:s', (int)$entry['lastFailureTimestamp'])); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small"><?php echo $L->g('security-login-not-available'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-secondary"><?php echo Sanitize::html($entry['site']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <span class="bi bi-list-columns fs-1 d-block mb-2"></span>
                                    <span><?php echo $L->g('security-login-blocklist-empty'); ?></span>
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
                <h5 class="mb-0"><?php echo $L->g('security-login-section-alerts'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-login-section-alerts-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($securityAlerts)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($securityAlerts as $alert): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
                                <div class="d-flex flex-column flex-lg-row gap-2 align-items-lg-center">
                                    <span class="badge <?php echo securityLoginStatusBadgeClass($alert['status']); ?>">
                                        <?php echo Sanitize::html(securityLoginStatusLabel($alert['status'], $L)); ?>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">
                                            <?php echo Sanitize::html($alert['title']); ?>
                                            <span class="text-muted">· <?php echo Sanitize::html($alert['site']); ?></span>
                                        </div>
                                        <?php if (!empty($alert['notes'])): ?>
                                            <div class="small text-muted mt-1">
                                                <?php echo Sanitize::html($alert['notes']); ?>
                                            </div>
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
                                        <span class="small text-muted"><?php echo $L->g('security-login-not-available'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-success mb-0 d-flex align-items-center gap-2">
                    <span class="bi bi-check-circle"></span>
                    <span><?php echo $L->g('security-login-alerts-empty'); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><?php echo $L->g('security-login-section-recommendations'); ?></h5>
                <span class="text-muted small"><?php echo $L->g('security-login-section-recommendations-desc'); ?></span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($securityRecommendations)): ?>
                <div class="row g-3">
                    <?php foreach ($securityRecommendations as $recommendation): ?>
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
                <div class="alert alert-info mb-0"><?php echo Sanitize::html($L->g('security-login-overview-empty')); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>security-login.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">
