<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

$pageLangFile = PATH_LANGUAGES . 'pages/spider-log/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$layout['title'] .= ' - ' . $L->g('spider-log-title');

if (!function_exists('mgwSpiderLogReadJsonSafe')) {
    function mgwSpiderLogReadJsonSafe($filePath)
    {
        if (!is_string($filePath) || $filePath === '' || !file_exists($filePath)) {
            return array();
        }

        $lines = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return array();
        }

        if (!empty($lines)) {
            $firstLine = trim((string)reset($lines));
            if (strpos($firstLine, '<?php') === 0) {
                array_shift($lines);
            }
        }

        $payload = trim(implode("\n", $lines));
        if ($payload === '') {
            return array();
        }

        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('mgwSpiderLogFormatDateTime')) {
    function mgwSpiderLogFormatDateTime($timestamp)
    {
        if (!is_numeric($timestamp) || (int)$timestamp <= 0) {
            return '--';
        }
        return date('Y-m-d H:i:s', (int)$timestamp);
    }
}

if (!function_exists('mgwSpiderLogFormatDuration')) {
    function mgwSpiderLogFormatDuration($milliseconds)
    {
        if (!is_numeric($milliseconds)) {
            return '--';
        }
        $ms = (float)$milliseconds;
        if ($ms >= 1000) {
            return number_format($ms / 1000, 2) . ' s';
        }
        return number_format($ms, 2) . ' ms';
    }
}

if (!function_exists('mgwSpiderLogFormatBytesLabel')) {
    function mgwSpiderLogFormatBytesLabel($bytes)
    {
        if (!is_numeric($bytes)) {
            return '0 B';
        }
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $value = (float)$bytes;
        $index = 0;
        while ($value >= 1024 && $index < count($units) - 1) {
            $value /= 1024;
            $index++;
        }
        $decimals = $value >= 10 ? 1 : 2;
        return number_format($value, $decimals) . ' ' . $units[$index];
    }
}

if (!function_exists('mgwSpiderLogCatalog')) {
    function mgwSpiderLogCatalog()
    {
        return array(
            array(
                'id' => 'googlebot',
                'label' => 'Googlebot',
                'provider' => 'Google Search',
                'patterns' => array('googlebot', 'apis-google', 'mediapartners-google', 'google-sitemaps'),
                'docs' => 'https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers',
                'tags' => array('search', 'global')
            ),
            array(
                'id' => 'bingbot',
                'label' => 'Bingbot',
                'provider' => 'Microsoft Bing',
                'patterns' => array('bingbot', 'bingpreview'),
                'docs' => 'https://www.bing.com/webmasters/help/which-crawlers-does-bing-use-8c184ec0',
                'tags' => array('search', 'global')
            ),
            array(
                'id' => 'baiduspider',
                'label' => 'Baidu Spider',
                'provider' => 'Baidu',
                'patterns' => array('baiduspider'),
                'docs' => 'https://ziyuan.baidu.com/wiki/907',
                'tags' => array('search', 'china')
            ),
            array(
                'id' => 'sogouspider',
                'label' => 'Sogou Spider',
                'provider' => 'Sogou',
                'patterns' => array('sogou web spider', 'sogou inst spider', 'sosospider', 'sogou spider'),
                'docs' => 'https://www.sogou.com/docs/help/webmasters.htm',
                'tags' => array('search', 'china')
            ),
            array(
                'id' => 'haosouspider',
                'label' => '360 Spider',
                'provider' => '360 Search',
                'patterns' => array('360spider', 'haosou spider', 'qihu spider'),
                'docs' => 'https://www.so.com/help/help_3_2.html',
                'tags' => array('search', 'china')
            ),
            array(
                'id' => 'yandexbot',
                'label' => 'Yandex Bot',
                'provider' => 'Yandex',
                'patterns' => array('yandexbot', 'yandeximages', 'yandexvideo', 'yandexnews'),
                'docs' => 'https://yandex.com/support/webmaster/robot-workings/check-yandex-robots.html',
                'tags' => array('search', 'ru')
            ),
            array(
                'id' => 'duckduckbot',
                'label' => 'DuckDuckBot',
                'provider' => 'DuckDuckGo',
                'patterns' => array('duckduckbot'),
                'docs' => 'https://duckduckgo.com/help/spread',
                'tags' => array('search', 'privacy')
            ),
            array(
                'id' => 'slurp',
                'label' => 'Yahoo! Slurp',
                'provider' => 'Yahoo',
                'patterns' => array('slurp'),
                'docs' => 'https://help.yahoo.com/kb/yahoo-search/SLN22600.html',
                'tags' => array('search', 'global')
            ),
            array(
                'id' => 'seznambot',
                'label' => 'SeznamBot',
                'provider' => 'Seznam',
                'patterns' => array('seznambot'),
                'docs' => 'https://napoveda.seznam.cz/en/full-text-search/seznams-robots/',
                'tags' => array('search', 'cz')
            ),
            array(
                'id' => 'soso',
                'label' => 'SOSO Spider',
                'provider' => 'Tencent',
                'patterns' => array('sosospider'),
                'docs' => '',
                'tags' => array('search', 'china')
            )
        );
    }
}

