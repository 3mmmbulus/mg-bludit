<?php defined('MAIGEWAN') or die('Maigewan CMS.');

class DashboardInsights
{
    private $configFile;
    private $data;

    public function __construct($configFile = null)
    {
        $baseDir = PATH_CONFIG . 'dashboard' . DS;
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, DIR_PERMISSIONS, true);
        }

        $this->configFile = $configFile ?: $baseDir . 'insights.php';
        $this->data = null;
    }

    public function getData()
    {
        $this->ensureDataLoaded();
        return $this->data;
    }

    public function getMetadata()
    {
        $payload = $this->getData();
        $metadata = isset($payload['metadata']) && is_array($payload['metadata']) ? $payload['metadata'] : array();
        return array(
            'generatedAt' => isset($metadata['generatedAt']) ? (string)$metadata['generatedAt'] : '',
            'source' => isset($metadata['source']) ? (string)$metadata['source'] : '',
            'notes' => isset($metadata['notes']) ? (string)$metadata['notes'] : ''
        );
    }

    public function getOverviewDeltas()
    {
        $payload = $this->getData();
        $section = isset($payload['overview']) && is_array($payload['overview']) ? $payload['overview'] : array();
        return array(
            'totalDelta' => isset($section['totalDelta']) ? (float)$section['totalDelta'] : 0.0,
            'activeDelta' => isset($section['activeDelta']) ? (float)$section['activeDelta'] : 0.0,
            'abnormalDelta' => isset($section['abnormalDelta']) ? (float)$section['abnormalDelta'] : 0.0,
            'pendingDelta' => isset($section['pendingDelta']) ? (float)$section['pendingDelta'] : 0.0
        );
    }

    public function getHttpsSummary()
    {
        $payload = $this->getData();
        $section = isset($payload['https']) && is_array($payload['https']) ? $payload['https'] : array();
        $trend = array();

        if (!empty($section['trend']) && is_array($section['trend'])) {
            foreach ($section['trend'] as $item) {
                $trend[] = array(
                    'label' => isset($item['label']) ? (string)$item['label'] : '',
                    'valid' => isset($item['valid']) ? (int)$item['valid'] : 0,
                    'expiring' => isset($item['expiring']) ? (int)$item['expiring'] : 0,
                    'invalid' => isset($item['invalid']) ? (int)$item['invalid'] : 0
                );
            }
        }

        return array(
            'valid' => isset($section['valid']) ? (int)$section['valid'] : 0,
            'expiring' => isset($section['expiring']) ? (int)$section['expiring'] : 0,
            'invalid' => isset($section['invalid']) ? (int)$section['invalid'] : 0,
            'trend' => $trend
        );
    }

    public function getSpiderSummary()
    {
        $payload = $this->getData();
        $section = isset($payload['spiders']) && is_array($payload['spiders']) ? $payload['spiders'] : array();

        $topSources = array();
        if (!empty($section['topSources']) && is_array($section['topSources'])) {
            foreach ($section['topSources'] as $source) {
                $topSources[] = array(
                    'name' => isset($source['name']) ? (string)$source['name'] : '',
                    'hits' => isset($source['hits']) ? (int)$source['hits'] : 0,
                    'change' => isset($source['change']) ? (string)$source['change'] : ''
                );
            }
        }

        $trend = array();
        if (!empty($section['trend']) && is_array($section['trend'])) {
            foreach ($section['trend'] as $item) {
                $trend[] = array(
                    'label' => isset($item['label']) ? (string)$item['label'] : '',
                    'value' => isset($item['value']) ? (int)$item['value'] : 0
                );
            }
        }

        return array(
            'total' => isset($section['total']) ? (int)$section['total'] : 0,
            'last24h' => isset($section['last24h']) ? (int)$section['last24h'] : 0,
            'activeBots' => isset($section['activeBots']) ? (int)$section['activeBots'] : 0,
            'topSources' => $topSources,
            'trend' => $trend
        );
    }

    public function getSpiderLatestRecords($limit = 5)
    {
        $payload = $this->getData();
        $section = isset($payload['spiders']) && is_array($payload['spiders']) ? $payload['spiders'] : array();
        $records = array();

        if (!empty($section['latest']) && is_array($section['latest'])) {
            foreach ($section['latest'] as $item) {
                $records[] = array(
                    'bot' => isset($item['bot']) ? (string)$item['bot'] : '',
                    'domain' => isset($item['domain']) ? (string)$item['domain'] : '',
                    'path' => isset($item['path']) ? (string)$item['path'] : '',
                    'ip' => isset($item['ip']) ? (string)$item['ip'] : '',
                    'time' => isset($item['time']) ? (string)$item['time'] : ''
                );
            }
        }

        return $limit > 0 ? array_slice($records, 0, $limit) : $records;
    }

    public function getTasksSummary()
    {
        $payload = $this->getData();
        $section = isset($payload['tasksSummary']) && is_array($payload['tasksSummary']) ? $payload['tasksSummary'] : array();

        $success = isset($section['success']) ? (int)$section['success'] : 0;
        $failed = isset($section['failed']) ? (int)$section['failed'] : 0;
        $total = $success + $failed;
        $successRate = $total > 0 ? round(($success / $total) * 100, 1) : 0.0;
        $failureRate = $total > 0 ? round(($failed / $total) * 100, 1) : 0.0;

        return array(
            'date' => isset($section['date']) ? (string)$section['date'] : '',
            'success' => $success,
            'failed' => $failed,
            'total' => $total,
            'successRate' => $successRate,
            'failureRate' => $failureRate
        );
    }

    public function getConfiguredLatestSites($limit = 5)
    {
        $payload = $this->getData();
        $entries = array();

        if (!empty($payload['latestSites']) && is_array($payload['latestSites'])) {
            foreach ($payload['latestSites'] as $item) {
                $entries[] = array(
                    'name' => isset($item['name']) ? (string)$item['name'] : '',
                    'group' => isset($item['group']) ? (string)$item['group'] : '',
                    'status' => isset($item['status']) ? (string)$item['status'] : '',
                    'change' => isset($item['change']) ? (string)$item['change'] : '',
                    'operator' => isset($item['operator']) ? (string)$item['operator'] : (isset($item['owner']) ? (string)$item['owner'] : ''),
                    'owner' => isset($item['owner']) ? (string)$item['owner'] : '',
                    'updatedAt' => isset($item['updatedAt']) ? (string)$item['updatedAt'] : ''
                );
            }
        }

        return $limit > 0 ? array_slice($entries, 0, $limit) : $entries;
    }

    public function getRunningTasks($limit = 5)
    {
        $payload = $this->getData();
        $entries = array();

        if (!empty($payload['runningTasks']) && is_array($payload['runningTasks'])) {
            foreach ($payload['runningTasks'] as $item) {
                $entries[] = array(
                    'name' => isset($item['name']) ? (string)$item['name'] : '',
                    'schedule' => isset($item['schedule']) ? (string)$item['schedule'] : '',
                    'lastRun' => isset($item['lastRun']) ? (string)$item['lastRun'] : '',
                    'status' => isset($item['status']) ? (string)$item['status'] : '',
                    'duration' => isset($item['duration']) ? (string)$item['duration'] : ''
                );
            }
        }

        return $limit > 0 ? array_slice($entries, 0, $limit) : $entries;
    }

    public function getTaskStatusBreakdown()
    {
        $tasks = $this->getRunningTasks(0);
        $buckets = array(
            'success' => 0,
            'warning' => 0,
            'failed' => 0,
            'running' => 0,
            'pending' => 0,
            'other' => 0
        );

        foreach ($tasks as $task) {
            $status = strtolower(trim((string)($task['status'] ?? '')));

            if ($status === '') {
                $buckets['other']++;
                continue;
            }

            if (array_key_exists($status, $buckets)) {
                $buckets[$status]++;
            } else {
                $buckets['other']++;
            }
        }

        $total = array_sum($buckets);

        return array(
            'counts' => $buckets,
            'total' => $total
        );
    }

    public function getSiteStatusBreakdown()
    {
        $sites = $this->getConfiguredLatestSites(0);
        $buckets = array(
            'active' => 0,
            'abnormal' => 0,
            'pending' => 0
        );
        $raw = array();

        foreach ($sites as $site) {
            $status = isset($site['status']) ? (string)$site['status'] : '';
            $bucket = self::statusBucket($status);
            if (!array_key_exists($bucket, $buckets)) {
                $buckets[$bucket] = 0;
            }
            $buckets[$bucket]++;
            $raw[] = array(
                'status' => $status,
                'bucket' => $bucket,
                'name' => isset($site['name']) ? (string)$site['name'] : ''
            );
        }

        $total = array_sum($buckets);

        return array(
            'buckets' => $buckets,
            'total' => $total,
            'raw' => $raw
        );
    }

    public function getSystemAlerts($limit = 5)
    {
        $payload = $this->getData();
        $alerts = array();

        if (!empty($payload['systemAlerts']) && is_array($payload['systemAlerts'])) {
            foreach ($payload['systemAlerts'] as $item) {
                $alerts[] = array(
                    'title' => isset($item['title']) ? (string)$item['title'] : '',
                    'level' => isset($item['level']) ? (string)$item['level'] : '',
                    'message' => isset($item['message']) ? (string)$item['message'] : '',
                    'timestamp' => isset($item['timestamp']) ? (string)$item['timestamp'] : ''
                );
            }
        }

        if (empty($alerts)) {
            $tasks = $this->getRunningTasks(0);
            foreach ($tasks as $task) {
                $status = strtolower(trim((string)($task['status'] ?? '')));
                if (!in_array($status, array('warning', 'failed', 'error'), true)) {
                    continue;
                }

                $messageParts = array();
                $lastRun = isset($task['lastRun']) ? (string)$task['lastRun'] : '';
                $duration = isset($task['duration']) ? (string)$task['duration'] : '';
                $schedule = isset($task['schedule']) ? (string)$task['schedule'] : '';

                if ($lastRun !== '') {
                    $messageParts[] = $lastRun;
                }
                if ($duration !== '') {
                    $messageParts[] = $duration;
                }
                if ($schedule !== '') {
                    $messageParts[] = $schedule;
                }

                $alerts[] = array(
                    'title' => isset($task['name']) ? (string)$task['name'] : '',
                    'level' => $status === 'failed' ? 'critical' : 'warning',
                    'message' => implode(' · ', $messageParts),
                    'timestamp' => $lastRun
                );
            }
        }

        return $limit > 0 ? array_slice($alerts, 0, $limit) : $alerts;
    }

    public function getAuthorizationStatus()
    {
        $payload = $this->getData();
        $section = isset($payload['authorization']) && is_array($payload['authorization']) ? $payload['authorization'] : array();

        $authorized = array();
        if (!empty($section['authorizedTo']) && is_array($section['authorizedTo'])) {
            foreach ($section['authorizedTo'] as $item) {
                $authorized[] = (string)$item;
            }
        }

        return array(
            'license' => isset($section['license']) ? (string)$section['license'] : '',
            'status' => isset($section['status']) ? (string)$section['status'] : '',
            'expiresAt' => isset($section['expiresAt']) ? (string)$section['expiresAt'] : '',
            'lastChecked' => isset($section['lastChecked']) ? (string)$section['lastChecked'] : '',
            'supportLevel' => isset($section['supportLevel']) ? (string)$section['supportLevel'] : '',
            'authorizedTo' => $authorized
        );
    }

    public function getRecommendations($limit = 5)
    {
        $payload = $this->getData();
        $entries = array();

        if (!empty($payload['recommendations']) && is_array($payload['recommendations'])) {
            foreach ($payload['recommendations'] as $item) {
                $entries[] = array(
                    'title' => isset($item['title']) ? (string)$item['title'] : '',
                    'severity' => isset($item['severity']) ? (string)$item['severity'] : '',
                    'description' => isset($item['description']) ? (string)$item['description'] : ''
                );
            }
        }

        return $limit > 0 ? array_slice($entries, 0, $limit) : $entries;
    }

    public static function summarizeGroups(array $groups)
    {
        $summary = array(
            'totalGroups' => 0,
            'totalSites' => 0,
            'activeSites' => 0,
            'abnormalSites' => 0,
            'pendingSites' => 0,
            'activeGroups' => 0,
            'abnormalGroups' => 0,
            'pendingGroups' => 0
        );

        foreach ($groups as $group) {
            $summary['totalGroups']++;
            $count = (int)($group['site_count'] ?? 0);
            if ($count <= 0 && isset($group['domains']) && is_array($group['domains'])) {
                $count = count($group['domains']);
            }
            $summary['totalSites'] += $count;

            $bucket = self::statusBucket($group['status'] ?? 'active');
            if ($bucket === 'active') {
                $summary['activeSites'] += $count;
                $summary['activeGroups']++;
            } elseif ($bucket === 'abnormal') {
                $summary['abnormalSites'] += $count;
                $summary['abnormalGroups']++;
            } else {
                $summary['pendingSites'] += $count;
                $summary['pendingGroups']++;
            }
        }

        return $summary;
    }

    public static function buildLatestSitesFallback(array $groups, $limit = 5)
    {
        $entries = array();

        foreach ($groups as $group) {
            $domains = isset($group['domains']) && is_array($group['domains']) ? $group['domains'] : array();
            $primary = !empty($domains) ? (string)$domains[0] : (string)($group['group_name'] ?? $group['group_id'] ?? '');

            $entries[] = array(
                'name' => $primary,
                'group' => isset($group['group_name']) ? (string)$group['group_name'] : (string)($group['group_id'] ?? ''),
                'status' => isset($group['status']) ? (string)$group['status'] : 'active',
                'change' => '',
                'operator' => isset($group['created_by']) ? (string)$group['created_by'] : 'system',
                'owner' => isset($group['created_by']) ? (string)$group['created_by'] : 'system',
                'updatedAt' => isset($group['updated_at']) ? (string)$group['updated_at'] : (string)($group['created_at'] ?? '')
            );
        }

        usort($entries, function ($a, $b) {
            $timeA = isset($a['updatedAt']) ? strtotime($a['updatedAt']) : 0;
            $timeB = isset($b['updatedAt']) ? strtotime($b['updatedAt']) : 0;
            return $timeB <=> $timeA;
        });

        return $limit > 0 ? array_slice($entries, 0, $limit) : $entries;
    }

    public static function statusBucket($status)
    {
        $status = strtolower(trim((string)$status));

        if (in_array($status, array('warning', 'error', 'abnormal', 'failed', 'maintenance', 'degraded'), true)) {
            return 'abnormal';
        }

        if (in_array($status, array('pending', 'inactive', 'paused', 'draft', 'standby', 'hold'), true)) {
            return 'pending';
        }

        return 'active';
    }

    private function ensureDataLoaded()
    {
        if ($this->data !== null) {
            return;
        }

        $this->data = $this->readJsonWithGuard($this->configFile);
    }

    private function readJsonWithGuard($path)
    {
        if (!is_readable($path)) {
            return array();
        }

        $buffer = file_get_contents($path);
        if ($buffer === false) {
            return array();
        }

        $markerPos = strpos($buffer, '?>');
        if ($markerPos !== false) {
            $buffer = substr($buffer, $markerPos + 2);
        }

        $buffer = trim($buffer);
        if ($buffer === '') {
            return array();
        }

        $decoded = json_decode($buffer, true);
        return is_array($decoded) ? $decoded : array();
    }
}
