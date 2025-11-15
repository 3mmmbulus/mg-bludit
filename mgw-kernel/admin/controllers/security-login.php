<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

$pageLangFile = PATH_LANGUAGES . 'pages/security-login/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$layout['title'] .= ' - ' . $L->g('security-login-title');

if (!function_exists('mgwSecurityLoginReadJsonWithGuard')) {
    function mgwSecurityLoginReadJsonWithGuard($filePath)
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

        $jsonPayload = trim(implode('', $lines));
        if ($jsonPayload === '' || $jsonPayload === '{}' || $jsonPayload === '[]') {
            return array();
        }

        $decoded = json_decode($jsonPayload, true);
        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('mgwSecurityLoginBuildPluginClassName')) {
    function mgwSecurityLoginBuildPluginClassName($slug)
    {
        $segments = preg_split('/[-_]/', (string)$slug);
        $className = 'plugin';
        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }
            $className .= Text::firstCharUp($segment);
        }
        return $className;
    }
}

$systemConfig = mgwSecurityLoginReadJsonWithGuard(PATH_CONFIG . 'system.php');
$adminUriFilter = isset($systemConfig['adminUriFilter']) ? $systemConfig['adminUriFilter'] : 'admin';

$securityOverview = array();
$siteBruteForceProfiles = array();
$blacklistEntries = array();
$userSecurityProfiles = array();
$securityAlerts = array();
$securityRecommendations = array();

$adminUriSanitized = trim((string)$adminUriFilter);

global $security;
$bruteForceFailures = (int)$security->getField('numberFailuresAllowed');
$bruteForceMinutes = (int)$security->getField('minutesBlocked');
$bruteForceBlacklist = $security->getField('blackList');
if (!is_array($bruteForceBlacklist)) {
    $bruteForceBlacklist = array();
}

global $plugins;
$twoFactorSlugs = array('twofactor', 'two-factor', 'authenticator', 'otp', 'totp', 'security-twofactor', 'twofa');
$twoFactorDetected = false;
$twoFactorEnabled = false;
foreach ($twoFactorSlugs as $slugCandidate) {
    $classCandidate = mgwSecurityLoginBuildPluginClassName($slugCandidate);
    if (isset($plugins['all'][$classCandidate])) {
        $twoFactorDetected = true;
        if ($plugins['all'][$classCandidate]->installed()) {
            $twoFactorEnabled = true;
            break;
        }
    } elseif (is_dir(PATH_PLUGINS . $slugCandidate)) {
        $twoFactorDetected = true;
    }
}

if ($twoFactorEnabled) {
    $twoFactorStatus = 'ok';
    $twoFactorMessage = $L->g('security-login-card-twofactor-enabled');
} elseif ($twoFactorDetected) {
    $twoFactorStatus = 'warn';
    $twoFactorMessage = $L->g('security-login-card-twofactor-available');
} else {
    $twoFactorStatus = 'fail';
    $twoFactorMessage = $L->g('security-login-card-twofactor-missing');
}
$twoFactorHint = $L->g('security-login-card-twofactor-hint');
$twoFactorAction = array();
if ($twoFactorStatus !== 'ok') {
    $twoFactorAction = array(
        'label' => $L->g('security-login-card-twofactor-configure'),
        'url' => HTML_PATH_ADMIN_ROOT . 'plugins',
        'target' => ''
    );
}

$bruteForceStatus = 'ok';
if ($bruteForceFailures > 10 || $bruteForceMinutes < 5) {
    $bruteForceStatus = ($bruteForceFailures >= 20 || $bruteForceMinutes < 2) ? 'fail' : 'warn';
}
$bruteForceMessage = sprintf($L->g('security-login-card-bruteforce-message'), $bruteForceFailures, $bruteForceMinutes);
$bruteForceHint = $L->g($bruteForceStatus === 'fail' ? 'security-login-card-bruteforce-risk' : 'security-login-card-bruteforce-hint');

$adminUriStatus = Text::lowercase($adminUriSanitized) === 'admin' ? 'warn' : 'ok';
$adminUriMessage = $adminUriStatus === 'ok'
    ? sprintf($L->g('security-login-card-endpoint-hardened'), $adminUriSanitized)
    : $L->g('security-login-card-endpoint-default');
$adminUriHint = $L->g('security-login-card-endpoint-hint');

$accountFindingsCount = 0;
$accountSevere = false;
$currentTime = time();