if (!function_exists('mgwSpiderLogIdentifyAgent')) {
    function mgwSpiderLogIdentifyAgent($ua, $customRules = array())
    {
        $ua = (string)$ua;
        $uaLower = strtolower($ua);

        if (!empty($customRules) && is_array($customRules)) {
            foreach ($customRules as $rule) {
                if (!is_array($rule)) {
                    continue;
                }
                $matched = false;
                $pattern = isset($rule['pattern']) ? (string)$rule['pattern'] : '';
                $regex = isset($rule['regex']) ? (bool)$rule['regex'] : false;

                if ($regex && $pattern !== '') {
                    if (@preg_match($pattern, $ua) === 1) {
                        $matched = true;
                    }
                } elseif ($pattern !== '') {
                    if (stripos($ua, $pattern) !== false) {
                        $matched = true;
                    }
                } elseif (isset($rule['contains'])) {
                    $contains = is_array($rule['contains']) ? $rule['contains'] : array((string)$rule['contains']);
                    foreach ($contains as $needle) {
                        if ($needle !== '' && stripos($ua, $needle) !== false) {
                            $matched = true;
                            break;
                        }
                    }
                }

                if ($matched) {
                    return array(
                        'id' => isset($rule['id']) && $rule['id'] !== '' ? (string)$rule['id'] : 'custom-' . substr(sha1($uaLower), 0, 8),
                        'label' => isset($rule['label']) && $rule['label'] !== '' ? (string)$rule['label'] : 'Custom Spider',
                        'provider' => isset($rule['provider']) ? (string)$rule['provider'] : '',
                        'docs' => isset($rule['docs']) ? (string)$rule['docs'] : '',
                        'confidence' => isset($rule['confidence']) ? (string)$rule['confidence'] : 'custom',
                        'suspicious' => isset($rule['suspicious']) ? (bool)$rule['suspicious'] : false,
                        'tags' => isset($rule['tags']) && is_array($rule['tags']) ? $rule['tags'] : array('custom'),
                        'pattern' => $pattern,
                        'rule' => $rule
                    );
                }
            }
        }

        foreach (mgwSpiderLogCatalog() as $item) {
            if (!isset($item['patterns']) || !is_array($item['patterns'])) {
                continue;
            }
            foreach ($item['patterns'] as $pattern) {
                $pattern = (string)$pattern;
                if ($pattern === '') {
                    continue;
                }
                if ($pattern[0] === '/' && @preg_match($pattern, '') !== false) {
                    if (@preg_match($pattern, $ua) === 1) {
                        return array(
                            'id' => $item['id'],
                            'label' => $item['label'],
                            'provider' => isset($item['provider']) ? $item['provider'] : '',
                            'docs' => isset($item['docs']) ? $item['docs'] : '',
                            'confidence' => 'verified',
                            'suspicious' => false,
                            'tags' => isset($item['tags']) ? $item['tags'] : array('search'),
                            'pattern' => $pattern
                        );
                    }
                } else {
                    if (stripos($uaLower, strtolower($pattern)) !== false) {
                        return array(
                            'id' => $item['id'],
                            'label' => $item['label'],
                            'provider' => isset($item['provider']) ? $item['provider'] : '',
                            'docs' => isset($item['docs']) ? $item['docs'] : '',
                            'confidence' => 'verified',
                            'suspicious' => false,
                            'tags' => isset($item['tags']) ? $item['tags'] : array('search'),
                            'pattern' => $pattern
                        );
                    }
                }
            }
        }

        $containsBotKeyword = strpos($uaLower, 'bot') !== false || strpos($uaLower, 'spider') !== false || strpos($uaLower, 'crawler') !== false;
        $containsBrowserSignature = strpos($uaLower, 'mozilla') !== false || strpos($uaLower, 'chrome/') !== false || strpos($uaLower, 'safari/') !== false;
        $looksSuspicious = false;
        if ($containsBotKeyword) {
            $looksSuspicious = true;
            if (strpos($uaLower, 'http') !== false || strpos($uaLower, '+') !== false) {
                $looksSuspicious = false;
            }
            if ($containsBrowserSignature) {
                $looksSuspicious = true;
            }
        }

        return array(
            'id' => $containsBotKeyword ? 'unverified-bot' : 'unknown-agent',
            'label' => $containsBotKeyword ? 'Unverified Spider' : 'Unknown Agent',
            'provider' => '',
            'docs' => '',
            'confidence' => $containsBotKeyword ? 'low' : 'unknown',
            'suspicious' => $looksSuspicious,
            'tags' => $containsBotKeyword ? array('unverified') : array(),
            'pattern' => ''
        );
    }
}

