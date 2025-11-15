<?php defined('MAIGEWAN') or die('Maigewan CMS.');

// Keep page title aligned with admin layout conventions
$layout['title'] .= ' - '.$L->g('about-page-title');

// Load page-specific language strings to keep the layout translatable.
$aboutLanguageDir = PATH_LANGUAGES.'pages'.DS.'about'.DS;
$aboutLanguageFiles = array();

$defaultAboutLanguage = $aboutLanguageDir.DEFAULT_LANGUAGE_FILE;
if (Sanitize::pathFile($defaultAboutLanguage)) {
    $aboutLanguageFiles[] = $defaultAboutLanguage;
}

$currentAboutLanguage = $aboutLanguageDir.$L->currentLanguage().'.json';
if ($currentAboutLanguage !== $defaultAboutLanguage && Sanitize::pathFile($currentAboutLanguage)) {
    $aboutLanguageFiles[] = $currentAboutLanguage;
}

if (!empty($aboutLanguageFiles)) {
    $aboutDictionary = array();
    foreach ($aboutLanguageFiles as $languageFile) {
        $languageData = new dbJSON($languageFile, false);
        if (!empty($languageData->db) && is_array($languageData->db)) {
            $aboutDictionary = array_merge($aboutDictionary, $languageData->db);
        }
    }

    if (!empty($aboutDictionary)) {
        $L->add($aboutDictionary);
    }
}

if (!function_exists('mgw_about_cpu_cores')) {
    function mgw_about_cpu_cores()
    {
        $coreCount = null;

        if (function_exists('shell_exec')) {
            $output = @shell_exec('nproc 2>/dev/null');
            if (is_string($output) && trim($output) !== '') {
                $coreCount = (int) trim($output);
            }
        }

        if ($coreCount === null && is_readable('/proc/cpuinfo')) {
            $data = @file_get_contents('/proc/cpuinfo');
            if ($data !== false) {
                $coreCount = substr_count($data, "processor\t:");
                if ($coreCount <= 0) {
                    $coreCount = substr_count($data, 'processor\t');
                }
                if ($coreCount <= 0) {
                    $coreCount = null;
                }
            }
        }

        if ($coreCount === null && function_exists('sys_getloadavg')) {
            $coreCount = count(sys_getloadavg());
        }

        return ($coreCount && $coreCount > 0) ? $coreCount : null;
    }
}

if (!function_exists('mgw_about_memory_info')) {
    function mgw_about_memory_info()
    {
        $info = array(
            'total' => null,
            'available' => null,
            'used' => null,
            'usedPercentage' => null,
            'phpUsage' => memory_get_usage(true)
        );

        if (is_readable('/proc/meminfo')) {
            $meminfo = @file_get_contents('/proc/meminfo');
            if ($meminfo !== false) {
                preg_match('/^MemTotal:\s+(\d+)/mi', $meminfo, $matchesTotal);
                preg_match('/^MemAvailable:\s+(\d+)/mi', $meminfo, $matchesAvailable);

                if (!empty($matchesTotal[1])) {
                    $info['total'] = (int) $matchesTotal[1] * 1024;
                }
                if (!empty($matchesAvailable[1])) {
                    $info['available'] = (int) $matchesAvailable[1] * 1024;
                }

                if ($info['total'] !== null && $info['available'] !== null) {
                    $info['used'] = max($info['total'] - $info['available'], 0);
                    if ($info['total'] > 0) {
                        $info['usedPercentage'] = round(($info['used'] / $info['total']) * 100, 1);
                    }
                }
            }
        }

        return $info;
    }
}

if (!function_exists('mgw_about_disk_info')) {
    function mgw_about_disk_info()
    {
        $total = @disk_total_space(PATH_ROOT);
        $free = @disk_free_space(PATH_ROOT);

        if ($total !== false && $total > 0 && $free !== false) {
            $used = max($total - $free, 0);
            $percent = round(($used / $total) * 100, 1);
        } else {
            $used = null;
            $percent = null;
        }

        return array(
            'total' => $total !== false ? $total : null,
            'free' => $free !== false ? $free : null,
            'used' => $used,
            'usedPercentage' => $percent,
            'appFootprint' => Filesystem::getSize(PATH_ROOT)
        );
    }
}

if (!function_exists('mgw_about_commits')) {
    function mgw_about_commits($limit = 3)
    {
        $limit = max((int) $limit, 1);

        $url = 'https://api.github.com/repos/3mmmbulus/mg-bludit/commits?sha=main&per_page=' . $limit;
        $response = null;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 6,
                CURLOPT_USERAGENT => 'MaigewanCMS/AboutPage'
            ));
            $response = curl_exec($ch);
            curl_close($ch);
        } elseif (ini_get('allow_url_fopen')) {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'header' => "User-Agent: MaigewanCMS/AboutPage\r\n",
                    'timeout' => 6
                )
            ));
            $response = @file_get_contents($url, false, $context);
        }

        if (!$response) {
            return array();
        }

        $payload = json_decode($response, true);
        if (!is_array($payload)) {
            return array();
        }

        $commits = array();
        foreach ($payload as $entry) {
            if (count($commits) >= $limit) {
                break;
            }
            $message = $entry['commit']['message'] ?? '';
            $firstLine = trim((string) strtok($message, "\n"));
            $commits[] = array(
                'hash' => substr($entry['sha'] ?? '', 0, 7),
                'author' => $entry['commit']['author']['name'] ?? 'unknown',
                'url' => $entry['html_url'] ?? '',
                'published' => $entry['commit']['author']['date'] ?? null,
                'headline' => $firstLine !== '' ? $firstLine : 'Update'
            );
        }

        return $commits;
    }
}