global $users;
$usersDatabase = isset($users->db) && is_array($users->db) ? $users->db : array();
foreach ($usersDatabase as $username => $userData) {
    $role = isset($userData['role']) ? $userData['role'] : 'author';
    $email = isset($userData['email']) ? $userData['email'] : '';
    $tokenRemember = isset($userData['tokenRemember']) ? $userData['tokenRemember'] : '';
    $tokenAuthTTL = isset($userData['tokenAuthTTL']) ? trim((string)$userData['tokenAuthTTL']) : '';

    $issues = array();
    if (Text::isEmpty($email)) {
        $issues[] = $L->g('security-login-users-issue-no-email');
    }

    $rememberActive = !Text::isEmpty($tokenRemember);
    if ($rememberActive) {
        $issues[] = $L->g('security-login-users-issue-remember');
    }

    $tokenStatus = 'unknown';
    if (!Text::isEmpty($tokenAuthTTL)) {
        $ttlTimestamp = strtotime($tokenAuthTTL);
        if ($ttlTimestamp !== false) {
            if ($ttlTimestamp < $currentTime) {
                $tokenStatus = 'expired';
                $issues[] = $L->g('security-login-users-issue-token-expired');
            } elseif ($ttlTimestamp < $currentTime + (7 * 24 * 60 * 60)) {
                $tokenStatus = 'expiring';
                $issues[] = $L->g('security-login-users-issue-token-expiring');
            } else {
                $tokenStatus = 'valid';
            }
        }
    }

    $status = empty($issues) ? 'ok' : 'warn';
    if (!empty($issues)) {
        $accountFindingsCount += count($issues);
    }

    if (($rememberActive && $role === 'admin') || $tokenStatus === 'expired') {
        $status = 'fail';
        $accountSevere = true;
    }

    $userSecurityProfiles[] = array(
        'username' => $username,
        'role' => $role,
        'email' => $email,
        'rememberActive' => $rememberActive,
        'tokenAuthTTL' => $tokenAuthTTL,
        'tokenStatus' => $tokenStatus,
        'issues' => $issues,
        'status' => $status
    );
}

if ($accountFindingsCount === 0) {
    $accountStatus = 'ok';
    $accountMessage = $L->g('security-login-card-accounts-clean');
} else {
    $accountStatus = $accountSevere ? 'fail' : 'warn';
    $messageKey = $accountSevere ? 'security-login-card-accounts-severe' : 'security-login-card-accounts-action-needed';
    $accountMessage = sprintf($L->g($messageKey), $accountFindingsCount);
}
$accountHint = $L->g('security-login-card-accounts-hint');

$securityOverview = array(
    array(
        'title' => $L->g('security-login-card-twofactor-title'),
        'status' => $twoFactorStatus,
        'message' => $twoFactorMessage,
        'hint' => $twoFactorHint,
        'action' => $twoFactorAction
    ),
    array(
        'title' => $L->g('security-login-card-bruteforce-title'),
        'status' => $bruteForceStatus,
        'message' => $bruteForceMessage,
        'hint' => $bruteForceHint,
        'action' => array()
    ),
    array(
        'title' => $L->g('security-login-card-endpoint-title'),
        'status' => $adminUriStatus,
        'message' => $adminUriMessage,
        'hint' => $adminUriHint,
        'action' => array()
    ),
    array(
        'title' => $L->g('security-login-card-accounts-title'),
        'status' => $accountStatus,
        'message' => $accountMessage,
        'hint' => $accountHint,
        'action' => array()
    )
);

$sitesRoot = PATH_ROOT . 'mgw-content' . DS;
$siteDirectories = glob($sitesRoot . '*', GLOB_ONLYDIR);
if ($siteDirectories === false) {
    $siteDirectories = array();
}

foreach ($siteDirectories as $directory) {
    $slug = basename($directory);
    if ($slug === '' || $slug[0] === '.') {
        continue;
    }

    $securityFile = $directory . DS . 'databases' . DS . 'security.php';
    $siteSecurityData = mgwSecurityLoginReadJsonWithGuard($securityFile);

    $siteFailuresAllowed = isset($siteSecurityData['numberFailuresAllowed']) ? (int)$siteSecurityData['numberFailuresAllowed'] : 10;
    $siteMinutesBlocked = isset($siteSecurityData['minutesBlocked']) ? (int)$siteSecurityData['minutesBlocked'] : 5;
    $siteBlacklist = isset($siteSecurityData['blackList']) && is_array($siteSecurityData['blackList']) ? $siteSecurityData['blackList'] : array();

    $siteLastBlockedTimestamp = 0;
    foreach ($siteBlacklist as $ip => $entry) {
        $lastFailure = isset($entry['lastFailure']) ? (int)$entry['lastFailure'] : 0;
        $attempts = isset($entry['numberFailures']) ? (int)$entry['numberFailures'] : 0;

        if ($lastFailure > $siteLastBlockedTimestamp) {
            $siteLastBlockedTimestamp = $lastFailure;
        }

        $blacklistEntries[] = array(
            'site' => $slug,
            'ip' => $ip,
            'failures' => $attempts,
            'lastFailureTimestamp' => $lastFailure
        );
    }

    $siteBruteForceProfiles[] = array(
        'site' => $slug,
        'failuresAllowed' => $siteFailuresAllowed,
        'minutesBlocked' => $siteMinutesBlocked,
        'blockedCount' => count($siteBlacklist),
        'lastBlocked' => $siteLastBlockedTimestamp
    );
}