if (!function_exists('mgwSpiderLogNormalizeEntry')) {
    function mgwSpiderLogNormalizeEntry($entry, $fallbackSite)
    {
        $entry = is_array($entry) ? $entry : array();
        $timestamp = null;
        if (isset($entry['time'])) {
            $timestamp = is_numeric($entry['time']) ? (int)$entry['time'] : strtotime((string)$entry['time']);
        } elseif (isset($entry['timestamp'])) {
            $timestamp = is_numeric($entry['timestamp']) ? (int)$entry['timestamp'] : strtotime((string)$entry['timestamp']);
        } elseif (isset($entry['datetime'])) {
            $timestamp = strtotime((string)$entry['datetime']);
        }
        if (!is_numeric($timestamp) || $timestamp <= 0) {
            $timestamp = time();
        }

        $duration = null;
        if (isset($entry['duration'])) {
            $duration = is_numeric($entry['duration']) ? (float)$entry['duration'] : null;
        } elseif (isset($entry['timeTaken'])) {
            $duration = is_numeric($entry['timeTaken']) ? (float)$entry['timeTaken'] : null;
        }

        return array(
            'time' => $timestamp,
            'site' => isset($entry['site']) ? (string)$entry['site'] : $fallbackSite,
            'ip' => isset($entry['ip']) ? (string)$entry['ip'] : (isset($entry['remoteAddr']) ? (string)$entry['remoteAddr'] : ''),
            'method' => isset($entry['method']) ? strtoupper((string)$entry['method']) : (isset($entry['requestMethod']) ? strtoupper((string)$entry['requestMethod']) : 'GET'),
            'url' => isset($entry['url']) ? (string)$entry['url'] : (isset($entry['path']) ? (string)$entry['path'] : '/'),
            'status' => isset($entry['status']) ? (int)$entry['status'] : (isset($entry['code']) ? (int)$entry['code'] : 0),
            'referer' => isset($entry['referer']) ? (string)$entry['referer'] : (isset($entry['referrer']) ? (string)$entry['referrer'] : ''),
            'ua' => isset($entry['ua']) ? (string)$entry['ua'] : (isset($entry['userAgent']) ? (string)$entry['userAgent'] : ''),
            'bytes' => isset($entry['bytes']) && is_numeric($entry['bytes']) ? (float)$entry['bytes'] : null,
            'duration' => $duration
        );
    }
}

