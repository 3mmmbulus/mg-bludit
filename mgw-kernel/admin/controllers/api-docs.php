<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

$pageLangFile = PATH_LANGUAGES . 'pages/api-docs/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$layout['title'] .= ' - ' . $L->g('api-docs-title');

if (!function_exists('mgwApiDocsReadJsonSafe')) {
    function mgwApiDocsReadJsonSafe($filePath)
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

if (!function_exists('mgwApiDocsSlugify')) {
    function mgwApiDocsSlugify($text)
    {
        $text = strtolower((string)$text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        if ($text === '') {
            $text = 'item-' . substr(md5((string)microtime(true)), 0, 8);
        }
        return $text;
    }
}

if (!function_exists('mgwApiDocsBuildSample')) {
    function mgwApiDocsBuildSample()
    {
        $panelBase = 'https://panel.example.com/api';
        $siteBase = 'https://{site-domain}/api';
        $now = time();

        $categories = array();

        $categories[] = array(
            'id' => 'panel-sites',
            'title' => 'Panel • Site Management',
            'type' => 'panel',
            'summary' => 'Manage tenants and domains for the multi-site cluster from the central control plane.',
            'endpoints' => array(
                array(
                    'id' => 'panel-sites-list',
                    'name' => 'List managed sites',
                    'summary' => 'Returns paginated tenant inventory with status, quota, and domain metadata.',
                    'method' => 'GET',
                    'path' => '/admin/sites',
                    'tags' => array('sites', 'tenants', 'inventory'),
                    'permission' => 'site:list',
                    'scope' => 'panel.sites.read',
                    'auth' => array('required' => true, 'type' => 'token', 'header' => 'X-Admin-Token'),
                    'rateLimit' => array('limit' => 60, 'window' => '1m'),
                    'parameters' => array(
                        'query' => array(
                            array('name' => 'page', 'in' => 'query', 'type' => 'integer', 'required' => false, 'description' => 'Page index starting from 1.', 'example' => 1),
                            array('name' => 'perPage', 'in' => 'query', 'type' => 'integer', 'required' => false, 'description' => 'Page size (max 100).', 'example' => 20),
                            array('name' => 'status', 'in' => 'query', 'type' => 'string', 'required' => false, 'description' => 'Filter by site status (active, paused, error).', 'example' => 'active')
                        ),
                        'headers' => array(
                            array('name' => 'X-Admin-Token', 'in' => 'header', 'type' => 'string', 'required' => true, 'description' => 'Panel-issued admin token.', 'example' => 'adm_demo_token'),
                            array('name' => 'X-Trace-Id', 'in' => 'header', 'type' => 'string', 'required' => false, 'description' => 'Optional request trace identifier.', 'example' => 'trace-1749471200')
                        ),
                        'body' => array()
                    ),
                    'responses' => array(
                        'success' => array(
                            'status' => 200,
                            'body' => array(
                                'code' => 0,
                                'message' => 'OK',
                                'data' => array(
                                    'page' => 1,
                                    'perPage' => 20,
                                    'total' => 128,
                                    'sites' => array(
                                        array('id' => '1dun.net', 'title' => '1dun 主站', 'status' => 'active', 'createdAt' => '2024-04-18T09:21:30Z'),
                                        array('id' => 'www.1dun.net', 'title' => '1dun Portal', 'status' => 'paused', 'createdAt' => '2024-05-01T11:15:02Z')
                                    )
                                )
                            )
                        ),
                        'error' => array(
                            'status' => 401,
                            'body' => array('code' => 40101, 'message' => 'Token expired or invalid', 'traceId' => 'trace-unauthorized')
                        )
                    ),
                    'errorCases' => array(
                        array('title' => 'Missing token header', 'status' => 401, 'description' => 'X-Admin-Token header must be provided for all panel APIs.'),
                        array('title' => 'Insufficient scope', 'status' => 403, 'description' => 'User token lacks panel.sites.read scope.')
                    ),
                    'curl' => <<<'CURL'
curl --request GET \
  --url https://panel.example.com/api/admin/sites?page=1&perPage=20 \
  --header 'Accept: application/json' \
  --header 'X-Admin-Token: {admin_token}' \
  --header 'X-Trace-Id: trace-demo-001'
CURL
                    ,
                    'playground' => array(
                        'method' => 'GET',
                        'base' => 'panel',
                        'path' => '/admin/sites?page=1&perPage=20',
                        'headers' => array('X-Admin-Token' => '{admin_token}', 'Accept' => 'application/json'),
                        'body' => ''
                    ),
                    'base' => $panelBase
                ),
                array(
                    'id' => 'panel-sites-create',
                    'name' => 'Create managed site',
                    'summary' => 'Provision a new tenant site with base domain, theme preset, and quota settings.',
                    'method' => 'POST',
                    'path' => '/admin/sites',
                    'tags' => array('sites', 'provisioning'),
                    'permission' => 'site:create',
                    'scope' => 'panel.sites.write',
                    'auth' => array('required' => true, 'type' => 'token', 'header' => 'X-Admin-Token'),
                    'rateLimit' => array('limit' => 30, 'window' => '10m'),
                    'parameters' => array(
                        'query' => array(),
                        'headers' => array(
                            array('name' => 'X-Admin-Token', 'in' => 'header', 'type' => 'string', 'required' => true, 'description' => 'Panel-issued admin token.', 'example' => 'adm_demo_token')
                        ),
                        'body' => array(
                            array('name' => 'id', 'in' => 'body', 'type' => 'string', 'required' => true, 'description' => 'Site identifier, must be globally unique.', 'example' => 'docs.example.net'),
                            array('name' => 'title', 'in' => 'body', 'type' => 'string', 'required' => true, 'description' => 'Display title of the site.', 'example' => 'Docs Portal'),
                            array('name' => 'plan', 'in' => 'body', 'type' => 'string', 'required' => false, 'description' => 'Service plan code (standard, pro, enterprise).', 'example' => 'pro'),
                            array('name' => 'theme', 'in' => 'body', 'type' => 'string', 'required' => false, 'description' => 'Initial theme slug.', 'example' => 'alternative')
                        )
                    ),
                    'responses' => array(
                        'success' => array(
                            'status' => 201,
                            'body' => array('code' => 0, 'message' => 'Site created', 'data' => array('id' => 'docs.example.net', 'status' => 'initializing'))
                        ),
                        'error' => array(
                            'status' => 422,
                            'body' => array('code' => 42201, 'message' => 'Site id already exists', 'errors' => array('id' => array('The id has already been taken.')))
                        )
                    ),
                    'errorCases' => array(
                        array('title' => 'Duplicate site id', 'status' => 422, 'description' => 'Ensure the tenant id is unique across the cluster.'),
                        array('title' => 'Quota exceeded', 'status' => 429, 'description' => 'Provisioning limit reached for the current billing period.')
                    ),
                    'curl' => <<<'CURL'
curl --request POST \
  --url https://panel.example.com/api/admin/sites \
  --header 'Content-Type: application/json' \
  --header 'X-Admin-Token: {admin_token}' \
  --data '{
    "id": "docs.example.net",
    "title": "Docs Portal",
    "plan": "pro",
    "theme": "alternative"
  }'
CURL
                    ,
                    'playground' => array(
                        'method' => 'POST',
                        'base' => 'panel',
                        'path' => '/admin/sites',
                        'headers' => array('X-Admin-Token' => '{admin_token}', 'Content-Type' => 'application/json'),
                        'body' => json_encode(array('id' => 'docs.example.net', 'title' => 'Docs Portal', 'plan' => 'pro', 'theme' => 'alternative'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                    ),
                    'base' => $panelBase
                )
            )
        );

        $categories[] = array(
            'id' => 'panel-content',
            'title' => 'Panel • Content Publishing',
            'type' => 'panel',
            'summary' => 'Moderate, schedule, and publish content to sites directly from the control panel.',
            'endpoints' => array(
                array(
                    'id' => 'panel-content-publish',
                    'name' => 'Publish article to site',
                    'summary' => 'Publishes or updates a markdown article to the selected site workspace.',
                    'method' => 'POST',
                    'path' => '/admin/sites/{siteId}/content',
                    'tags' => array('content', 'publish'),
                    'permission' => 'content:publish',
                    'scope' => 'panel.content.write',
                    'auth' => array('required' => true, 'type' => 'token', 'header' => 'X-Admin-Token'),
                    'rateLimit' => array('limit' => 120, 'window' => '1m'),
                    'parameters' => array(
                        'query' => array(
                            array('name' => 'preview', 'in' => 'query', 'type' => 'boolean', 'required' => false, 'description' => 'If true, content is stored as draft preview.', 'example' => false)
                        ),
                        'headers' => array(
                            array('name' => 'X-Admin-Token', 'in' => 'header', 'type' => 'string', 'required' => true, 'description' => 'Panel token with content scope.', 'example' => 'adm_demo_token'),
                            array('name' => 'X-Content-Signature', 'in' => 'header', 'type' => 'string', 'required' => false, 'description' => 'Optional HMAC signature for payload integrity.', 'example' => 'sha256=...')
                        ),
                        'body' => array(
                            array('name' => 'title', 'in' => 'body', 'type' => 'string', 'required' => true, 'description' => 'Human friendly title.', 'example' => 'Hello API Docs'),
                            array('name' => 'slug', 'in' => 'body', 'type' => 'string', 'required' => true, 'description' => 'URL slug (auto generated if empty).', 'example' => 'hello-api-docs'),
                            array('name' => 'content', 'in' => 'body', 'type' => 'text', 'required' => true, 'description' => 'Markdown content body.', 'example' => '# Hello'),
                            array('name' => 'status', 'in' => 'body', 'type' => 'string', 'required' => true, 'description' => 'Publish status (published, draft).', 'example' => 'published')
                        )
                    ),
                    'responses' => array(
                        'success' => array(
                            'status' => 200,
                            'body' => array('code' => 0, 'message' => 'Published', 'data' => array('siteId' => 'example.com', 'slug' => 'hello-api-docs', 'status' => 'published'))
                        ),
                        'error' => array(
                            'status' => 409,
                            'body' => array('code' => 40901, 'message' => 'Draft conflict', 'conflictId' => 'draft-92a83e')
                        )
                    ),
                    'errorCases' => array(
                        array('title' => 'Draft conflict', 'status' => 409, 'description' => 'Site has unpublished draft with same slug; use preview=false to overwrite.'),
                        array('title' => 'Missing content scope', 'status' => 403, 'description' => 'Token requires content:publish permission.')
                    ),
                    'curl' => <<<'CURL'
curl --request POST \
  --url https://panel.example.com/api/admin/sites/example.com/content?preview=false \
  --header 'Content-Type: application/json' \
  --header 'X-Admin-Token: {admin_token}' \
  --data '{
    "title": "Hello API Docs",
    "slug": "hello-api-docs",
    "content": "# Hello\nThis is generated from the API.",
    "status": "published"
  }'
CURL
                    ,
                    'playground' => array(
                        'method' => 'POST',
                        'base' => 'panel',
                        'path' => '/admin/sites/example.com/content?preview=false',
                        'headers' => array('X-Admin-Token' => '{admin_token}', 'Content-Type' => 'application/json'),
                        'body' => json_encode(array('title' => 'Hello API Docs', 'slug' => 'hello-api-docs', 'content' => "# Hello\nThis is generated from the API.", 'status' => 'published'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                    ),
                    'base' => $panelBase
                )
            )
        );

        $categories[] = array(
            'id' => 'site-content',
            'title' => 'Site • Content Delivery',
            'type' => 'site',
            'summary' => 'Expose public content and search APIs directly from individual site instances.',
            'endpoints' => array(
                array(
                    'id' => 'site-content-list',
                    'name' => 'List published content',
                    'summary' => 'Returns public article list with pagination for the active site.',
                    'method' => 'GET',
                    'path' => '/content',
                    'tags' => array('site', 'content', 'public'),
                    'permission' => 'public',
                    'scope' => 'site.content.read',
                    'auth' => array('required' => false, 'type' => 'none'),
                    'rateLimit' => array('limit' => 1200, 'window' => '1m'),
                    'parameters' => array(
                        'query' => array(
                            array('name' => 'page', 'in' => 'query', 'type' => 'integer', 'required' => false, 'description' => 'Page index starting from 1.', 'example' => 1),
                            array('name' => 'category', 'in' => 'query', 'type' => 'string', 'required' => false, 'description' => 'Filter by category slug.', 'example' => 'announcements')
                        ),
                        'headers' => array(),
                        'body' => array()
                    ),
                    'responses' => array(
                        'success' => array(
                            'status' => 200,
                            'body' => array('code' => 0, 'data' => array('page' => 1, 'items' => array(array('slug' => 'welcome', 'title' => 'Welcome'), array('slug' => 'api-beta', 'title' => 'API Beta Notes'))))
                        ),
                        'error' => array(
                            'status' => 500,
                            'body' => array('code' => 50001, 'message' => 'Upstream datastore timeout')
                        )
                    ),
                    'errorCases' => array(
                        array('title' => 'Cache warmup', 'status' => 503, 'description' => 'Immediately after deployment API may respond 503 while cache warms.'),
                        array('title' => 'Invalid category', 'status' => 400, 'description' => 'Unknown category slug yields validation error.')
                    ),
                    'curl' => <<<'CURL'
curl --request GET \
  --url https://{site-domain}/api/content?page=1&category=announcements \
  --header 'Accept: application/json'
CURL
                    ,
                    'playground' => array(
                        'method' => 'GET',
                        'base' => 'site',
                        'path' => '/content?page=1',
                        'headers' => array('Accept' => 'application/json'),
                        'body' => ''
                    ),
                    'base' => $siteBase
                ),
                array(
                    'id' => 'site-cache-purge',
                    'name' => 'Purge site cache tag',
                    'summary' => 'Invalidates cache entries by tag for the current site instance.',
                    'method' => 'DELETE',
                    'path' => '/cache/tags/{tag}',
                    'tags' => array('cache', 'invalidate'),
                    'permission' => 'cache:purge',
                    'scope' => 'site.cache.write',
                    'auth' => array('required' => true, 'type' => 'token', 'header' => 'X-Site-Token'),
                    'rateLimit' => array('limit' => 20, 'window' => '1m'),
                    'parameters' => array(
                        'query' => array(
                            array('name' => 'preview', 'in' => 'query', 'type' => 'boolean', 'required' => false, 'description' => 'If true, only preview cache bucket is purged.', 'example' => false)
                        ),
                        'headers' => array(
                            array('name' => 'X-Site-Token', 'in' => 'header', 'type' => 'string', 'required' => true, 'description' => 'Site-issued API token.', 'example' => 'site_demo_token'),
                            array('name' => 'X-Timestamp', 'in' => 'header', 'type' => 'integer', 'required' => true, 'description' => 'Unix timestamp used for signature.', 'example' => $now)
                        ),
                        'body' => array()
                    ),
                    'responses' => array(
                        'success' => array(
                            'status' => 202,
                            'body' => array('code' => 0, 'message' => 'Purge accepted', 'data' => array('tag' => 'homepage', 'status' => 'queued'))
                        ),
                        'error' => array(
                            'status' => 403,
                            'body' => array('code' => 40301, 'message' => 'Signature verification failed')
                        )
                    ),
                    'errorCases' => array(
                        array('title' => 'Signature mismatch', 'status' => 403, 'description' => 'Ensure HMAC signature is calculated with shared secret.'),
                        array('title' => 'Rate limit reached', 'status' => 429, 'description' => 'Reduce purge frequency or batch tags.')
                    ),
                    'curl' => <<<'CURL'
curl --request DELETE \
  --url https://{site-domain}/api/cache/tags/homepage?preview=false \
  --header 'X-Site-Token: {site_token}' \
  --header 'X-Timestamp: {timestamp}' \
  --header 'X-Signature: {hmac_signature}'
CURL
                    ,
                    'playground' => array(
                        'method' => 'DELETE',
                        'base' => 'site',
                        'path' => '/cache/tags/homepage?preview=false',
                        'headers' => array('X-Site-Token' => '{site_token}', 'X-Timestamp' => (string)$now, 'X-Signature' => '{hmac_signature}'),
                        'body' => ''
                    ),
                    'base' => $siteBase
                )
            )
        );

        $categories[] = array(
            'id' => 'panel-monitoring',
            'title' => 'Panel • Monitoring & Logs',
            'type' => 'panel',
            'summary' => 'Query platform telemetry including error logs, spider analytics, and health probes.',
            'endpoints' => array(
                array(
                    'id' => 'panel-spider-metrics',
                    'name' => 'Fetch spider metrics',
                    'summary' => 'Aggregates spider visit metrics across sites for the requested window.',
                    'method' => 'GET',
                    'path' => '/admin/insights/spider',
                    'tags' => array('spider', 'analytics'),
                    'permission' => 'insight:read',
                    'scope' => 'panel.insight.read',
                    'auth' => array('required' => true, 'type' => 'token', 'header' => 'X-Admin-Token'),
                    'rateLimit' => array('limit' => 90, 'window' => '5m'),
                    'parameters' => array(
                        'query' => array(
                            array('name' => 'range', 'in' => 'query', 'type' => 'string', 'required' => false, 'description' => 'Time range (24h, 7d, 30d).', 'example' => '7d'),
                            array('name' => 'siteId', 'in' => 'query', 'type' => 'string', 'required' => false, 'description' => 'Filter to a specific site id.', 'example' => 'example.com')
                        ),
                        'headers' => array(
                            array('name' => 'X-Admin-Token', 'in' => 'header', 'type' => 'string', 'required' => true, 'description' => 'Panel token with insight scope.', 'example' => 'adm_demo_token')
                        ),
                        'body' => array()
                    ),
                    'responses' => array(
                        'success' => array(
                            'status' => 200,
                            'body' => array(
                                'code' => 0,
                                'data' => array(
                                    'range' => '7d',
                                    'totalHits' => 18234,
                                    'uniqueBots' => 32,
                                    'sites' => array(
                                        array('siteId' => 'example.com', 'hits' => 8234, 'uniqueBots' => 18),
                                        array('siteId' => '1dun.net', 'hits' => 10000, 'uniqueBots' => 22)
                                    )
                                )
                            )
                        ),
                        'error' => array(
                            'status' => 503,
                            'body' => array('code' => 50302, 'message' => 'Spider telemetry pipeline unavailable')
                        )
                    ),
                    'errorCases' => array(
                        array('title' => 'Telemetry delay', 'status' => 206, 'description' => 'Partial data returned when pipeline is lagging.'),
                        array('title' => 'Unknown site id', 'status' => 400, 'description' => 'siteId must match registered tenant id.')
                    ),
                    'curl' => <<<'CURL'
curl --request GET \
  --url https://panel.example.com/api/admin/insights/spider?range=7d&siteId=example.com \
  --header 'Accept: application/json' \
  --header 'X-Admin-Token: {admin_token}'
CURL
                    ,
                    'playground' => array(
                        'method' => 'GET',
                        'base' => 'panel',
                        'path' => '/admin/insights/spider?range=7d',
                        'headers' => array('X-Admin-Token' => '{admin_token}', 'Accept' => 'application/json'),
                        'body' => ''
                    ),
                    'base' => $panelBase
                )
            )
        );

        $meta = array(
            'version' => '2025.11',
            'lastUpdated' => $now - 3600,
            'baseUrls' => array(
                'panel' => $panelBase,
                'site' => $siteBase
            ),
            'auth' => array(
                'panelHeader' => 'X-Admin-Token',
                'siteHeader' => 'X-Site-Token',
                'signatureHeader' => 'X-Signature',
                'timestampHeader' => 'X-Timestamp',
                'nonceHeader' => 'X-Nonce',
                'expiresIn' => '2h'
            ),
            'scopes' => array('site:list', 'content:publish', 'insight:read', 'cache:purge')
        );

        $playground = array(
            'token' => 'adm_demo_token',
            'siteToken' => 'site_demo_token',
            'headers' => array('Accept' => 'application/json', 'Content-Type' => 'application/json')
        );

        $examples = array('sites', 'content publish', 'spider metrics', 'cache purge');

        return array(
            'meta' => $meta,
            'categories' => $categories,
            'playground' => $playground,
            'examples' => $examples
        );
    }
}

$sampleData = mgwApiDocsBuildSample();

$apiDocsDir = PATH_ROOT . 'mgw-config' . DS . 'api-docs' . DS;
$metaData = mgwApiDocsReadJsonSafe($apiDocsDir . 'meta.php');
if (empty($metaData)) {
    $metaData = mgwApiDocsReadJsonSafe($apiDocsDir . 'meta.json');
}
$catalogData = mgwApiDocsReadJsonSafe($apiDocsDir . 'catalog.php');
if (empty($catalogData)) {
    $catalogData = mgwApiDocsReadJsonSafe($apiDocsDir . 'catalog.json');
}
$playgroundData = mgwApiDocsReadJsonSafe($apiDocsDir . 'playground.php');
if (empty($playgroundData)) {
    $playgroundData = mgwApiDocsReadJsonSafe($apiDocsDir . 'playground.json');
}

if (!empty($metaData) && is_array($metaData)) {
    $sampleData['meta'] = array_replace_recursive($sampleData['meta'], $metaData);
}

if (!empty($catalogData) && is_array($catalogData)) {
    if (isset($catalogData['categories']) && is_array($catalogData['categories'])) {
        $sampleData['categories'] = $catalogData['categories'];
    } else {
        $sampleData['categories'] = $catalogData;
    }
}

if (!empty($playgroundData) && is_array($playgroundData)) {
    $sampleData['playground'] = array_replace_recursive($sampleData['playground'], $playgroundData);
}

$rawCategories = isset($sampleData['categories']) && is_array($sampleData['categories']) ? $sampleData['categories'] : array();
$rawMeta = isset($sampleData['meta']) && is_array($sampleData['meta']) ? $sampleData['meta'] : array();
$rawPlayground = isset($sampleData['playground']) && is_array($sampleData['playground']) ? $sampleData['playground'] : array();
$searchExamples = isset($sampleData['examples']) && is_array($sampleData['examples']) ? $sampleData['examples'] : array();

$categoriesNormalized = array();
$endpointsFlat = array();
$totalEndpoints = 0;
$panelCount = 0;
$siteCount = 0;
$securedCount = 0;

foreach ($rawCategories as $category) {
    if (!is_array($category)) {
        continue;
    }
    $categoryId = isset($category['id']) ? (string)$category['id'] : '';
    $categoryTitle = isset($category['title']) ? (string)$category['title'] : '';
    if ($categoryId === '') {
        $categoryId = mgwApiDocsSlugify($categoryTitle);
    }
    if ($categoryTitle === '') {
        $categoryTitle = ucfirst(str_replace('-', ' ', $categoryId));
    }
    $categoryType = isset($category['type']) && in_array($category['type'], array('panel', 'site'), true) ? $category['type'] : 'panel';
    $categorySummary = isset($category['summary']) ? (string)$category['summary'] : '';
    $categoryEndpoints = isset($category['endpoints']) && is_array($category['endpoints']) ? $category['endpoints'] : array();

    $normalizedEndpoints = array();

    foreach ($categoryEndpoints as $endpoint) {
        if (!is_array($endpoint)) {
            continue;
        }
        $endpointId = isset($endpoint['id']) ? (string)$endpoint['id'] : '';
        if ($endpointId === '') {
            $endpointId = $categoryId . '-' . substr(md5(json_encode($endpoint)), 0, 8);
        }
        $endpointName = isset($endpoint['name']) ? (string)$endpoint['name'] : $endpointId;
        $endpointSummary = isset($endpoint['summary']) ? (string)$endpoint['summary'] : '';
        $endpointMethod = strtoupper(isset($endpoint['method']) ? (string)$endpoint['method'] : 'GET');
        $endpointPath = isset($endpoint['path']) ? (string)$endpoint['path'] : '/';
        $endpointType = isset($endpoint['type']) && in_array($endpoint['type'], array('panel', 'site'), true) ? $endpoint['type'] : $categoryType;
        $endpointPermission = isset($endpoint['permission']) ? (string)$endpoint['permission'] : '';
        $endpointScope = isset($endpoint['scope']) ? (string)$endpoint['scope'] : '';
        $endpointTags = isset($endpoint['tags']) && is_array($endpoint['tags']) ? array_values(array_filter(array_map('strval', $endpoint['tags']))) : array();
        $endpointAuth = isset($endpoint['auth']) && is_array($endpoint['auth']) ? $endpoint['auth'] : array();
        $endpointSecured = isset($endpointAuth['required']) ? (bool)$endpointAuth['required'] : ($endpointType === 'panel');
        $endpointRate = isset($endpoint['rateLimit']) && is_array($endpoint['rateLimit']) ? $endpoint['rateLimit'] : array();
        $endpointParameters = isset($endpoint['parameters']) && is_array($endpoint['parameters']) ? $endpoint['parameters'] : array();
        $endpointResponses = isset($endpoint['responses']) && is_array($endpoint['responses']) ? $endpoint['responses'] : array();
        $endpointErrorCases = isset($endpoint['errorCases']) && is_array($endpoint['errorCases']) ? $endpoint['errorCases'] : array();
        $endpointCurl = isset($endpoint['curl']) ? (string)$endpoint['curl'] : '';
        $endpointPlayground = isset($endpoint['playground']) && is_array($endpoint['playground']) ? $endpoint['playground'] : array();
        $endpointBase = isset($endpoint['base']) ? (string)$endpoint['base'] : (isset($rawMeta['baseUrls'][$endpointType]) ? (string)$rawMeta['baseUrls'][$endpointType] : '');

        $paramQuery = isset($endpointParameters['query']) && is_array($endpointParameters['query']) ? $endpointParameters['query'] : array();
        $paramHeaders = isset($endpointParameters['headers']) && is_array($endpointParameters['headers']) ? $endpointParameters['headers'] : array();
        $paramBody = isset($endpointParameters['body']) && is_array($endpointParameters['body']) ? $endpointParameters['body'] : array();

        $successResponse = isset($endpointResponses['success']) && is_array($endpointResponses['success']) ? $endpointResponses['success'] : array();
        $errorResponse = isset($endpointResponses['error']) && is_array($endpointResponses['error']) ? $endpointResponses['error'] : array();

        $searchTextParts = array(
            strtolower($endpointName),
            strtolower($endpointSummary),
            strtolower($endpointMethod),
            strtolower($endpointPath),
            implode(' ', array_map('strtolower', $endpointTags)),
            strtolower($endpointPermission),
            strtolower($endpointScope)
        );
        $searchText = trim(implode(' ', array_filter($searchTextParts)));

        $endpointNormalized = array(
            'id' => $endpointId,
            'name' => $endpointName,
            'summary' => $endpointSummary,
            'method' => $endpointMethod,
            'path' => $endpointPath,
            'type' => $endpointType,
            'permission' => $endpointPermission,
            'scope' => $endpointScope,
            'tags' => $endpointTags,
            'auth' => $endpointAuth,
            'secured' => $endpointSecured,
            'rateLimit' => $endpointRate,
            'parameters' => array(
                'query' => $paramQuery,
                'headers' => $paramHeaders,
                'body' => $paramBody
            ),
            'responses' => array(
                'success' => $successResponse,
                'error' => $errorResponse
            ),
            'errorCases' => $endpointErrorCases,
            'curl' => $endpointCurl,
            'playground' => $endpointPlayground,
            'base' => $endpointBase,
            'categoryId' => $categoryId,
            'categoryTitle' => $categoryTitle,
            'searchText' => $searchText
        );

        $normalizedEndpoints[] = $endpointNormalized;
        $endpointsFlat[] = $endpointNormalized;
        $totalEndpoints++;
        if ($endpointType === 'panel') {
            $panelCount++;
        } else {
            $siteCount++;
        }
        if ($endpointSecured) {
            $securedCount++;
        }
    }

    $categoriesNormalized[] = array(
        'id' => $categoryId,
        'title' => $categoryTitle,
        'type' => $categoryType,
        'summary' => $categorySummary,
        'endpoints' => $normalizedEndpoints
    );
}

$apiDocMeta = $rawMeta;
$apiDocCategories = $categoriesNormalized;
$apiDocPlayground = $rawPlayground;
$apiDocSearchExamples = $searchExamples;
$apiDocEndpointsFlat = $endpointsFlat;
$apiDocStats = array(
    'total' => $totalEndpoints,
    'panel' => $panelCount,
    'site' => $siteCount,
    'secured' => $securedCount
);

extract(array(
    'apiDocMeta' => $apiDocMeta,
    'apiDocCategories' => $apiDocCategories,
    'apiDocPlayground' => $apiDocPlayground,
    'apiDocSearchExamples' => $apiDocSearchExamples,
    'apiDocEndpointsFlat' => $apiDocEndpointsFlat,
    'apiDocStats' => $apiDocStats
), EXTR_OVERWRITE);