usort($blacklistEntries, function ($a, $b) {
    return ($b['lastFailureTimestamp'] ?? 0) <=> ($a['lastFailureTimestamp'] ?? 0);
});
$blacklistEntries = array_slice($blacklistEntries, 0, 20);

$dictionaryLabelMap = array(
    'settings-changes' => 'security-login-alert-dictionary-settings-changes',
    'user-login-failed' => 'security-login-alert-dictionary-user-login-failed',
    'user-logged' => 'security-login-alert-dictionary-user-logged-in',
    'user-logged-in' => 'security-login-alert-dictionary-user-logged-in',
    'security-ip-blocked' => 'security-login-alert-dictionary-security-ip-blocked',
    'security-blacklist' => 'security-login-alert-dictionary-security-blacklist',
    'plugin-activated' => 'security-login-alert-dictionary-plugin-activated',
    'plugin-deactivated' => 'security-login-alert-dictionary-plugin-deactivated'
);

foreach ($siteDirectories as $directory) {
    $slug = basename($directory);
    if ($slug === '' || $slug[0] === '.') {
        continue;
    }

    $syslogFile = $directory . DS . 'databases' . DS . 'syslog.php';
    $syslogData = mgwSecurityLoginReadJsonWithGuard($syslogFile);
    if (empty($syslogData) || !is_array($syslogData)) {
        continue;
    }

    foreach ($syslogData as $entry) {
        $dictionaryKey = isset($entry['dictionaryKey']) ? $entry['dictionaryKey'] : '';
        if ($dictionaryKey === '') {
            continue;
        }

        $includeEntry = Text::stringContains($dictionaryKey, 'login', false)
            || Text::stringContains($dictionaryKey, 'security', false)
            || isset($dictionaryLabelMap[$dictionaryKey]);

        if (!$includeEntry) {
            continue;
        }

        $titleKey = isset($dictionaryLabelMap[$dictionaryKey]) ? $dictionaryLabelMap[$dictionaryKey] : null;
        $title = $titleKey ? $L->g($titleKey) : Text::firstCharUp(str_replace('-', ' ', $dictionaryKey));

        $status = 'warn';
        if (Text::stringContains($dictionaryKey, 'failed', false) || Text::stringContains($dictionaryKey, 'blocked', false)) {
            $status = 'fail';
        } elseif (Text::stringContains($dictionaryKey, 'logged', false)) {
            $status = 'ok';
        }

        $timestamp = isset($entry['date']) ? strtotime($entry['date']) : 0;

        $securityAlerts[] = array(
            'site' => $slug,
            'title' => $title,
            'status' => $status,
            'notes' => isset($entry['notes']) ? $entry['notes'] : '',
            'dictionaryKey' => $dictionaryKey,
            'date' => isset($entry['date']) ? $entry['date'] : '',
            'method' => isset($entry['method']) ? $entry['method'] : '',
            'timestamp' => $timestamp
        );
    }
}

usort($securityAlerts, function ($a, $b) {
    return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
});
$securityAlerts = array_slice($securityAlerts, 0, 10);

$securityRecommendations = array(
    array(
        'icon' => 'bi-shield-lock',
        'status' => $twoFactorStatus,
        'title' => $L->g('security-login-recommendation-2fa-title'),
        'body' => $L->g('security-login-recommendation-2fa-body')
    ),
    array(
        'icon' => 'bi-signpost-split',
        'status' => $adminUriStatus,
        'title' => $L->g('security-login-recommendation-admin-uri-title'),
        'body' => sprintf($L->g('security-login-recommendation-admin-uri-body'), $adminUriSanitized)
    ),
    array(
        'icon' => 'bi-clipboard-data',
        'status' => !empty($securityAlerts) ? 'warn' : 'ok',
        'title' => $L->g('security-login-recommendation-log-monitor-title'),
        'body' => $L->g('security-login-recommendation-log-monitor-body')
    )
);

$securityOverview = array_values($securityOverview);
$siteBruteForceProfiles = array_values($siteBruteForceProfiles);
$blacklistEntries = array_values($blacklistEntries);
$userSecurityProfiles = array_values($userSecurityProfiles);
$securityAlerts = array_values($securityAlerts);
$securityRecommendations = array_values($securityRecommendations);