if (!function_exists('mgwSpiderLogAssembleSiteData')) {
    function mgwSpiderLogAssembleSiteData($siteSlug, $siteLabel, $entries, $options, $customRules)
    {
        $options = is_array($options) ? $options : array();
        $summary = isset($options['summary']) && is_array($options['summary']) ? $options['summary'] : array();
        $retentionOption = isset($options['retention']) && is_array($options['retention']) ? $options['retention'] : array();
        $trendOption = isset($options['trend']) && is_array($options['trend']) ? $options['trend'] : array();
        $archivesOption = isset($options['archives']) && is_array($options['archives']) ? $options['archives'] : array();

        $processedEntries = array();
        $suspiciousEntries = array();
        $uniqueIps = array();
        $uniqueBots = array();
        $engineDistribution = array();
        $hourlyBuckets = array();
        $dailyBuckets = array();

        $entries = is_array($entries) ? $entries : array();
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $normalized = mgwSpiderLogNormalizeEntry($entry, $siteSlug);
            $agentInfo = mgwSpiderLogIdentifyAgent($normalized['ua'], $customRules);
            $normalized['engine'] = isset($agentInfo['label']) ? (string)$agentInfo['label'] : 'Unknown';
            $normalized['engineId'] = isset($agentInfo['id']) ? (string)$agentInfo['id'] : '';
            $normalized['engineProvider'] = isset($agentInfo['provider']) ? (string)$agentInfo['provider'] : '';
            $normalized['confidence'] = isset($agentInfo['confidence']) ? (string)$agentInfo['confidence'] : 'unknown';
            $normalized['pattern'] = isset($agentInfo['pattern']) ? (string)$agentInfo['pattern'] : '';
            $normalized['tags'] = isset($agentInfo['tags']) && is_array($agentInfo['tags']) ? $agentInfo['tags'] : array();
            $normalized['docs'] = isset($agentInfo['docs']) ? (string)$agentInfo['docs'] : '';
            $normalized['isSuspicious'] = !empty($agentInfo['suspicious']);

            $processedEntries[] = $normalized;
            if ($normalized['isSuspicious'] && count($suspiciousEntries) < 100) {
                $suspiciousEntries[] = $normalized;
            }
            if ($normalized['ip'] !== '') {
                $uniqueIps[$normalized['ip']] = true;
            }
            $botKey = $normalized['engineId'] !== '' ? $normalized['engineId'] : (string)$normalized['engine'];
            if ($botKey !== '') {
                $uniqueBots[$botKey] = $normalized['engine'];
            }

            $engineKey = $normalized['engine'] !== '' ? $normalized['engine'] : 'Unknown';
            if (!isset($engineDistribution[$engineKey])) {
                $engineDistribution[$engineKey] = array('label' => $engineKey, 'count' => 0, 'suspicious' => 0);
            }
            $engineDistribution[$engineKey]['count']++;
            if ($normalized['isSuspicious']) {
                $engineDistribution[$engineKey]['suspicious']++;
            }

            $hourKey = date('Y-m-d H:00', $normalized['time']);
            if (!isset($hourlyBuckets[$hourKey])) {
                $hourlyBuckets[$hourKey] = array('hits' => 0, 'unique' => array());
            }
            $hourlyBuckets[$hourKey]['hits']++;
            $hourlyBuckets[$hourKey]['unique'][$botKey] = true;

            $dayKey = date('Y-m-d', $normalized['time']);
            if (!isset($dailyBuckets[$dayKey])) {
                $dailyBuckets[$dayKey] = array('hits' => 0, 'unique' => array());
            }
            $dailyBuckets[$dayKey]['hits']++;
            $dailyBuckets[$dayKey]['unique'][$botKey] = true;
        }

        usort($processedEntries, function ($a, $b) {
            return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
        });

        usort($suspiciousEntries, function ($a, $b) {
            return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
        });

        $rawCount = count($processedEntries);
        $totalHits = isset($summary['hits']) ? (int)$summary['hits'] : (isset($summary['total']) ? (int)$summary['total'] : $rawCount);
        if ($totalHits < $rawCount) {
            $totalHits = $rawCount;
        }

        $uniqueIpCount = isset($summary['uniqueIp']) ? (int)$summary['uniqueIp'] : (isset($summary['uniqueIps']) ? (int)$summary['uniqueIps'] : count($uniqueIps));
        $uniqueIpCount = max($uniqueIpCount, count($uniqueIps));

        $uniqueBotCount = isset($summary['uniqueBots']) ? (int)$summary['uniqueBots'] : (isset($summary['uniqueAgents']) ? (int)$summary['uniqueAgents'] : count($uniqueBots));
        $uniqueBotCount = max($uniqueBotCount, count($uniqueBots));

        $siteSuspicious = isset($summary['suspicious']) ? (int)$summary['suspicious'] : count($suspiciousEntries);
        $siteSuspicious = max($siteSuspicious, count($suspiciousEntries));

        $siteLastSeen = isset($summary['lastSeen']) ? (int)$summary['lastSeen'] : (isset($summary['last']) ? (int)$summary['last'] : 0);
        if ($siteLastSeen <= 0 && !empty($processedEntries)) {
            $siteLastSeen = (int)$processedEntries[0]['time'];
        }

        $engineList = array();
        foreach ($engineDistribution as $engineKey => $info) {
            $percent = $totalHits > 0 ? ($info['count'] / $totalHits) * 100 : 0;
            $engineList[] = array(
                'label' => $info['label'],
                'count' => $info['count'],
                'percent' => $percent,
                'suspicious' => $info['suspicious']
            );
        }

        usort($engineList, function ($a, $b) {
            return ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
        });

        $topAgent = array('label' => '—', 'count' => 0, 'percent' => 0, 'suspicious' => 0);
        if (!empty($engineList)) {
            $topAgent = $engineList[0];
        }

        $trendData = array('24h' => array(), '7d' => array(), '30d' => array());
        if (isset($trendOption['24h']) && is_array($trendOption['24h'])) {
            $trendData['24h'] = array_values($trendOption['24h']);
        }
        if (isset($trendOption['7d']) && is_array($trendOption['7d'])) {
            $trendData['7d'] = array_values($trendOption['7d']);
        }
        if (isset($trendOption['30d']) && is_array($trendOption['30d'])) {
            $trendData['30d'] = array_values($trendOption['30d']);
        }

        $now = time();
        if (empty($trendData['24h'])) {
            $trend24 = array();
            for ($i = 23; $i >= 0; $i--) {
                $ts = strtotime(date('Y-m-d H:00:00', $now - $i * 3600));
                $key = date('Y-m-d H:00', $ts);
                $hits = isset($hourlyBuckets[$key]['hits']) ? (int)$hourlyBuckets[$key]['hits'] : 0;
                $unique = isset($hourlyBuckets[$key]['unique']) ? count($hourlyBuckets[$key]['unique']) : 0;
                $trend24[] = array(
                    'label' => date('H:00', $ts),
                    'hits' => $hits,
                    'unique' => $unique,
                    'timestamp' => $ts
                );
            }
            $trendData['24h'] = $trend24;
        }

        if (empty($trendData['7d'])) {
            $trend7 = array();
            for ($i = 6; $i >= 0; $i--) {
                $ts = strtotime(date('Y-m-d 00:00:00', $now - $i * 86400));
                $key = date('Y-m-d', $ts);
                $hits = isset($dailyBuckets[$key]['hits']) ? (int)$dailyBuckets[$key]['hits'] : 0;
                $unique = isset($dailyBuckets[$key]['unique']) ? count($dailyBuckets[$key]['unique']) : 0;
                $trend7[] = array(
                    'label' => date('m-d', $ts),
                    'hits' => $hits,
                    'unique' => $unique,
                    'timestamp' => $ts
                );
            }
            $trendData['7d'] = $trend7;
        }

        if (empty($trendData['30d'])) {
            $trend30 = array();
            for ($i = 29; $i >= 0; $i--) {
                $ts = strtotime(date('Y-m-d 00:00:00', $now - $i * 86400));
                $key = date('Y-m-d', $ts);
                $hits = isset($dailyBuckets[$key]['hits']) ? (int)$dailyBuckets[$key]['hits'] : 0;
                $unique = isset($dailyBuckets[$key]['unique']) ? count($dailyBuckets[$key]['unique']) : 0;
                $trend30[] = array(
                    'label' => date('m-d', $ts),
                    'hits' => $hits,
                    'unique' => $unique,
                    'timestamp' => $ts
                );
            }
            $trendData['30d'] = $trend30;
        }

        $archivesList = array();
        if (!empty($archivesOption)) {
            foreach ($archivesOption as $archive) {
                if (!is_array($archive)) {
                    continue;
                }
                $archivesList[] = array(
                    'type' => isset($archive['type']) ? (string)$archive['type'] : 'custom',
                    'label' => isset($archive['label']) ? (string)$archive['label'] : 'Archive',
                    'count' => isset($archive['count']) ? (int)$archive['count'] : 0,
                    'size' => isset($archive['size']) ? (string)$archive['size'] : mgwSpiderLogFormatBytesLabel(isset($archive['bytes']) ? (float)$archive['bytes'] : 0),
                    'path' => isset($archive['path']) ? (string)$archive['path'] : '',
                    'updated' => isset($archive['updated']) ? (int)$archive['updated'] : 0
                );
            }
        } else {
            $dailyList = array();
            for ($i = 0; $i < 7; $i++) {
                $dayTs = strtotime(date('Y-m-d 00:00:00', $now - $i * 86400));
                $key = date('Y-m-d', $dayTs);
                $hits = isset($dailyBuckets[$key]['hits']) ? (int)$dailyBuckets[$key]['hits'] : 0;
                $dailyList[] = array(
                    'type' => 'daily',
                    'label' => $key,
                    'count' => $hits,
                    'size' => mgwSpiderLogFormatBytesLabel(max($hits, 1) * 480),
                    'path' => '',
                    'updated' => $dayTs
                );
            }

            $weeklyList = array();
            for ($i = 0; $i < 4; $i++) {
                $weekStart = strtotime('monday this week -' . $i . ' week', $now);
                $weekEnd = strtotime('+6 days', $weekStart);
                $weekLabel = date('Y-m-d', $weekStart) . ' ~ ' . date('Y-m-d', $weekEnd);
                $hits = 0;
                for ($d = 0; $d < 7; $d++) {
                    $key = date('Y-m-d', strtotime('+' . $d . ' day', $weekStart));
                    $hits += isset($dailyBuckets[$key]['hits']) ? (int)$dailyBuckets[$key]['hits'] : 0;
                }
                $weeklyList[] = array(
                    'type' => 'weekly',
                    'label' => $weekLabel,
                    'count' => $hits,
                    'size' => mgwSpiderLogFormatBytesLabel(max($hits, 1) * 520),
                    'path' => '',
                    'updated' => $weekEnd
                );
            }

            $monthlyList = array();
            for ($i = 0; $i < 3; $i++) {
                $monthTs = strtotime(date('Y-m-01 00:00:00', strtotime('-' . $i . ' month', $now)));
                $monthLabel = date('Y-m', $monthTs);
                $hits = 0;
                $daysInMonth = (int)date('t', $monthTs);
                for ($d = 0; $d < $daysInMonth; $d++) {
                    $key = date('Y-m-d', strtotime('+' . $d . ' day', $monthTs));
                    $hits += isset($dailyBuckets[$key]['hits']) ? (int)$dailyBuckets[$key]['hits'] : 0;
                }
                $monthlyList[] = array(
                    'type' => 'monthly',
                    'label' => $monthLabel,
                    'count' => $hits,
                    'size' => mgwSpiderLogFormatBytesLabel(max($hits, 1) * 580),
                    'path' => '',
                    'updated' => strtotime(date('Y-m-t 23:59:59', $monthTs))
                );
            }

            $archivesList = array_merge($dailyList, $weeklyList, $monthlyList);
        }

        $retention = array(
            'days' => isset($retentionOption['days']) ? (int)$retentionOption['days'] : (isset($summary['retentionDays']) ? (int)$summary['retentionDays'] : 30),
            'autoClean' => isset($retentionOption['autoClean']) ? (bool)$retentionOption['autoClean'] : (isset($summary['autoClean']) ? (bool)$summary['autoClean'] : true),
            'lastClean' => isset($retentionOption['lastClean']) ? (int)$retentionOption['lastClean'] : (isset($summary['lastClean']) ? (int)$summary['lastClean'] : (time() - 86400)),
            'nextClean' => isset($retentionOption['nextClean']) ? (int)$retentionOption['nextClean'] : (isset($summary['nextClean']) ? (int)$summary['nextClean'] : (time() + 86400 * 3))
        );

        $limitedLogs = array_slice($processedEntries, 0, 150);
        $limitedSuspicious = array_slice($suspiciousEntries, 0, 50);

        return array(
            'data' => array(
                'slug' => $siteSlug,
                'label' => $siteLabel,
                'totalHits' => $totalHits,
                'uniqueIps' => $uniqueIpCount,
                'uniqueBots' => $uniqueBotCount,
                'suspicious' => $siteSuspicious,
                'lastSeen' => $siteLastSeen,
                'topAgent' => $topAgent,
                'trend' => $trendData,
                'source' => array('total' => $totalHits, 'engines' => $engineList),
                'archives' => $archivesList,
                'logs' => $limitedLogs,
                'suspiciousLogs' => $limitedSuspicious,
                'retention' => $retention,
                'rawCount' => $rawCount
            ),
            'uniqueIps' => array_keys($uniqueIps),
            'uniqueBots' => $uniqueBots
        );
    }
}

