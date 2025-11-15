<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

$pageLangFile = PATH_LANGUAGES . 'pages/security-firewall/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$layout['title'] .= ' - ' . $L->g('security-firewall-title');

if (!function_exists('mgwSecurityFirewallReadJsonWithGuard')) {
    function mgwSecurityFirewallReadJsonWithGuard($filePath)
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

if (!function_exists('mgwSecurityFirewallFormatTimestamp')) {
    function mgwSecurityFirewallFormatTimestamp($timestamp)
    {
        $timestamp = (int)$timestamp;
        if ($timestamp <= 0) {
            return '';
        }
        return date('Y-m-d H:i:s', $timestamp);
    }
}

$systemConfig = mgwSecurityFirewallReadJsonWithGuard(PATH_CONFIG . 'system.php');
$firewallConfig = array();
if (isset($systemConfig['firewall']) && is_array($systemConfig['firewall'])) {
    $firewallConfig = $systemConfig['firewall'];
}

$globalWhitelist = array();
if (isset($firewallConfig['whitelist']) && is_array($firewallConfig['whitelist'])) {
    $globalWhitelist = $firewallConfig['whitelist'];
} elseif (isset($systemConfig['firewallWhitelist']) && is_array($systemConfig['firewallWhitelist'])) {
    $globalWhitelist = $systemConfig['firewallWhitelist'];
}

$blockedCountries = array();
if (isset($firewallConfig['blockedCountries']) && is_array($firewallConfig['blockedCountries'])) {
    $blockedCountries = $firewallConfig['blockedCountries'];
} elseif (isset($systemConfig['blockedCountries']) && is_array($systemConfig['blockedCountries'])) {
    $blockedCountries = $systemConfig['blockedCountries'];
}

$userAgentRules = array();
if (isset($firewallConfig['userAgentRules']) && is_array($firewallConfig['userAgentRules'])) {
    $userAgentRules = $firewallConfig['userAgentRules'];
}

$sensitivePathRules = array();
if (isset($firewallConfig['protectedPaths']) && is_array($firewallConfig['protectedPaths'])) {
    $sensitivePathRules = $firewallConfig['protectedPaths'];
}

$webhookTargets = array();
if (isset($firewallConfig['webhooks']) && is_array($firewallConfig['webhooks'])) {
    $webhookTargets = $firewallConfig['webhooks'];
}

$adminUriFilter = isset($systemConfig['adminUriFilter']) ? trim((string)$systemConfig['adminUriFilter']) : 'admin';

$firewallOverview = array();
$ipWhitelistEntries = array();
$ipBlacklistEntries = array();
$rateLimitMatrix = array();
$geoAccessMatrix = array();
$uaRuleMatrix = array();
$pathGuardMatrix = array();
$webhookMatrix = array();
$firewallAlerts = array();
$firewallRecommendations = array();

global $security;
$globalBlacklist = $security->getField('blackList');
if (!is_array($globalBlacklist)) {
    $globalBlacklist = array();
}

$globalBlacklistCount = count($globalBlacklist);
$globalBlacklistPreview = array();
foreach ($globalBlacklist as $ip => $data) {
    $lastFailure = isset($data['lastFailure']) ? (int)$data['lastFailure'] : 0;
    $failures = isset($data['numberFailures']) ? (int)$data['numberFailures'] : 0;
    $globalBlacklistPreview[] = array(
        'scope' => 'global',
        'ip' => $ip,
        'failures' => $failures,
        'lastFailure' => $lastFailure
    );
}

usort($globalBlacklistPreview, function ($a, $b) {
    return ($b['lastFailure'] ?? 0) <=> ($a['lastFailure'] ?? 0);
});
$ipBlacklistEntries = array_slice($globalBlacklistPreview, 0, 50);

foreach ($globalWhitelist as $entry) {
    if (is_array($entry)) {
        $ip = isset($entry['ip']) ? (string)$entry['ip'] : '';
        $label = isset($entry['label']) ? (string)$entry['label'] : '';
        $note = isset($entry['note']) ? (string)$entry['note'] : '';
    } else {
        $ip = (string)$entry;
        $label = '';
        $note = '';
    }
    if ($ip === '') {
        continue;
    }
    $ipWhitelistEntries[] = array(
        'scope' => 'global',
        'ip' => $ip,
        'label' => $label,
        'note' => $note
    );
}

$bruteForceFailures = (int)$security->getField('numberFailuresAllowed');
$bruteForceMinutes = (int)$security->getField('minutesBlocked');

$rateLimitStatus = 'ok';
if ($bruteForceFailures > 12 || $bruteForceMinutes < 5) {
    $rateLimitStatus = ($bruteForceFailures >= 20 || $bruteForceMinutes < 2) ? 'fail' : 'warn';
}

$blacklistStatus = $globalBlacklistCount >= 10 ? 'ok' : ($globalBlacklistCount === 0 ? 'warn' : 'ok');
$blacklistMessage = sprintf($L->g('security-firewall-card-blacklist-message'), $globalBlacklistCount);
$blacklistHint = $L->g($globalBlacklistCount === 0 ? 'security-firewall-card-blacklist-empty' : 'security-firewall-card-blacklist-hint');

$whitelistStatus = !empty($ipWhitelistEntries) ? 'ok' : 'warn';
$geoStatus = !empty($blockedCountries) ? 'ok' : 'warn';
$automationStatus = !empty($webhookTargets) ? 'ok' : 'warn';

$firewallOverview = array(
    array(
        'title' => $L->g('security-firewall-card-blacklist-title'),
        'status' => $blacklistStatus,
        'message' => $blacklistMessage,
        'hint' => $blacklistHint,
        'action' => array()
    ),
    array(
        'title' => $L->g('security-firewall-card-ratelimit-title'),
        'status' => $rateLimitStatus,
        'message' => sprintf($L->g('security-firewall-card-ratelimit-message'), $bruteForceFailures, $bruteForceMinutes),
        'hint' => $L->g($rateLimitStatus === 'fail' ? 'security-firewall-card-ratelimit-risk' : 'security-firewall-card-ratelimit-hint'),
        'action' => array()
    ),
    array(
        'title' => $L->g('security-firewall-card-geo-title'),
        'status' => $geoStatus,
        'message' => sprintf($L->g('security-firewall-card-geo-message'), count($blockedCountries)),
        'hint' => $L->g($geoStatus === 'ok' ? 'security-firewall-card-geo-hint' : 'security-firewall-card-geo-empty'),
        'action' => array()
    ),
    array(
        'title' => $L->g('security-firewall-card-automation-title'),
        'status' => $automationStatus,
        'message' => sprintf($L->g('security-firewall-card-automation-message'), count($webhookTargets)),
        'hint' => $L->g($automationStatus === 'ok' ? 'security-firewall-card-automation-hint' : 'security-firewall-card-automation-empty'),
        'action' => empty($webhookTargets) ? array(
            'label' => $L->g('security-firewall-card-automation-configure'),
            'url' => HTML_PATH_ADMIN_ROOT . 'plugins',
            'target' => ''
        ) : array()
    )
);

$sitesRoot = PATH_ROOT . 'mgw-content' . DS;
$siteDirectories = glob($sitesRoot . '*', GLOB_ONLYDIR);
if ($siteDirectories === false) {
    $siteDirectories = array();
}

$dictionaryWatchList = array(
    'firewall-blocked' => 'security-firewall-alert-firewall-blocked',
    'firewall-whitelist-hit' => 'security-firewall-alert-firewall-whitelist-hit',
    'firewall-geo-blocked' => 'security-firewall-alert-firewall-geo-blocked',
    'firewall-ua-blocked' => 'security-firewall-alert-firewall-ua-blocked',
    'rate-limit-detected' => 'security-firewall-alert-rate-limit-detected',
    'security-ip-blocked' => 'security-firewall-alert-security-ip-blocked',
    'security-blacklist' => 'security-firewall-alert-security-blacklist',
    'ddos-detected' => 'security-firewall-alert-ddos-detected'
);

$globalGeoSummary = array(
    'scope' => 'global',
    'mode' => !empty($blockedCountries) ? 'deny' : 'none',
    'blockedCount' => count($blockedCountries),
    'blockedCountries' => $blockedCountries,
    'status' => $geoStatus
);
$geoAccessMatrix[] = $globalGeoSummary;

foreach ($userAgentRules as $rule) {
    if (is_array($rule)) {
        $pattern = isset($rule['pattern']) ? (string)$rule['pattern'] : '';
        $action = isset($rule['action']) ? (string)$rule['action'] : '';
        $note = isset($rule['note']) ? (string)$rule['note'] : '';
    } else {
        $pattern = (string)$rule;
        $action = 'block';
        $note = '';
    }
    if ($pattern === '') {
        continue;
    }
    $uaRuleMatrix[] = array(
        'scope' => 'global',
        'pattern' => $pattern,
        'action' => $action,
        'note' => $note,
        'status' => 'ok'
    );
}

foreach ($sensitivePathRules as $rule) {
    if (is_array($rule)) {
        $path = isset($rule['path']) ? (string)$rule['path'] : '';
        $action = isset($rule['action']) ? (string)$rule['action'] : 'captcha';
        $note = isset($rule['note']) ? (string)$rule['note'] : '';
    } else {
        $path = (string)$rule;
        $action = 'captcha';
        $note = '';
    }
    if ($path === '') {
        continue;
    }
    $pathGuardMatrix[] = array(
        'scope' => 'global',
        'path' => $path,
        'action' => $action,
        'note' => $note,
        'status' => 'ok'
    );
}

foreach ($webhookTargets as $webhook) {
    if (is_array($webhook)) {
        $endpoint = isset($webhook['endpoint']) ? (string)$webhook['endpoint'] : '';
        $events = isset($webhook['events']) ? (array)$webhook['events'] : array();
        $secret = isset($webhook['secret']) ? (string)$webhook['secret'] : '';
    } else {
        $endpoint = (string)$webhook;
        $events = array();
        $secret = '';
    }
    if ($endpoint === '') {
        continue;
    }
    $webhookMatrix[] = array(
        'scope' => 'global',
        'endpoint' => $endpoint,
        'events' => $events,
        'secret' => $secret,
        'status' => 'ok'
    );
}

foreach ($siteDirectories as $directory) {
    $slug = basename($directory);
    if ($slug === '' || $slug[0] === '.') {
        continue;
    }

    $securityFile = $directory . DS . 'databases' . DS . 'security.php';
    $siteSecurityData = mgwSecurityFirewallReadJsonWithGuard($securityFile);

    $siteWhitelist = array();
    if (isset($siteSecurityData['whiteList']) && is_array($siteSecurityData['whiteList'])) {
        $siteWhitelist = $siteSecurityData['whiteList'];
    }

    $siteBlacklist = array();
    if (isset($siteSecurityData['blackList']) && is_array($siteSecurityData['blackList'])) {
        $siteBlacklist = $siteSecurityData['blackList'];
    }

    foreach ($siteWhitelist as $entry) {
        if (is_array($entry)) {
            $ip = isset($entry['ip']) ? (string)$entry['ip'] : '';
            $label = isset($entry['label']) ? (string)$entry['label'] : '';
            $note = isset($entry['note']) ? (string)$entry['note'] : '';
        } else {
            $ip = (string)$entry;
            $label = '';
            $note = '';
        }
        if ($ip === '') {
            continue;
        }
        $ipWhitelistEntries[] = array(
            'scope' => $slug,
            'ip' => $ip,
            'label' => $label,
            'note' => $note
        );
    }

    foreach ($siteBlacklist as $ip => $data) {
        $lastFailure = isset($data['lastFailure']) ? (int)$data['lastFailure'] : 0;
        $failures = isset($data['numberFailures']) ? (int)$data['numberFailures'] : 0;
        $ipBlacklistEntries[] = array(
            'scope' => $slug,
            'ip' => $ip,
            'failures' => $failures,
            'lastFailure' => $lastFailure
        );
    }

    $siteFailuresAllowed = isset($siteSecurityData['numberFailuresAllowed']) ? (int)$siteSecurityData['numberFailuresAllowed'] : $bruteForceFailures;
    $siteMinutesBlocked = isset($siteSecurityData['minutesBlocked']) ? (int)$siteSecurityData['minutesBlocked'] : $bruteForceMinutes;
    $siteBlockedCount = count($siteBlacklist);
    $siteLastBlocked = 0;
    foreach ($siteBlacklist as $entry) {
        $siteLastBlocked = max($siteLastBlocked, isset($entry['lastFailure']) ? (int)$entry['lastFailure'] : 0);
    }

    $siteRateStatus = 'ok';
    if ($siteFailuresAllowed > $bruteForceFailures || $siteMinutesBlocked < $bruteForceMinutes) {
        $siteRateStatus = 'warn';
    }
    if ($siteFailuresAllowed >= 20 || $siteMinutesBlocked < 2) {
        $siteRateStatus = 'fail';
    }

    $rateLimitMatrix[] = array(
        'site' => $slug,
        'failuresAllowed' => $siteFailuresAllowed,
        'minutesBlocked' => $siteMinutesBlocked,
        'blockedCount' => $siteBlockedCount,
        'lastBlocked' => $siteLastBlocked,
        'status' => $siteRateStatus
    );

    $siteBlockedCountries = array();
    if (isset($siteSecurityData['blockedCountries']) && is_array($siteSecurityData['blockedCountries'])) {
        $siteBlockedCountries = $siteSecurityData['blockedCountries'];
    }

    if (!empty($siteBlockedCountries)) {
        $geoAccessMatrix[] = array(
            'scope' => $slug,
            'mode' => 'deny',
            'blockedCount' => count($siteBlockedCountries),
            'blockedCountries' => $siteBlockedCountries,
            'status' => 'ok'
        );
    }

    if (isset($siteSecurityData['userAgentRules']) && is_array($siteSecurityData['userAgentRules'])) {
        foreach ($siteSecurityData['userAgentRules'] as $rule) {
            if (is_array($rule)) {
                $pattern = isset($rule['pattern']) ? (string)$rule['pattern'] : '';
                $action = isset($rule['action']) ? (string)$rule['action'] : '';
                $note = isset($rule['note']) ? (string)$rule['note'] : '';
            } else {
                $pattern = (string)$rule;
                $action = 'block';
                $note = '';
            }
            if ($pattern === '') {
                continue;
            }
            $uaRuleMatrix[] = array(
                'scope' => $slug,
                'pattern' => $pattern,
                'action' => $action,
                'note' => $note,
                'status' => 'ok'
            );
        }
    }

    if (isset($siteSecurityData['protectedPaths']) && is_array($siteSecurityData['protectedPaths'])) {
        foreach ($siteSecurityData['protectedPaths'] as $rule) {
            if (is_array($rule)) {
                $path = isset($rule['path']) ? (string)$rule['path'] : '';
                $action = isset($rule['action']) ? (string)$rule['action'] : 'captcha';
                $note = isset($rule['note']) ? (string)$rule['note'] : '';
            } else {
                $path = (string)$rule;
                $action = 'captcha';
                $note = '';
            }
            if ($path === '') {
                continue;
            }
            $pathGuardMatrix[] = array(
                'scope' => $slug,
                'path' => $path,
                'action' => $action,
                'note' => $note,
                'status' => 'ok'
            );
        }
    }

    if (isset($siteSecurityData['webhooks']) && is_array($siteSecurityData['webhooks'])) {
        foreach ($siteSecurityData['webhooks'] as $webhook) {
            if (is_array($webhook)) {
                $endpoint = isset($webhook['endpoint']) ? (string)$webhook['endpoint'] : '';
                $events = isset($webhook['events']) ? (array)$webhook['events'] : array();
                $secret = isset($webhook['secret']) ? (string)$webhook['secret'] : '';
            } else {
                $endpoint = (string)$webhook;
                $events = array();
                $secret = '';
            }
            if ($endpoint === '') {
                continue;
            }
            $webhookMatrix[] = array(
                'scope' => $slug,
                'endpoint' => $endpoint,
                'events' => $events,
                'secret' => $secret,
                'status' => 'ok'
            );
        }
    }

    $syslogFile = $directory . DS . 'databases' . DS . 'syslog.php';
    $syslogData = mgwSecurityFirewallReadJsonWithGuard($syslogFile);
    if (!empty($syslogData) && is_array($syslogData)) {
        foreach ($syslogData as $entry) {
            $dictionaryKey = isset($entry['dictionaryKey']) ? (string)$entry['dictionaryKey'] : '';
            $notes = isset($entry['notes']) ? (string)$entry['notes'] : '';
            $dateString = isset($entry['date']) ? (string)$entry['date'] : '';
            $method = isset($entry['method']) ? (string)$entry['method'] : '';

            $include = false;
            foreach ($dictionaryWatchList as $needle => $translationKey) {
                if ($dictionaryKey === $needle || Text::stringContains($dictionaryKey, $needle, false)) {
                    $include = true;
                    break;
                }
                if ($notes !== '' && Text::stringContains($notes, $needle, false)) {
                    $include = true;
                    break;
                }
            }

            if (!$include && ($dictionaryKey !== '' && (Text::stringContains($dictionaryKey, 'firewall', false) || Text::stringContains($dictionaryKey, 'rate', false)))) {
                $include = true;
            }

            if (!$include) {
                continue;
            }

            $translationKey = isset($dictionaryWatchList[$dictionaryKey]) ? $dictionaryWatchList[$dictionaryKey] : null;
            $title = $translationKey ? $L->g($translationKey) : ($dictionaryKey !== '' ? Text::firstCharUp(str_replace('-', ' ', $dictionaryKey)) : $L->g('security-firewall-alert-generic'));

            $severity = 'warn';
            if (Text::stringContains($dictionaryKey, 'ddos', false) || Text::stringContains($dictionaryKey, 'blocked', false)) {
                $severity = 'fail';
            }

            $timestamp = $dateString !== '' ? strtotime($dateString) : 0;

            $firewallAlerts[] = array(
                'site' => $slug,
                'title' => $title,
                'status' => $severity,
                'notes' => $notes,
                'dictionaryKey' => $dictionaryKey,
                'date' => $dateString,
                'method' => $method,
                'timestamp' => $timestamp
            );
        }
    }
}

usort($ipBlacklistEntries, function ($a, $b) {
    return ($b['lastFailure'] ?? 0) <=> ($a['lastFailure'] ?? 0);
});
$ipBlacklistEntries = array_slice($ipBlacklistEntries, 0, 50);

usort($firewallAlerts, function ($a, $b) {
    return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
});
$firewallAlerts = array_slice($firewallAlerts, 0, 12);

$pathGuardMatrix[] = array(
    'scope' => 'admin',
    'path' => '/' . $adminUriFilter,
    'action' => 'rename',
    'note' => $L->g('security-firewall-default-admin-path'),
    'status' => Text::lowercase($adminUriFilter) === 'admin' ? 'warn' : 'ok'
);

$firewallRecommendations = array(
    array(
        'icon' => 'bi-shield-check',
        'status' => $blacklistStatus,
        'title' => $L->g('security-firewall-recommendation-blacklist-title'),
        'body' => $L->g('security-firewall-recommendation-blacklist-body')
    ),
    array(
        'icon' => 'bi-geo-alt',
        'status' => $geoStatus,
        'title' => $L->g('security-firewall-recommendation-geo-title'),
        'body' => $L->g('security-firewall-recommendation-geo-body')
    ),
    array(
        'icon' => 'bi-broadcast',
        'status' => $automationStatus,
        'title' => $L->g('security-firewall-recommendation-webhook-title'),
        'body' => $L->g('security-firewall-recommendation-webhook-body')
    )
);

$firewallOverview = array_values($firewallOverview);
$ipWhitelistEntries = array_values($ipWhitelistEntries);
$ipBlacklistEntries = array_values($ipBlacklistEntries);
$rateLimitMatrix = array_values($rateLimitMatrix);
$geoAccessMatrix = array_values($geoAccessMatrix);
$uaRuleMatrix = array_values($uaRuleMatrix);
$pathGuardMatrix = array_values($pathGuardMatrix);
$webhookMatrix = array_values($webhookMatrix);
$firewallAlerts = array_values($firewallAlerts);
$firewallRecommendations = array_values($firewallRecommendations);