$systemOverview = array(
    'edition' => defined('MAIGEWAN_PRO') ? 'PRO' : 'Standard',
    'codename' => defined('MAIGEWAN_CODENAME') ? MAIGEWAN_CODENAME : 'N/A',
    'build' => defined('MAIGEWAN_BUILD') ? MAIGEWAN_BUILD : 'N/A',
    'version' => defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : 'N/A',
    'releaseDate' => defined('MAIGEWAN_RELEASE_DATE') ? MAIGEWAN_RELEASE_DATE : null
);

$hostname = gethostname();
$serverIp = $_SERVER['SERVER_ADDR'] ?? null;
if (!$serverIp && $hostname) {
    $serverIp = @gethostbyname($hostname);
}

$appConfigPath = realpath(PATH_CONFIG) ?: PATH_CONFIG;

$environmentInfo = array(
    'runtime' => php_uname(),
    'phpVersion' => PHP_VERSION,
    'phpSapi' => PHP_SAPI,
    'hostname' => $hostname ?: 'N/A',
    'ip' => $serverIp ?: 'N/A',
    'configPath' => $appConfigPath,
    'applicationFootprint' => Filesystem::getSize(PATH_ROOT)
);

$serverStatus = array(
    'cpu' => array(
        'cores' => mgw_about_cpu_cores(),
        'loadAverage' => function_exists('sys_getloadavg') ? sys_getloadavg() : array()
    ),
    'memory' => mgw_about_memory_info(),
    'disk' => mgw_about_disk_info()
);

$dependencyMatrix = array(
    array(
        'label' => $L->g('about-dependency-openssl-label'),
        'extension' => 'openssl',
        'description' => $L->g('about-dependency-openssl-desc')
    ),
    array(
        'label' => $L->g('about-dependency-curl-label'),
        'extension' => 'curl',
        'description' => $L->g('about-dependency-curl-desc')
    ),
    array(
        'label' => $L->g('about-dependency-gd-label'),
        'extension' => 'gd',
        'description' => $L->g('about-dependency-gd-desc')
    ),
    array(
        'label' => $L->g('about-dependency-mbstring-label'),
        'extension' => 'mbstring',
        'description' => $L->g('about-dependency-mbstring-desc')
    ),
    array(
        'label' => $L->g('about-dependency-pdo-label'),
        'extension' => 'pdo',
        'description' => $L->g('about-dependency-pdo-desc')
    ),
    array(
        'label' => $L->g('about-dependency-pdo-mysql-label'),
        'extension' => 'pdo_mysql',
        'description' => $L->g('about-dependency-pdo-mysql-desc')
    ),
    array(
        'label' => $L->g('about-dependency-fileinfo-label'),
        'extension' => 'fileinfo',
        'description' => $L->g('about-dependency-fileinfo-desc')
    ),
    array(
        'label' => $L->g('about-dependency-dom-label'),
        'extension' => 'dom',
        'description' => $L->g('about-dependency-dom-desc')
    ),
    array(
        'label' => $L->g('about-dependency-zip-label'),
        'extension' => 'zip',
        'description' => $L->g('about-dependency-zip-desc')
    ),
    array(
        'label' => $L->g('about-dependency-redis-label'),
        'extension' => 'redis',
        'description' => $L->g('about-dependency-redis-desc')
    )
);

foreach ($dependencyMatrix as $index => $dependency) {
    $dependencyMatrix[$index]['loaded'] = extension_loaded($dependency['extension']);
}

$latestCommits = mgw_about_commits(3);

$teamCredits = array(
    array(
        'group' => $L->g('about-team-group-core'),
        'members' => array(
            array('name' => '3mmmbulus', 'role' => $L->g('about-team-role-project-lead'), 'link' => 'https://github.com/3mmmbulus'),
            array('name' => $L->g('about-team-member-contributors'), 'role' => $L->g('about-team-role-core-engineering'), 'link' => 'https://github.com/3mmmbulus/mg-bludit/graphs/contributors')
        )
    ),
    array(
        'group' => $L->g('about-team-group-design'),
        'members' => array(
            array('name' => 'Martial Pixel', 'role' => $L->g('about-team-role-design-partner'), 'link' => 'https://martialpixel.com'),
            array('name' => $L->g('about-team-member-reviewers'), 'role' => $L->g('about-team-role-community-reviewers'))
        )
    ),
    array(
        'group' => $L->g('about-team-group-thanks'),
        'members' => array(
            array('name' => 'Bludit Project', 'role' => $L->g('about-team-role-bludit-foundation'), 'link' => 'https://www.bludit.com'),
            array('name' => $L->g('about-team-member-oss-community'), 'role' => $L->g('about-team-role-open-source'))
        )
    )
);

$contactChannels = array(
    array('type' => 'email', 'typeLabel' => $L->g('about-contact-type-email'), 'label' => 'support@maigewan.com', 'url' => 'mailto:support@maigewan.com'),
    array('type' => 'telegram', 'typeLabel' => $L->g('about-contact-type-telegram'), 'label' => '@maigewan', 'url' => 'https://t.me/maigewan'),
    array('type' => 'website', 'typeLabel' => $L->g('about-contact-type-website'), 'label' => 'maigewan.com/support', 'url' => 'https://www.maigewan.com/support')
);

$licenseNotes = $L->g('about-license-thanks');