if (!function_exists('mgwSpiderLogBuildSample')) {
    function mgwSpiderLogBuildSample($customRules)
    {
        $sampleEntries = array(
            array(
                'time' => time() - 180,
                'site' => 'example.com',
                'ip' => '66.249.66.1',
                'method' => 'GET',
                'url' => '/products/landing',
                'status' => 200,
                'referer' => 'https://www.google.com/',
                'ua' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                'duration' => 142
            ),
            array(
                'time' => time() - 520,
                'site' => 'example.com',
                'ip' => '157.55.39.96',
                'method' => 'GET',
                'url' => '/blog/seo-guide',
                'status' => 200,
                'referer' => '',
                'ua' => 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
                'duration' => 210
            ),
            array(
                'time' => time() - 820,
                'site' => 'example.com',
                'ip' => '116.179.32.24',
                'method' => 'GET',
                'url' => '/sitemap.xml',
                'status' => 200,
                'referer' => '',
                'ua' => 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
                'duration' => 165
            ),
            array(
                'time' => time() - 930,
                'site' => 'example.com',
                'ip' => '207.46.13.54',
                'method' => 'GET',
                'url' => '/hidden/login',
                'status' => 403,
                'referer' => '',
                'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0 Safari/537.36 Baiduspider',
                'duration' => 312
            ),
            array(
                'time' => time() - 1200,
                'site' => 'example.com',
                'ip' => '87.250.233.84',
                'method' => 'GET',
                'url' => '/ru/catalog',
                'status' => 200,
                'referer' => '',
                'ua' => 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)',
                'duration' => 186
            ),
            array(
                'time' => time() - 1600,
                'site' => 'example.com',
                'ip' => '203.0.113.254',
                'method' => 'GET',
                'url' => '/admin/login',
                'status' => 200,
                'referer' => '',
                'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/116.0.0.0 Safari/537.36',
                'duration' => 240
            )
        );

        $sampleSummary = array(
            'hits' => 428,
            'uniqueIp' => 42,
            'uniqueBots' => 9,
            'suspicious' => 3,
            'lastSeen' => time() - 120
        );

        $sampleTrend = array(
            '7d' => array(
                array('label' => date('m-d', strtotime('-6 days')), 'hits' => 120, 'unique' => 6),
                array('label' => date('m-d', strtotime('-5 days')), 'hits' => 140, 'unique' => 7),
                array('label' => date('m-d', strtotime('-4 days')), 'hits' => 165, 'unique' => 8),
                array('label' => date('m-d', strtotime('-3 days')), 'hits' => 184, 'unique' => 9),
                array('label' => date('m-d', strtotime('-2 days')), 'hits' => 196, 'unique' => 10),
                array('label' => date('m-d', strtotime('-1 day')), 'hits' => 205, 'unique' => 10),
                array('label' => date('m-d'), 'hits' => 212, 'unique' => 11)
            )
        );

        $sampleRetention = array(
            'days' => 45,
            'autoClean' => true,
            'lastClean' => time() - 43200,
            'nextClean' => time() + 172800
        );

        return mgwSpiderLogAssembleSiteData('example.com', 'example.com', $sampleEntries, array(
            'summary' => $sampleSummary,
            'trend' => $sampleTrend,
            'retention' => $sampleRetention
        ), $customRules);
    }
}

