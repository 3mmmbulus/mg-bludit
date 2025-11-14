<?php defined('MAIGEWAN') or die('Maigewan CMS.');

/**
 * SiteGroups
 *
 * 管理站点分组批次数据的工具类，通过 JSON 文件持久化每个批次的信息。
 */
class SiteGroups
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var array
     */
    protected $allowedTypes = array('single', 'multi');

    /**
     * @var array
     */
    protected $allowedCategories = array(
        'enterprise',
        'blog',
        'news',
        'commerce',
        'community',
        'directory',
        'forum',
        'tools',
        'video',
        'image',
        'download',
        'document',
        'qa',
        'other'
    );

    /**
     * @var array
     */
    protected $allowedRedirectPolicies = array('at_to_www', 'force_www', 'off');

    /**
     * SiteGroups constructor.
     *
     * @param string|null $basePath 自定义数据目录，默认使用 mgw-config/site-groups/
     */
    public function __construct($basePath = null)
    {
        $this->basePath = $basePath ?: PATH_CONFIG . 'site-groups' . DS;
        if (!is_dir($this->basePath)) {
            @mkdir($this->basePath, DIR_PERMISSIONS, true);
        }
    }

    public function getAllowedTypes()
    {
        return $this->allowedTypes;
    }

    public function getAllowedCategories()
    {
        return $this->allowedCategories;
    }

    public function getAllowedRedirectPolicies()
    {
        return $this->allowedRedirectPolicies;
    }

    /**
     * 列出所有可用的批次日期（目录名）。
     *
     * @return array
     */
    public function getAvailableDates()
    {
        if (!is_dir($this->basePath)) {
            return array();
        }

        $dates = array();
        foreach (glob($this->basePath . '*', GLOB_ONLYDIR) as $dir) {
            $dates[] = basename($dir);
        }

        rsort($dates);
        return $dates;
    }

    /**
     * 返回指定批次日期的所有分组。
     * 如果未指定日期，则返回全部分组数据。
     *
     * @param string|null $date yyyy-mm-dd
     * @return array
     */
    public function listGroups($date = null)
    {
        $results = array();

        if ($date !== null) {
            $date = $this->normalizeDate($date);
            $results = $this->loadDateGroups($date);
        } else {
            foreach ($this->getAvailableDates() as $batchDate) {
                $results = array_merge($results, $this->loadDateGroups($batchDate));
            }
        }

        usort($results, function ($a, $b) {
            $timeA = $a['created_at'] ?? '';
            $timeB = $b['created_at'] ?? '';
            return strcmp($timeB, $timeA);
        });

        return $results;
    }

    /**
     * 获取指定批次中的单个分组。
     *
     * @param string $groupId
     * @param string $date
     * @return array|null
     */
    public function getGroup($groupId, $date)
    {
        $groupId = trim($groupId);
        $date = $this->normalizeDate($date);

        if ($groupId === '') {
            return null;
        }

        $file = $this->buildFilePath($date, $groupId);
        if (!file_exists($file)) {
            return null;
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            return null;
        }

        $data['group_id'] = $data['group_id'] ?? $groupId;
        $data['batch_date'] = $data['batch_date'] ?? $date;
        $data['domains'] = $this->normalizeDomains($data['domains'] ?? array());
        $data['site_count'] = $this->normalizeCount($data);
        $data['type'] = $this->sanitizeType($data['type'] ?? 'single');
        $data['category'] = $this->sanitizeCategory($data['category'] ?? ($data['mode'] ?? 'other'));
        $data['mode'] = $data['category'];
        $data['redirect_policy'] = $this->sanitizeRedirectPolicy($data['redirect_policy'] ?? 'off');
        $data['image_localization'] = $this->sanitizeToggle($data['image_localization'] ?? 'off');
        $data['image_rename'] = $data['image_localization'] === 'on'
            ? $this->sanitizeToggle($data['image_rename'] ?? 'off')
            : 'off';
        $data['article_image_count'] = $this->sanitizeArticleImageCount($data['article_image_count'] ?? 1);
        $data['article_thumbnail_first'] = $this->sanitizeToggle($data['article_thumbnail_first'] ?? 'off');
        $data['domain_templates'] = $this->sanitizeDomainTemplates($data['domains'], $data['domain_templates'] ?? array());

        return $data;
    }

    /**
     * 创建或更新分组。
     *
     * @param array $data
     * @return array 保存后的数据
     */
    public function saveGroup(array $data)
    {
        $now = date('Y-m-d H:i:s');
        $date = $this->normalizeDate($data['batch_date'] ?? date('Y-m-d'));
        $this->ensureDateDirectory($date);

        $groupId = trim($data['group_id'] ?? '');
        if ($groupId === '') {
            $groupId = $this->generateGroupId($date);
        }

        $existing = $this->getGroup($groupId, $date);

        $domains = $this->normalizeDomains($data['domains'] ?? array());
        $type = $this->sanitizeType($data['type'] ?? 'single');
        $category = $this->sanitizeCategory($data['category'] ?? ($data['mode'] ?? 'other'));
        $redirectPolicy = $this->sanitizeRedirectPolicy($data['redirect_policy'] ?? 'off');
        $imageLocalization = $this->sanitizeToggle($data['image_localization'] ?? 'off');
        $imageRename = $imageLocalization === 'on'
            ? $this->sanitizeToggle($data['image_rename'] ?? 'off')
            : 'off';
        $articleImageCount = $this->sanitizeArticleImageCount($data['article_image_count'] ?? 1);
        $articleThumbnailFirst = $this->sanitizeToggle($data['article_thumbnail_first'] ?? 'off');
        $domainTemplates = $this->sanitizeDomainTemplates($domains, $data['domain_templates'] ?? array());

        $payload = array(
            'group_id'   => $groupId,
            'group_name' => trim($data['group_name'] ?? ''),
            'type'       => $type,
            'mode'       => $category,
            'category'   => $category,
            'redirect_policy' => $redirectPolicy,
            'image_localization' => $imageLocalization,
            'image_rename' => $imageRename,
            'article_image_count' => $articleImageCount,
            'article_thumbnail_first' => $articleThumbnailFirst,
            'domain_templates' => $domainTemplates,
            'domains'    => $domains,
            'note'       => trim($data['note'] ?? ''),
            'status'     => trim($data['status'] ?? 'active'),
            'created_at' => $existing['created_at'] ?? ($data['created_at'] ?? $now),
            'updated_at' => $now,
            'created_by' => trim($data['created_by'] ?? ($existing['created_by'] ?? (Session::get('username') ?: 'system'))),
            'site_count' => $this->normalizeCount($data),
            'batch_date' => $date
        );

        // 如果传入 site_count 为 0，则自动根据域名数量计算
        if ($payload['site_count'] <= 0) {
            $payload['site_count'] = count($payload['domains']);
        }

        $file = $this->buildFilePath($date, $groupId);
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($file, $json);

        return $payload;
    }

    /**
     * 删除指定分组。
     *
     * @param string $groupId
     * @param string $date
     * @return bool
     */
    public function deleteGroup($groupId, $date)
    {
        $groupId = trim($groupId);
        $date = $this->normalizeDate($date);

        if ($groupId === '') {
            return false;
        }

        $file = $this->buildFilePath($date, $groupId);
        if (!file_exists($file)) {
            return false;
        }

        return unlink($file);
    }

    /**
     * 根据批次生成新的分组 ID (g001, g002 ...)。
     *
     * @param string $date
     * @return string
     */
    public function generateGroupId($date)
    {
        $date = $this->normalizeDate($date);
        $groups = $this->loadDateGroups($date);

        $max = 0;
        foreach ($groups as $group) {
            if (!empty($group['group_id']) && preg_match('/^g(\d{3})$/', $group['group_id'], $matches)) {
                $value = (int)$matches[1];
                if ($value > $max) {
                    $max = $value;
                }
            }
        }

        $next = $max + 1;
        return 'g' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 统计总数信息。
     *
     * @param array|null $groups
     * @return array
     */
    public function summarize(array $groups = null)
    {
        $groups = $groups ?? $this->listGroups();
        $totalGroups = count($groups);
        $totalSites = 0;
        $activeGroups = 0;

        foreach ($groups as $group) {
            $totalSites += (int)($group['site_count'] ?? 0);
            if (($group['status'] ?? '') === 'active') {
                $activeGroups++;
            }
        }

        return array(
            'total_groups' => $totalGroups,
            'total_sites' => $totalSites,
            'active_groups' => $activeGroups
        );
    }

    // ---------------------------------------------------------------------
    // Internals
    // ---------------------------------------------------------------------

    protected function ensureDateDirectory($date)
    {
        $dir = $this->basePath . $date;
        if (!is_dir($dir)) {
            @mkdir($dir, DIR_PERMISSIONS, true);
        }
    }

    protected function loadDateGroups($date)
    {
        $dir = $this->basePath . $date . DS;
        if (!is_dir($dir)) {
            return array();
        }

        $results = array();
        foreach (glob($dir . '*.json') as $file) {
            $content = file_get_contents($file);
            $data = json_decode($content, true);
            if (!is_array($data)) {
                continue;
            }

            $data['group_id'] = $data['group_id'] ?? basename($file, '.json');
            $data['batch_date'] = $data['batch_date'] ?? $date;
            $data['domains'] = $this->normalizeDomains($data['domains'] ?? array());
            $data['site_count'] = $this->normalizeCount($data);
            $data['type'] = $this->sanitizeType($data['type'] ?? 'single');
            $data['category'] = $this->sanitizeCategory($data['category'] ?? ($data['mode'] ?? 'other'));
            $data['mode'] = $data['category'];
            $data['redirect_policy'] = $this->sanitizeRedirectPolicy($data['redirect_policy'] ?? 'off');
            $data['image_localization'] = $this->sanitizeToggle($data['image_localization'] ?? 'off');
            $data['image_rename'] = $data['image_localization'] === 'on'
                ? $this->sanitizeToggle($data['image_rename'] ?? 'off')
                : 'off';
            $data['article_image_count'] = $this->sanitizeArticleImageCount($data['article_image_count'] ?? 1);
            $data['article_thumbnail_first'] = $this->sanitizeToggle($data['article_thumbnail_first'] ?? 'off');
            $data['domain_templates'] = $this->sanitizeDomainTemplates($data['domains'], $data['domain_templates'] ?? array());

            $results[] = $data;
        }

        return $results;
    }

    protected function buildFilePath($date, $groupId)
    {
        return $this->basePath . $date . DS . $groupId . '.json';
    }

    protected function normalizeDate($date)
    {
        $date = trim($date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return date('Y-m-d');
        }
        return $date;
    }

    public function normalizeDomains($domains)
    {
        if (is_string($domains)) {
            $domains = preg_split('/\r\n|\r|\n|,/', $domains);
        }

        if (!is_array($domains)) {
            return array();
        }

        $clean = array();
        $seen = array();
        foreach ($domains as $domain) {
            $domain = trim($domain);
            if ($domain !== '') {
                $key = $this->slugifyDomain($domain);
                if ($key !== '' && !isset($seen[$key])) {
                    $clean[] = $domain;
                    $seen[$key] = true;
                }
            }
        }

        return array_values($clean);
    }

    public function findConflictingDomains(array $domains, $currentGroupId = '')
    {
        if (empty($domains)) {
            return array();
        }

        $lookup = array();
        foreach ($domains as $domain) {
            $lookup[$this->slugifyDomain($domain)] = $domain;
        }

        $conflicts = array();
        foreach ($this->listGroups() as $group) {
            $groupId = $group['group_id'] ?? '';
            if ($groupId === $currentGroupId) {
                continue;
            }

            $groupDomains = $group['domains'] ?? array();
            foreach ($groupDomains as $existingDomain) {
                $key = $this->slugifyDomain($existingDomain);
                if (isset($lookup[$key])) {
                    $conflicts[$lookup[$key]] = array(
                        'group_id' => $groupId,
                        'group_name' => $group['group_name'] ?? $groupId
                    );
                }
            }
        }

        return $conflicts;
    }

    public function parseDomainInput($domains)
    {
        if (is_string($domains)) {
            $domains = preg_split('/\r\n|\r|\n|,/', $domains);
        }

        if (!is_array($domains)) {
            return array(
                'domains' => array(),
                'templates' => array()
            );
        }

        $rawDomains = array();
        $templateCandidates = array();

        foreach ($domains as $line) {
            $line = trim((string)$line);
            if ($line === '') {
                continue;
            }

            $domain = $line;
            $template = '';

            $parts = explode('-', $line);
            if (count($parts) > 1) {
                $candidateTemplate = strtolower((string)array_pop($parts));
                $candidateDomain = implode('-', $parts);
                if ($candidateTemplate !== '' && strpos($candidateTemplate, '.') === false && strpos($candidateDomain, '.') !== false) {
                    $domain = $candidateDomain;
                    $template = $candidateTemplate;
                }
            }

            $rawDomains[] = $domain;

            if ($template !== '') {
                $templateCandidates[$this->slugifyDomain($domain)] = array(
                    'domain' => $domain,
                    'template' => $template
                );
            }
        }

        $domainsList = $this->normalizeDomains($rawDomains);

        $templates = array();
        foreach ($domainsList as $domain) {
            $key = $this->slugifyDomain($domain);
            if (isset($templateCandidates[$key])) {
                $templates[$domain] = $templateCandidates[$key]['template'];
            }
        }

        return array(
            'domains' => $domainsList,
            'templates' => $templates
        );
    }

    protected function normalizeCount($data)
    {
        if (isset($data['site_count']) && is_numeric($data['site_count'])) {
            return (int)$data['site_count'];
        }

        $domains = $this->normalizeDomains($data['domains'] ?? array());
        return count($domains);
    }

    protected function slugifyDomain($domain)
    {
        $domain = trim($domain);
        if ($domain === '') {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($domain, 'UTF-8');
        }

        return strtolower($domain);
    }

    protected function sanitizeType($value)
    {
        $value = trim((string)$value);
        if (in_array($value, $this->allowedTypes, true)) {
            return $value;
        }
        return $this->allowedTypes[0];
    }

    protected function sanitizeCategory($value)
    {
        $value = trim((string)$value);
        if (in_array($value, $this->allowedCategories, true)) {
            return $value;
        }
        return 'other';
    }

    protected function sanitizeRedirectPolicy($value)
    {
        $value = trim((string)$value);
        if (in_array($value, $this->allowedRedirectPolicies, true)) {
            return $value;
        }
        return 'off';
    }

    protected function sanitizeToggle($value)
    {
        return trim((string)$value) === 'on' ? 'on' : 'off';
    }

    protected function sanitizeArticleImageCount($value)
    {
        $value = (int)$value;
        if ($value < 1) {
            return 1;
        }
        if ($value > 100) {
            return 100;
        }
        return $value;
    }

    protected function sanitizeDomainTemplates(array $domains, $templates)
    {
        if (!is_array($templates)) {
            return array();
        }

        $sanitized = array();
        foreach ($domains as $domain) {
            $lookupKeys = array($domain, $this->slugifyDomain($domain));
            $templateValue = '';
            foreach ($lookupKeys as $key) {
                if (isset($templates[$key]) && is_string($templates[$key])) {
                    $templateValue = strtolower(trim($templates[$key]));
                    break;
                }
            }

            if ($templateValue !== '' && preg_match('/^[a-z0-9-]+$/', $templateValue)) {
                $sanitized[$domain] = $templateValue;
            }
        }

        return $sanitized;
    }
}
