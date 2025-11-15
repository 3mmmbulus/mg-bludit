<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$apiDocMeta = isset($apiDocMeta) && is_array($apiDocMeta) ? $apiDocMeta : array();
$apiDocCategories = isset($apiDocCategories) && is_array($apiDocCategories) ? $apiDocCategories : array();
$apiDocPlayground = isset($apiDocPlayground) && is_array($apiDocPlayground) ? $apiDocPlayground : array();
$apiDocSearchExamples = isset($apiDocSearchExamples) && is_array($apiDocSearchExamples) ? $apiDocSearchExamples : array();
$apiDocEndpointsFlat = isset($apiDocEndpointsFlat) && is_array($apiDocEndpointsFlat) ? $apiDocEndpointsFlat : array();
$apiDocStats = isset($apiDocStats) && is_array($apiDocStats) ? $apiDocStats : array('total' => 0, 'panel' => 0, 'site' => 0, 'secured' => 0);

if (!function_exists('apiDocsMethodBadgeClass')) {
    function apiDocsMethodBadgeClass($method)
    {
        $method = strtoupper((string)$method);
        switch ($method) {
            case 'GET':
                return 'bg-success-subtle text-success';
            case 'POST':
                return 'bg-primary-subtle text-primary';
            case 'PUT':
            case 'PATCH':
                return 'bg-warning-subtle text-warning';
            case 'DELETE':
                return 'bg-danger-subtle text-danger';
            default:
                return 'bg-secondary-subtle text-secondary';
        }
    }
}

if (!function_exists('apiDocsFormatDateTime')) {
    function apiDocsFormatDateTime($timestamp)
    {
        if (!is_numeric($timestamp)) {
            return '--';
        }
        $timestamp = (int)$timestamp;
        if ($timestamp <= 0) {
            return '--';
        }
        return date('Y-m-d H:i:s', $timestamp);
    }
}