$customRules = array();
$customRuleCandidates = array(
    PATH_ROOT . 'mgw-config' . DS . 'spiders' . DS . '_rules.php',
    PATH_ROOT . 'mgw-config' . DS . 'spiders' . DS . '_rules.json'
);
foreach ($customRuleCandidates as $candidate) {
    $rules = mgwSpiderLogReadJsonSafe($candidate);
    if (!empty($rules)) {
        $customRules = $rules;
        break;
    }
}

$spiderRoot = PATH_ROOT . 'mgw-config' . DS . 'spiders' . DS;
$siteDirectories = is_dir($spiderRoot) ? glob($spiderRoot . '*', GLOB_ONLYDIR) : array();
if ($siteDirectories === false) {
    $siteDirectories = array();
}

$selectedSite = isset($_GET['site']) ? (string)$_GET['site'] : '';
$selectedSite = preg_replace('/[^A-Za-z0-9\.\-]/', '', $selectedSite);

$siteDataMap = array();
$siteOptions = array();

$globalHits = 0;
$globalUniqueIps = array();
$globalUniqueBots = array();
$globalSuspicious = 0;
$globalLastSeen = 0;

foreach ($siteDirectories as $directory) {
    $siteSlug = basename($directory);
    if ($siteSlug === '' || $siteSlug[0] === '.') {
        continue;
    }

    $siteLabel = $siteSlug;
    $siteMetaFile = PATH_ROOT . 'mgw-content' . DS . $siteSlug . DS . 'databases' . DS . 'site.php';
    $siteMeta = mgwSpiderLogReadJsonSafe($siteMetaFile);
    if (isset($siteMeta['title']) && is_string($siteMeta['title']) && $siteMeta['title'] !== '') {
        $siteLabel = $siteMeta['title'];
    }

    $bundle = array();
    $bundleCandidates = array('index.php', 'index.json', 'summary.php', 'summary.json', 'spider-log.php', 'spider-log.json', 'latest.php', 'latest.json', 'metrics.php', 'metrics.json');
    foreach ($bundleCandidates as $candidate) {
        $data = mgwSpiderLogReadJsonSafe($directory . DS . $candidate);
        if (!empty($data)) {
            $bundle = $data;
            break;
        }
    }

    $entries = array();
    $entryCandidates = array('recent.php', 'recent.json', 'logs.php', 'logs.json', 'entries.php', 'entries.json');
    foreach ($entryCandidates as $candidate) {
        $data = mgwSpiderLogReadJsonSafe($directory . DS . $candidate);
        if (empty($data)) {
            continue;
        }
        if (isset($data['logs']) && is_array($data['logs'])) {
            $entries = $data['logs'];
            break;
        }
        if (isset($data['entries']) && is_array($data['entries'])) {
            $entries = $data['entries'];
            break;
        }
        if (array_values($data) === $data) {
            $entries = $data;
            break;
        }
    }

    if (empty($entries)) {
        if (isset($bundle['logs']) && is_array($bundle['logs'])) {
            $entries = $bundle['logs'];
        } elseif (isset($bundle['entries']) && is_array($bundle['entries'])) {
            $entries = $bundle['entries'];
        } elseif (isset($bundle['recent']) && is_array($bundle['recent'])) {
            $entries = $bundle['recent'];
        }
    }

    $assembled = mgwSpiderLogAssembleSiteData(
        $siteSlug,
        $siteLabel,
        $entries,
        array(
            'summary' => isset($bundle['summary']) ? $bundle['summary'] : (isset($bundle['totals']) ? $bundle['totals'] : array()),
            'trend' => isset($bundle['trend']) ? $bundle['trend'] : array(),
            'archives' => isset($bundle['archives']) ? $bundle['archives'] : array(),
            'retention' => isset($bundle['retention']) ? $bundle['retention'] : array()
        ),
        $customRules
    );

    $siteData = $assembled['data'];
    $siteDataMap[$siteSlug] = $siteData;

    $siteOptions[] = array(
        'value' => $siteSlug,
        'label' => $siteLabel,
        'hits' => $siteData['totalHits'],
        'suspicious' => $siteData['suspicious']
    );

    $globalHits += $siteData['totalHits'];
    foreach ($assembled['uniqueIps'] as $ip) {
        $globalUniqueIps[$ip] = true;
    }
    foreach ($assembled['uniqueBots'] as $id => $label) {
        if ($id === '') {
            $id = md5($label);
        }
        $globalUniqueBots[$id] = $label;
    }
    $globalSuspicious += $siteData['suspicious'];
    if ($siteData['lastSeen'] > $globalLastSeen) {
        $globalLastSeen = $siteData['lastSeen'];
    }
}

if (empty($siteDataMap)) {
    $sample = mgwSpiderLogBuildSample($customRules);
    $siteData = $sample['data'];
    $siteDataMap[$siteData['slug']] = $siteData;
    $siteOptions[] = array(
        'value' => $siteData['slug'],
        'label' => $siteData['label'],
        'hits' => $siteData['totalHits'],
        'suspicious' => $siteData['suspicious']
    );
    $globalHits += $siteData['totalHits'];
    foreach ($sample['uniqueIps'] as $ip) {
        $globalUniqueIps[$ip] = true;
    }
    foreach ($sample['uniqueBots'] as $id => $label) {
        if ($id === '') {
            $id = md5($label);
        }
        $globalUniqueBots[$id] = $label;
    }
    $globalSuspicious += $siteData['suspicious'];
    if ($siteData['lastSeen'] > $globalLastSeen) {
        $globalLastSeen = $siteData['lastSeen'];
    }
}

if ($selectedSite === '' || !isset($siteDataMap[$selectedSite])) {
    $selectedSite = key($siteDataMap);
}

$activeSite = isset($siteDataMap[$selectedSite]) ? $siteDataMap[$selectedSite] : reset($siteDataMap);
$activeSite = is_array($activeSite) ? $activeSite : array();

$spiderTrend = isset($activeSite['trend']) ? $activeSite['trend'] : array();
$spiderSource = isset($activeSite['source']) ? $activeSite['source'] : array();
$spiderArchives = isset($activeSite['archives']) ? $activeSite['archives'] : array();
$spiderLogs = isset($activeSite['logs']) ? $activeSite['logs'] : array();
$spiderSuspicious = isset($activeSite['suspiciousLogs']) ? $activeSite['suspiciousLogs'] : array();
$spiderRetention = isset($activeSite['retention']) ? $activeSite['retention'] : array();