if (!function_exists('apiDocsJsonEncode')) {
    function apiDocsJsonEncode($payload)
    {
        if ($payload === null || $payload === '') {
            return '';
        }
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
?>

<div class="api-docs-page container-fluid px-0">
    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1 d-flex align-items-center gap-2">
                <span class="bi bi-journal-code"></span>
                <span><?php echo $L->g('api-docs-title'); ?></span>
            </h2>
            <p class="text-muted small mb-0"><?php echo $L->g('api-docs-subtitle'); ?></p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="api-docs-stat border rounded-3 h-100 p-3">
                <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-stats-total'); ?></div>
                <div class="fs-3 fw-semibold mt-2"><?php echo number_format(isset($apiDocStats['total']) ? $apiDocStats['total'] : 0); ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="api-docs-stat border rounded-3 h-100 p-3">
                <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-stats-panel'); ?></div>
                <div class="fs-3 fw-semibold mt-2 text-primary"><?php echo number_format(isset($apiDocStats['panel']) ? $apiDocStats['panel'] : 0); ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="api-docs-stat border rounded-3 h-100 p-3">
                <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-stats-site'); ?></div>
                <div class="fs-3 fw-semibold mt-2 text-success"><?php echo number_format(isset($apiDocStats['site']) ? $apiDocStats['site'] : 0); ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="api-docs-stat border rounded-3 h-100 p-3">
                <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-stats-secured'); ?></div>
                <div class="fs-3 fw-semibold mt-2 text-danger"><?php echo number_format(isset($apiDocStats['secured']) ? $apiDocStats['secured'] : 0); ?></div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-lg-6">
                    <div class="position-relative">
                        <span class="bi bi-search position-absolute top-50 start-0 translate-middle-y ps-3 text-muted"></span>
                        <input id="apiDocsSearchInput" type="search" class="form-control ps-5" placeholder="<?php echo Sanitize::html($L->g('api-docs-search-placeholder')); ?>">
                    </div>
                </div>
                <div class="col-lg-3 d-flex gap-2">
                    <button id="apiDocsSearchClear" type="button" class="btn btn-outline-secondary w-100">
                        <span class="bi bi-x-circle"></span>
                        <span class="ms-1"><?php echo $L->g('api-docs-search-clear'); ?></span>
                    </button>
                </div>
                <div class="col-lg-3 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-primary flex-grow-1 active" data-api-filter="all"><?php echo $L->g('api-docs-filter-all'); ?></button>
                    <button type="button" class="btn btn-outline-primary flex-grow-1" data-api-filter="panel"><?php echo $L->g('api-docs-filter-panel'); ?></button>
                    <button type="button" class="btn btn-outline-primary flex-grow-1" data-api-filter="site"><?php echo $L->g('api-docs-filter-site'); ?></button>
                </div>
            </div>
            <?php if (!empty($apiDocSearchExamples)): ?>
                <div class="text-muted small mt-3">
                    <span class="fw-semibold text-uppercase"><?php echo $L->g('api-docs-search-examples'); ?>:</span>
                    <?php echo Sanitize::html(implode(' · ', $apiDocSearchExamples)); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-meta-version'); ?></div>
                    <div class="fw-semibold mt-2"><?php echo Sanitize::html(isset($apiDocMeta['version']) ? $apiDocMeta['version'] : '--'); ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-meta-updated'); ?></div>
                    <div class="fw-semibold mt-2"><?php echo apiDocsFormatDateTime(isset($apiDocMeta['lastUpdated']) ? $apiDocMeta['lastUpdated'] : null); ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-meta-token-expire'); ?></div>
                    <div class="fw-semibold mt-2"><?php echo Sanitize::html(isset($apiDocMeta['auth']['expiresIn']) ? $apiDocMeta['auth']['expiresIn'] : '--'); ?></div>
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-meta-panel-base'); ?></div>
                    <div class="fw-semibold text-primary mt-1 text-break"><?php echo Sanitize::html(isset($apiDocMeta['baseUrls']['panel']) ? $apiDocMeta['baseUrls']['panel'] : '--'); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-meta-site-base'); ?></div>
                    <div class="fw-semibold text-success mt-1 text-break"><?php echo Sanitize::html(isset($apiDocMeta['baseUrls']['site']) ? $apiDocMeta['baseUrls']['site'] : '--'); ?></div>
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-meta-auth'); ?></div>
                    <div class="small text-muted mt-1">
                        <div><?php echo $L->g('api-docs-category-type-panel'); ?>: <span class="fw-semibold text-primary"><?php echo Sanitize::html(isset($apiDocMeta['auth']['panelHeader']) ? $apiDocMeta['auth']['panelHeader'] : 'X-Admin-Token'); ?></span></div>
                        <div><?php echo $L->g('api-docs-category-type-site'); ?>: <span class="fw-semibold text-success"><?php echo Sanitize::html(isset($apiDocMeta['auth']['siteHeader']) ? $apiDocMeta['auth']['siteHeader'] : 'X-Site-Token'); ?></span></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-meta-signature'); ?></div>
                    <div class="small text-muted mt-1">
                        <div><?php echo $L->g('api-docs-auth-header'); ?>: <span class="fw-semibold"><?php echo Sanitize::html(isset($apiDocMeta['auth']['signatureHeader']) ? $apiDocMeta['auth']['signatureHeader'] : 'X-Signature'); ?></span></div>
                        <div><?php echo $L->g('api-docs-auth-signature'); ?>: <span class="fw-semibold"><?php echo Sanitize::html(isset($apiDocMeta['auth']['timestampHeader']) ? $apiDocMeta['auth']['timestampHeader'] : 'X-Timestamp'); ?></span> · <span class="fw-semibold"><?php echo Sanitize::html(isset($apiDocMeta['auth']['nonceHeader']) ? $apiDocMeta['auth']['nonceHeader'] : 'X-Nonce'); ?></span></div>
                    </div>
                </div>
            </div>
            <?php if (!empty($apiDocMeta['scopes']) && is_array($apiDocMeta['scopes'])): ?>
                <div class="mt-3">
                    <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-meta-scope'); ?></div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <?php foreach ($apiDocMeta['scopes'] as $scope): ?>
                            <span class="badge bg-light text-dark border"><?php echo Sanitize::html($scope); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xxl-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><?php echo $L->g('api-docs-categories-title'); ?></h5>
                    <span class="text-muted small"><?php echo $L->g('api-docs-categories-desc'); ?></span>
                </div>
                <div class="card-body p-0">
                    <nav class="list-group list-group-flush api-docs-category-list">
                        <?php foreach ($apiDocCategories as $category): ?>
                            <a class="list-group-item list-group-item-action" href="#api-category-<?php echo Sanitize::html($category['id']); ?>">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div>
                                        <div class="fw-semibold text-truncate"><?php echo Sanitize::html($category['title']); ?></div>
                                        <div class="text-muted small text-truncate"><?php echo Sanitize::html($category['summary']); ?></div>
                                    </div>
                                    <span class="badge bg-light text-dark border"><?php echo count(isset($category['endpoints']) ? $category['endpoints'] : array()); ?></span>
                                </div>
                                <span class="badge rounded-pill mt-2 <?php echo $category['type'] === 'panel' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'; ?>">
                                    <?php echo $category['type'] === 'panel' ? $L->g('api-docs-category-type-panel') : $L->g('api-docs-category-type-site'); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><?php echo $L->g('api-docs-token-title'); ?></h5>
                    <span class="text-muted small"><?php echo $L->g('api-docs-token-desc'); ?></span>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2"><?php echo $L->g('api-docs-token-helper'); ?></p>
                    <div class="mb-3">
                        <input id="apiDocsTokenInput" type="text" class="form-control" placeholder="<?php echo Sanitize::html($L->g('api-docs-token-input-placeholder')); ?>" value="<?php echo Sanitize::html(isset($apiDocPlayground['token']) ? $apiDocPlayground['token'] : ''); ?>">
                    </div>
                    <div class="d-flex gap-2 mb-3">
                        <button id="apiDocsTokenApply" type="button" class="btn btn-primary flex-grow-1"><?php echo $L->g('api-docs-token-apply'); ?></button>
                        <button id="apiDocsTokenReset" type="button" class="btn btn-outline-secondary flex-grow-1"><?php echo $L->g('api-docs-token-reset'); ?></button>
                    </div>
                    <div class="alert alert-light border small mb-0" id="apiDocsTokenActive">
                        <span class="bi bi-key me-2"></span><?php echo $L->g('api-docs-token-active'); ?> <span class="fw-semibold">{admin_token}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-9">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0"><?php echo $L->g('api-docs-test-title'); ?></h5>
                            <span class="text-muted small"><?php echo $L->g('api-docs-test-desc'); ?></span>
                        </div>
                        <div class="text-muted small" id="apiDocsPlaygroundStatus"></div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="apiDocsPlaygroundForm" class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label small text-uppercase text-muted"><?php echo $L->g('api-docs-test-endpoint'); ?></label>
                            <select id="apiDocsPlaygroundEndpoint" class="form-select">
                                <option value="">--</option>
                                <?php foreach ($apiDocEndpointsFlat as $endpoint): ?>
                                    <option value="<?php echo Sanitize::html($endpoint['id']); ?>" data-method="<?php echo Sanitize::html($endpoint['method']); ?>" data-path="<?php echo Sanitize::html($endpoint['path']); ?>" data-base="<?php echo Sanitize::html($endpoint['type']); ?>" data-playground="<?php echo Sanitize::html(json_encode(isset($endpoint['playground']) ? $endpoint['playground'] : array())); ?>" data-success="<?php echo Sanitize::html(json_encode(isset($endpoint['responses']['success']) ? $endpoint['responses']['success'] : array())); ?>"><?php echo Sanitize::html($endpoint['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label small text-uppercase text-muted"><?php echo $L->g('api-docs-test-base'); ?></label>
                            <select id="apiDocsPlaygroundBase" class="form-select">
                                <option value="panel" selected><?php echo $L->g('api-docs-category-type-panel'); ?></option>
                                <option value="site"><?php echo $L->g('api-docs-category-type-site'); ?></option>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label small text-uppercase text-muted"><?php echo $L->g('api-docs-test-method'); ?></label>
                            <select id="apiDocsPlaygroundMethod" class="form-select">
                                <option>GET</option>
                                <option>POST</option>
                                <option>PUT</option>
                                <option>PATCH</option>
                                <option>DELETE</option>
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label small text-uppercase text-muted"><?php echo $L->g('api-docs-test-path'); ?></label>
                            <input id="apiDocsPlaygroundPath" type="text" class="form-control" placeholder="/admin/sites">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-uppercase text-muted"><?php echo $L->g('api-docs-test-headers'); ?></label>
                            <textarea id="apiDocsPlaygroundHeaders" class="form-control" rows="2" placeholder="<?php echo Sanitize::html($L->g('api-docs-test-token-placeholder')); ?>"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-uppercase text-muted"><?php echo $L->g('api-docs-test-body'); ?></label>
                            <textarea id="apiDocsPlaygroundBody" class="form-control" rows="4" placeholder="{}"></textarea>
                        </div>
                        <div class="col-12 d-flex align-items-center justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <span class="bi bi-send"></span>
                                <span class="ms-1"><?php echo $L->g('api-docs-test-send'); ?></span>
                            </button>
                            <div class="text-muted small" id="apiDocsPlaygroundInfo"></div>
                        </div>
                    </form>
                    <div class="mt-3">
                        <label class="form-label small text-uppercase text-muted"><?php echo $L->g('api-docs-test-response'); ?></label>
                        <pre id="apiDocsPlaygroundResponse" class="api-docs-code api-docs-code-dark mb-0"><?php echo $L->g('api-docs-test-empty'); ?></pre>
                    </div>
                </div>
            </div>

            <div id="apiDocsEmptyState" class="alert alert-info d-none">
                <span class="bi bi-search me-2"></span><?php echo $L->g('api-docs-search-empty'); ?>
            </div>

            <?php foreach ($apiDocCategories as $category): ?>
                <section id="api-category-<?php echo Sanitize::html($category['id']); ?>" class="mb-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                        <div>
                            <h4 class="mb-1 d-flex align-items-center gap-2">
                                <span class="badge <?php echo $category['type'] === 'panel' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'; ?>"><?php echo $category['type'] === 'panel' ? $L->g('api-docs-category-type-panel') : $L->g('api-docs-category-type-site'); ?></span>
                                <span><?php echo Sanitize::html($category['title']); ?></span>
                            </h4>
                            <p class="text-muted small mb-0"><?php echo Sanitize::html($category['summary']); ?></p>
                        </div>
                        <span class="badge bg-light text-dark border align-self-start"><?php echo count(isset($category['endpoints']) ? $category['endpoints'] : array()); ?> <?php echo $L->g('api-docs-stats-total'); ?></span>
                    </div>

                    <?php foreach ($category['endpoints'] as $endpoint): ?>
                        <details class="api-docs-endpoint" data-type="<?php echo Sanitize::html($endpoint['type']); ?>" data-search="<?php echo Sanitize::html($endpoint['searchText']); ?>" data-endpoint-id="<?php echo Sanitize::html($endpoint['id']); ?>">
                            <summary class="api-docs-endpoint-summary">
                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge <?php echo apiDocsMethodBadgeClass($endpoint['method']); ?> text-uppercase"><?php echo Sanitize::html($endpoint['method']); ?></span>
                                        <span class="api-docs-endpoint-path text-monospace"><?php echo Sanitize::html($endpoint['path']); ?></span>
                                        <span class="badge <?php echo $endpoint['type'] === 'panel' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'; ?>">
                                            <?php echo $endpoint['type'] === 'panel' ? $L->g('api-docs-category-type-panel') : $L->g('api-docs-category-type-site'); ?>
                                        </span>
                                    </div>
                                    <div class="text-muted small text-uppercase">
                                        <?php echo $L->g('api-docs-endpoint-base'); ?>
                                        <span class="text-primary ms-1"><?php echo Sanitize::html($endpoint['base']); ?></span>
                                    </div>
                                </div>
                                <div class="fw-semibold mt-2"><?php echo Sanitize::html($endpoint['name']); ?></div>
                                <p class="text-muted small mb-0"><?php echo Sanitize::html($endpoint['summary']); ?></p>
                            </summary>
                            <div class="api-docs-endpoint-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-endpoint-permission'); ?></div>
                                        <div class="fw-semibold mt-1"><?php echo Sanitize::html($endpoint['permission'] !== '' ? $endpoint['permission'] : '--'); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-endpoint-scope'); ?></div>
                                        <div class="fw-semibold mt-1"><?php echo Sanitize::html($endpoint['scope'] !== '' ? $endpoint['scope'] : '--'); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small text-uppercase fw-semibold"><?php echo $L->g('api-docs-endpoint-rate-limit'); ?></div>
                                        <div class="fw-semibold mt-1">
                                            <?php
                                            $limit = isset($endpoint['rateLimit']['limit']) ? (int)$endpoint['rateLimit']['limit'] : 0;
                                            $window = isset($endpoint['rateLimit']['window']) ? (string)$endpoint['rateLimit']['window'] : '';
                                            if ($limit > 0 && $window !== '') {
                                                echo number_format($limit) . ' / ' . Sanitize::html($window);
                                            } else {
                                                echo '--';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small text-uppercase fw-semibold"><?php echo $endpoint['secured'] ? $L->g('api-docs-endpoint-auth-required') : $L->g('api-docs-endpoint-auth-optional'); ?></div>
                                        <div class="fw-semibold mt-1">
                                            <?php
                                            if (!empty($endpoint['auth']['header'])) {
                                                echo Sanitize::html($endpoint['auth']['header']);
                                            } elseif (!empty($endpoint['auth']['type']) && $endpoint['auth']['type'] !== 'none') {
                                                echo Sanitize::html(strtoupper($endpoint['auth']['type']));
                                            } else {
                                                echo '--';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($endpoint['tags'])): ?>
                                    <div class="mb-3">
                                        <div class="text-muted small text-uppercase fw-semibold mb-2"><?php echo $L->g('api-docs-endpoint-tags'); ?></div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($endpoint['tags'] as $tag): ?>
                                                <span class="badge bg-light text-dark border"><?php echo Sanitize::html($tag); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $paramSections = array(
                                    'query' => $L->g('api-docs-params-query'),
                                    'headers' => $L->g('api-docs-params-headers'),
                                    'body' => $L->g('api-docs-params-body')
                                );
                                ?>
                                <?php foreach ($paramSections as $paramKey => $paramLabel): ?>
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="text-uppercase text-muted small mb-2"><?php echo $paramLabel; ?></h6>
                                        </div>
                                        <?php if (!empty($endpoint['parameters'][$paramKey])): ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th><?php echo $L->g('api-docs-params-name'); ?></th>
                                                            <th><?php echo $L->g('api-docs-params-in'); ?></th>
                                                            <th><?php echo $L->g('api-docs-params-type'); ?></th>
                                                            <th><?php echo $L->g('api-docs-params-required'); ?></th>
                                                            <th><?php echo $L->g('api-docs-params-desc'); ?></th>
                                                            <th><?php echo $L->g('api-docs-params-example'); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($endpoint['parameters'][$paramKey] as $param): ?>
                                                            <tr>
                                                                <td class="text-monospace small">&ZeroWidthSpace;<?php echo Sanitize::html(isset($param['name']) ? $param['name'] : '--'); ?></td>
                                                                <td class="small text-uppercase text-muted"><?php echo Sanitize::html(isset($param['in']) ? $param['in'] : $paramKey); ?></td>
                                                                <td class="small"><?php echo Sanitize::html(isset($param['type']) ? $param['type'] : '--'); ?></td>
                                                                <td class="small fw-semibold"><?php echo isset($param['required']) && $param['required'] ? $L->g('api-docs-required-yes') : $L->g('api-docs-required-no'); ?></td>
                                                                <td class="small text-muted"><?php echo Sanitize::html(isset($param['description']) ? $param['description'] : '--'); ?></td>
                                                                <td class="small text-muted text-break"><?php echo isset($param['example']) ? Sanitize::html(is_scalar($param['example']) ? (string)$param['example'] : json_encode($param['example'])) : '--'; ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-light mb-0"><?php echo $L->g('api-docs-params-none'); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>

                                <div class="row g-3 mb-3">
                                    <div class="col-lg-6">
                                        <h6 class="text-uppercase text-muted small mb-2"><?php echo $L->g('api-docs-response-success'); ?></h6>
                                        <div class="small text-muted mb-2"><?php echo $L->g('api-docs-response-status'); ?>: <span class="fw-semibold"><?php echo isset($endpoint['responses']['success']['status']) ? (int)$endpoint['responses']['success']['status'] : 200; ?></span></div>
                                        <pre class="api-docs-code"><?php echo Sanitize::html(apiDocsJsonEncode(isset($endpoint['responses']['success']['body']) ? $endpoint['responses']['success']['body'] : array())); ?></pre>
                                    </div>
                                    <div class="col-lg-6">
                                        <h6 class="text-uppercase text-muted small mb-2"><?php echo $L->g('api-docs-response-error'); ?></h6>
                                        <div class="small text-muted mb-2"><?php echo $L->g('api-docs-response-status'); ?>: <span class="fw-semibold"><?php echo isset($endpoint['responses']['error']['status']) ? (int)$endpoint['responses']['error']['status'] : 400; ?></span></div>
                                        <pre class="api-docs-code"><?php echo Sanitize::html(apiDocsJsonEncode(isset($endpoint['responses']['error']['body']) ? $endpoint['responses']['error']['body'] : array())); ?></pre>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="text-uppercase text-muted small mb-0"><?php echo $L->g('api-docs-curl-example'); ?></h6>
                                        <button type="button" class="btn btn-outline-secondary btn-sm api-docs-copy" data-copy-text="<?php echo Sanitize::html($endpoint['curl']); ?>" data-copy-label="<?php echo Sanitize::html($L->g('api-docs-copy')); ?>" data-copy-done="<?php echo Sanitize::html($L->g('api-docs-copied')); ?>">
                                            <span class="bi bi-clipboard"></span>
                                            <span class="ms-1"><?php echo $L->g('api-docs-copy'); ?></span>
                                        </button>
                                    </div>
                                    <pre class="api-docs-code api-docs-code-dark"><?php echo Sanitize::html($endpoint['curl']); ?></pre>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h6 class="text-uppercase text-muted small mb-2"><?php echo $L->g('api-docs-error-cases-title'); ?></h6>
                                        <button type="button" class="btn btn-outline-primary btn-sm api-docs-load-playground" data-method="<?php echo Sanitize::html($endpoint['method']); ?>" data-base="<?php echo Sanitize::html($endpoint['type']); ?>" data-path="<?php echo Sanitize::html(isset($endpoint['playground']['path']) ? $endpoint['playground']['path'] : $endpoint['path']); ?>" data-playground="<?php echo Sanitize::html(json_encode(isset($endpoint['playground']) ? $endpoint['playground'] : array())); ?>" data-success="<?php echo Sanitize::html(json_encode(isset($endpoint['responses']['success']) ? $endpoint['responses']['success'] : array())); ?>">
                                            <span class="bi bi-lightning"></span>
                                            <span class="ms-1"><?php echo $L->g('api-docs-endpoint-load-playground'); ?></span>
                                        </button>
                                    </div>
                                    <?php if (!empty($endpoint['errorCases'])): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($endpoint['errorCases'] as $case): ?>
                                                <div class="list-group-item px-0">
                                                    <div class="d-flex align-items-start gap-3">
                                                        <span class="badge bg-warning-subtle text-warning"><?php echo $L->g('api-docs-error-cases-status'); ?> <?php echo isset($case['status']) ? (int)$case['status'] : 0; ?></span>
                                                        <div>
                                                            <div class="fw-semibold"><?php echo Sanitize::html(isset($case['title']) ? $case['title'] : '--'); ?></div>
                                                            <div class="small text-muted mt-1"><?php echo Sanitize::html(isset($case['description']) ? $case['description'] : '--'); ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-light mb-0"><?php echo $L->g('api-docs-error-cases-empty'); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
(function () {
    const searchInput = document.getElementById('apiDocsSearchInput');
    const clearButton = document.getElementById('apiDocsSearchClear');
    const filterButtons = document.querySelectorAll('[data-api-filter]');
    const endpoints = document.querySelectorAll('.api-docs-endpoint');
    const emptyState = document.getElementById('apiDocsEmptyState');
    let activeFilter = 'all';

    function applyFilters() {
        const term = (searchInput.value || '').trim().toLowerCase();
        let visibleCount = 0;
        endpoints.forEach(function (endpoint) {
            const matchesFilter = activeFilter === 'all' || endpoint.dataset.type === activeFilter;
            const matchesSearch = term === '' || (endpoint.dataset.search || '').indexOf(term) !== -1;
            const shouldShow = matchesFilter && matchesSearch;
            endpoint.style.display = shouldShow ? '' : 'none';
            if (shouldShow) {
                visibleCount++;
            }
        });
        if (emptyState) {
            emptyState.classList.toggle('d-none', visibleCount > 0);
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    if (clearButton) {
        clearButton.addEventListener('click', function () {
            searchInput.value = '';
            searchInput.focus();
            applyFilters();
        });
    }

    filterButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            filterButtons.forEach(function (btn) { btn.classList.remove('active'); });
            button.classList.add('active');
            activeFilter = button.getAttribute('data-api-filter') || 'all';
            applyFilters();
        });
    });

    applyFilters();

    const copyButtons = document.querySelectorAll('.api-docs-copy');
    copyButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const text = button.getAttribute('data-copy-text') || '';
            const original = button.innerHTML;
            const copiedLabel = button.getAttribute('data-copy-done') || 'Copied';
            navigator.clipboard.writeText(text).then(function () {
                button.innerHTML = '<span class="bi bi-check2"></span><span class="ms-1">' + copiedLabel + '</span>';
                setTimeout(function () {
                    button.innerHTML = original;
                }, 1600);
            });
        });
    });

    const tokenInput = document.getElementById('apiDocsTokenInput');
    const tokenApply = document.getElementById('apiDocsTokenApply');
    const tokenReset = document.getElementById('apiDocsTokenReset');
    const tokenActive = document.getElementById('apiDocsTokenActive');
    const tokenStorageKey = 'mgw_api_docs_token';

    function setActiveToken(token) {
        if (!tokenActive) {
            return;
        }
        const display = token && token !== '' ? token : '{admin_token}';
        tokenActive.innerHTML = '<span class="bi bi-key me-2"></span><?php echo $L->g('api-docs-token-active'); ?> <span class="fw-semibold">' + display + '</span>';
        if (tokenInput && tokenInput.value !== token) {
            tokenInput.value = token;
        }
    }

    const savedToken = localStorage.getItem(tokenStorageKey);
    if (savedToken) {
        setActiveToken(savedToken);
    } else if (tokenInput && tokenInput.value) {
        setActiveToken(tokenInput.value);
    } else {
        setActiveToken('');
    }

    if (tokenApply) {
        tokenApply.addEventListener('click', function () {
            const value = tokenInput ? tokenInput.value.trim() : '';
            localStorage.setItem(tokenStorageKey, value);
            setActiveToken(value);
        });
    }

    if (tokenReset) {
        tokenReset.addEventListener('click', function () {
            localStorage.removeItem(tokenStorageKey);
            setActiveToken('');
        });
    }

    const playgroundForm = document.getElementById('apiDocsPlaygroundForm');
    const playgroundEndpoint = document.getElementById('apiDocsPlaygroundEndpoint');
    const playgroundMethod = document.getElementById('apiDocsPlaygroundMethod');
    const playgroundPath = document.getElementById('apiDocsPlaygroundPath');
    const playgroundHeaders = document.getElementById('apiDocsPlaygroundHeaders');
    const playgroundBody = document.getElementById('apiDocsPlaygroundBody');
    const playgroundBase = document.getElementById('apiDocsPlaygroundBase');
    const playgroundResponse = document.getElementById('apiDocsPlaygroundResponse');
    const playgroundStatus = document.getElementById('apiDocsPlaygroundStatus');
    const playgroundInfo = document.getElementById('apiDocsPlaygroundInfo');
    const loadButtons = document.querySelectorAll('.api-docs-load-playground');
    let lastPlaygroundPayload = null;

    function renderResponse(title, data) {
        if (!playgroundResponse) {
            return;
        }
        const content = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
        playgroundResponse.textContent = title + '\n' + content;
    }

    function updatePlaygroundFromDataset(dataset) {
        if (playgroundMethod && dataset.method) {
            playgroundMethod.value = dataset.method;
        }
        if (playgroundBase && dataset.base) {
            playgroundBase.value = dataset.base;
        }
        if (playgroundPath && dataset.path) {
            playgroundPath.value = dataset.path;
        }
        if (playgroundHeaders) {
            const storedToken = localStorage.getItem(tokenStorageKey) || (tokenInput ? tokenInput.value.trim() : '');
            if (dataset.headers) {
                try {
                    const parsedHeaders = JSON.parse(dataset.headers);
                    if (storedToken && parsedHeaders['X-Admin-Token']) {
                        parsedHeaders['X-Admin-Token'] = storedToken;
                    }
                    playgroundHeaders.value = JSON.stringify(parsedHeaders, null, 2);
                } catch (err) {
                    playgroundHeaders.value = dataset.headers;
                }
            } else {
                playgroundHeaders.value = storedToken ? JSON.stringify({'X-Admin-Token': storedToken}, null, 2) : '';
            }
        }
        if (playgroundBody) {
            playgroundBody.value = dataset.body || '';
        }
        if (playgroundStatus) {
            playgroundStatus.textContent = '<?php echo $L->g('api-docs-playground-loaded'); ?>';
            setTimeout(function () {
                playgroundStatus.textContent = '';
            }, 2000);
        }
    }

    if (playgroundEndpoint) {
        playgroundEndpoint.addEventListener('change', function () {
            const selected = playgroundEndpoint.selectedOptions[0];
            if (!selected) {
                return;
            }
            const dataset = {
                method: selected.getAttribute('data-method') || 'GET',
                base: selected.getAttribute('data-base') || 'panel',
                path: selected.getAttribute('data-path') || '/',
                playground: selected.getAttribute('data-playground') || '{}',
                success: selected.getAttribute('data-success') || '{}'
            };
            try {
                const playgroundData = JSON.parse(dataset.playground);
                dataset.headers = JSON.stringify(playgroundData.headers || {});
                dataset.body = playgroundData.body || '';
                if (playgroundData.base) {
                    dataset.base = playgroundData.base;
                }
            } catch (err) {
                dataset.headers = '{}';
            }
            lastPlaygroundPayload = dataset.success ? JSON.parse(dataset.success) : null;
            updatePlaygroundFromDataset(dataset);
        });
    }

    loadButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const dataset = {
                method: button.getAttribute('data-method') || 'GET',
                base: button.getAttribute('data-base') || 'panel',
                path: button.getAttribute('data-path') || '/',
                playground: button.getAttribute('data-playground') || '{}',
                success: button.getAttribute('data-success') || '{}'
            };
            try {
                const playgroundData = JSON.parse(dataset.playground);
                dataset.headers = JSON.stringify(playgroundData.headers || {});
                dataset.body = playgroundData.body || '';
                if (playgroundData.base) {
                    dataset.base = playgroundData.base;
                }
            } catch (err) {
                dataset.headers = '{}';
            }
            try {
                lastPlaygroundPayload = JSON.parse(dataset.success || '{}');
            } catch (err) {
                lastPlaygroundPayload = null;
            }
            updatePlaygroundFromDataset(dataset);
            if (playgroundInfo) {
                playgroundInfo.textContent = '<?php echo $L->g('api-docs-endpoint-load-playground'); ?>';
                setTimeout(function () {
                    playgroundInfo.textContent = '';
                }, 2000);
            }
        });
    });

    if (playgroundForm) {
        playgroundForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const method = playgroundMethod ? playgroundMethod.value : 'GET';
            const base = playgroundBase ? playgroundBase.value : 'panel';
            const path = playgroundPath ? playgroundPath.value : '/';
            const headers = playgroundHeaders ? playgroundHeaders.value : '';
            const body = playgroundBody ? playgroundBody.value : '';
            const token = localStorage.getItem(tokenStorageKey) || (tokenInput ? tokenInput.value.trim() : '');
            const baseUrls = <?php echo json_encode(isset($apiDocMeta['baseUrls']) ? $apiDocMeta['baseUrls'] : array()); ?>;
            const baseUrl = baseUrls[base] || '';
            const requestPreview = {
                method: method,
                url: baseUrl + path,
                headers: headers ? headers : '{}',
                body: body
            };
            if (playgroundInfo) {
                playgroundInfo.textContent = token ? 'Token: ' + token : '';
            }
            if (lastPlaygroundPayload && lastPlaygroundPayload.body) {
                renderResponse('Mocked response', lastPlaygroundPayload.body);
            } else {
                renderResponse('Request preview', requestPreview);
            }
        });
    }
})();
</script>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>api-docs.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">