$spiderSiteSummary = array();
foreach ($siteDataMap as $item) {
    $spiderSiteSummary[] = array(
        'slug' => $item['slug'],
        'label' => $item['label'],
        'totalHits' => $item['totalHits'],
        'uniqueBots' => $item['uniqueBots'],
        'suspicious' => $item['suspicious'],
        'lastSeen' => $item['lastSeen'],
        'topAgent' => $item['topAgent']
    );
}

$spiderOverviewCards = array();
$spiderOverviewCards[] = array(
    'label' => $L->g('spider-log-card-total'),
    'value' => number_format($globalHits),
    'hint' => $L->g('spider-log-card-total-hint')
);
$spiderOverviewCards[] = array(
    'label' => $L->g('spider-log-card-unique'),
    'value' => number_format(count($globalUniqueBots)),
    'hint' => $L->g('spider-log-card-unique-hint')
);
$spiderOverviewCards[] = array(
    'label' => $L->g('spider-log-card-suspicious'),
    'value' => number_format($globalSuspicious),
    'hint' => $L->g('spider-log-card-suspicious-hint'),
    'status' => $globalSuspicious > 0 ? 'warn' : 'ok'
);
$spiderOverviewCards[] = array(
    'label' => $L->g('spider-log-card-last'),
    'value' => $globalLastSeen > 0 ? mgwSpiderLogFormatDateTime($globalLastSeen) : '--',
    'hint' => $L->g('spider-log-card-last-hint'),
    'time' => $globalLastSeen
);

$spiderBuiltinRules = mgwSpiderLogCatalog();
$spiderCustomRules = is_array($customRules) ? $customRules : array();
$spiderRuleStats = array(
    'builtin' => count($spiderBuiltinRules),
    'custom' => count($spiderCustomRules)
);

extract(array(
    'spiderOverviewCards' => $spiderOverviewCards,
    'spiderTrend' => $spiderTrend,
    'spiderSource' => $spiderSource,
    'spiderArchives' => $spiderArchives,
    'spiderLogs' => $spiderLogs,
    'spiderSuspicious' => $spiderSuspicious,
    'spiderRetention' => $spiderRetention,
    'spiderSiteSummary' => $spiderSiteSummary,
    'siteOptions' => $siteOptions,
    'selectedSite' => $selectedSite,
    'spiderRuleStats' => $spiderRuleStats,
    'spiderBuiltinRules' => $spiderBuiltinRules,
    'spiderCustomRules' => $spiderCustomRules
), EXTR_OVERWRITE);
