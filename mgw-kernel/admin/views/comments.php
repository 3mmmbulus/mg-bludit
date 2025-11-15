<?php defined('MAIGEWAN') or die('Maigewan CMS.');

$dataset = isset($commentsDataset) && is_array($commentsDataset) ? $commentsDataset : array();
$categories = isset($commentsCategories) && is_array($commentsCategories) ? $commentsCategories : array();
$statusOptions = isset($commentsStatusOptions) && is_array($commentsStatusOptions) ? $commentsStatusOptions : array();
$sources = isset($commentsSources) && is_array($commentsSources) ? $commentsSources : array();
$typeCounters = isset($commentsTypeCounters) && is_array($commentsTypeCounters) ? $commentsTypeCounters : array('content' => 0, 'nickname' => 0);
$industrySummary = isset($commentsIndustrySummary) && is_array($commentsIndustrySummary) ? $commentsIndustrySummary : array();
$statusSummary = isset($commentsStatusSummary) && is_array($commentsStatusSummary) ? $commentsStatusSummary : array();
$totalRecords = isset($commentsTotalRecords) ? (int) $commentsTotalRecords : count($dataset);
$activeRecords = isset($commentsActiveRecords) ? (int) $commentsActiveRecords : 0;
$maxUploadFiles = isset($commentsMaxUploadFiles) ? (int) $commentsMaxUploadFiles : 100;

$datasetJson = Sanitize::html(json_encode($dataset));
$statusOptionsJson = Sanitize::html(json_encode($statusOptions));
$categoriesJson = Sanitize::html(json_encode($categories));
$sourcesJson = Sanitize::html(json_encode($sources));
$statusSummaryJson = Sanitize::html(json_encode($statusSummary));
$typeCountersJson = Sanitize::html(json_encode($typeCounters));
$industrySummaryJson = Sanitize::html(json_encode($industrySummary));

$tokenCSRF = $security->getTokenCSRF();

$translations = array(
    'filterAllCategories' => $L->g('comments-library-filter-category-all'),
    'filterAllStatuses' => $L->g('comments-library-filter-status-all'),
    'filterAllSources' => $L->g('comments-library-filter-source-all'),
    'searchPlaceholder' => $L->g('comments-library-search-placeholder'),
    'tableEmpty' => $L->g('comments-library-table-empty'),
    'tableSummary' => $L->g('comments-library-table-summary'),
    'tableTypeContent' => $L->g('comments-library-type-content'),
    'tableTypeNickname' => $L->g('comments-library-type-nickname'),
    'statusActive' => $L->g('comments-library-status-active'),
    'statusPaused' => $L->g('comments-library-status-paused'),
    'statusArchived' => $L->g('comments-library-status-archived'),
    'industryEmpty' => $L->g('comments-library-industry-empty'),
    'detailTitle' => $L->g('comments-library-detail-title'),
    'detailTabComments' => $L->g('comments-library-detail-tab-comments'),
    'detailTabNicknames' => $L->g('comments-library-detail-tab-nicknames'),
    'detailMetaIndustry' => $L->g('comments-library-detail-meta-industry'),
    'detailMetaSource' => $L->g('comments-library-detail-meta-source'),
    'detailMetaImported' => $L->g('comments-library-detail-meta-imported'),
    'detailMetaUsage' => $L->g('comments-library-detail-meta-usage'),
    'detailMetaStatus' => $L->g('comments-library-detail-meta-status'),
    'detailMetaType' => $L->g('comments-library-detail-meta-type'),
    'detailEmptyPreview' => $L->g('comments-library-detail-empty'),
    'detailPreviewCount' => $L->g('comments-library-detail-preview-count'),
    'confirmDeleteSingle' => $L->g('comments-library-confirm-delete-single'),
    'confirmDeleteBulk' => $L->g('comments-library-confirm-delete-bulk'),
    'bulkDeleteLabel' => $L->g('comments-library-bulk-delete'),
    'bulkDeleteNone' => $L->g('comments-library-bulk-delete-none'),
    'uploadSuccess' => $L->g('comments-library-upload-success-generic'),
    'uploadLimitExceeded' => $L->g('comments-library-upload-limit-exceeded'),
    'uploadErrorType' => $L->g('comments-library-upload-error-type'),
    'uploadErrorCategory' => $L->g('comments-library-upload-error-category'),
    'uploadErrorFiles' => $L->g('comments-library-upload-error-files'),
    'statusBadgeActive' => $L->g('comments-library-status-active'),
    'statusBadgePaused' => $L->g('comments-library-status-paused'),
    'statusBadgeArchived' => $L->g('comments-library-status-archived'),
    'statusMenuActive' => $L->g('comments-library-status-menu-active'),
    'statusMenuPaused' => $L->g('comments-library-status-menu-paused'),
    'statusMenuArchived' => $L->g('comments-library-status-menu-archived'),
    'toastTitle' => $L->g('comments-library-toast-title')
);
$translationsJson = Sanitize::html(json_encode($translations));

function mgwCommentsIndustryLabel($key, $language)
{
    $localized = $language->g('logo-library-category-' . $key);
    if ($localized !== 'logo-library-category-' . $key) {
        return $localized;
    }

    $fallback = $language->g('comments-library-category-' . $key);
    if ($fallback !== 'comments-library-category-' . $key) {
        return $fallback;
    }

    return ucfirst((string) $key);
}
?>
<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>comments.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">

<div class="comments-library-page logo-library-page image-library-page asset-library-page container-fluid px-0"
    data-comments-root
    data-dataset="<?php echo $datasetJson; ?>"
    data-status-options="<?php echo $statusOptionsJson; ?>"
    data-categories="<?php echo $categoriesJson; ?>"
    data-sources="<?php echo $sourcesJson; ?>"
    data-status-summary="<?php echo $statusSummaryJson; ?>"
    data-type-summary="<?php echo $typeCountersJson; ?>"
    data-industry-summary="<?php echo $industrySummaryJson; ?>"
    data-translations="<?php echo $translationsJson; ?>"
    data-endpoint="<?php echo HTML_PATH_ADMIN_ROOT . 'ajax/comments-library'; ?>"
    data-token="<?php echo Sanitize::html($tokenCSRF); ?>"
    data-max-upload="<?php echo (int) $maxUploadFiles; ?>"
>
    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4" data-library-header>
        <div>
            <h2 class="mb-1 d-flex align-items-center gap-2">
                <span class="bi bi-chat-square-dots"></span>
                <span><?php echo $L->g('comments-library-page-title'); ?></span>
            </h2>
            <p class="text-muted mb-0 small d-flex flex-column flex-lg-row gap-2">
                <span><?php echo $L->g('comments-library-page-subtitle'); ?></span>
                <span class="text-primary-emphasis"><?php echo $L->g('comments-library-detail-preview-limit'); ?></span>
            </p>
        </div>
        <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
            <div class="btn-group" role="group" data-comments-type-switch>
                <button type="button" class="btn btn-outline-secondary" data-type="all" data-variant="secondary">
                    <span class="bi bi-collection me-1"></span>
                    <span><?php echo $L->g('comments-library-filter-type-all'); ?></span>
                </button>
                <button type="button" class="btn btn-outline-primary" data-type="content" data-variant="primary">
                    <span class="bi bi-chat-left-text me-1"></span>
                    <span><?php echo $L->g('comments-library-type-content'); ?></span>
                    <span class="badge bg-light text-dark border ms-2" data-type-count="content"><?php echo (int) ($typeCounters['content'] ?? 0); ?></span>
                </button>
                <button type="button" class="btn btn-outline-success" data-type="nickname" data-variant="success">
                    <span class="bi bi-person-lines-fill me-1"></span>
                    <span><?php echo $L->g('comments-library-type-nickname'); ?></span>
                    <span class="badge bg-light text-dark border ms-2" data-type-count="nickname"><?php echo (int) ($typeCounters['nickname'] ?? 0); ?></span>
                </button>
            </div>
            <button type="button" class="btn btn-primary" data-comments-upload-trigger>
                <span class="bi bi-upload me-1"></span>
                <span><?php echo $L->g('comments-library-upload-button'); ?></span>
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4" data-comments-metrics>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1"><?php echo $L->g('comments-library-metric-total'); ?></div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="h4 mb-0" data-metric-total><?php echo (int) $totalRecords; ?></div>
                        <span class="badge rounded-pill bg-primary-subtle text-primary"><span class="bi bi-collection"></span></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1"><?php echo $L->g('comments-library-metric-active'); ?></div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="h4 mb-0" data-metric-active><?php echo (int) $activeRecords; ?></div>
                        <span class="badge rounded-pill bg-success-subtle text-success"><span class="bi bi-lightning"></span></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1"><?php echo $L->g('comments-library-metric-content'); ?></div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="h4 mb-0" data-metric-content><?php echo (int) ($typeCounters['content'] ?? 0); ?></div>
                        <span class="badge rounded-pill bg-info-subtle text-info"><span class="bi bi-chat-left-text"></span></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1"><?php echo $L->g('comments-library-metric-nicknames'); ?></div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="h4 mb-0" data-metric-nickname><?php echo (int) ($typeCounters['nickname'] ?? 0); ?></div>
                        <span class="badge rounded-pill bg-warning-subtle text-warning"><span class="bi bi-person-lines-fill"></span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" data-comments-filters>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label small text-muted" for="commentsFilterCategory"><?php echo $L->g('comments-library-filter-category'); ?></label>
                    <select class="form-select" id="commentsFilterCategory" data-filter-category>
                        <option value="all"><?php echo $L->g('comments-library-filter-category-all'); ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo Sanitize::html($category); ?>"><?php echo Sanitize::html(mgwCommentsIndustryLabel($category, $L)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label small text-muted" for="commentsFilterStatus"><?php echo $L->g('comments-library-filter-status'); ?></label>
                    <select class="form-select" id="commentsFilterStatus" data-filter-status>
                        <option value="all"><?php echo $L->g('comments-library-filter-status-all'); ?></option>
                        <?php foreach ($statusOptions as $status): ?>
                            <?php $label = $L->g('comments-library-status-' . $status); ?>
                            <option value="<?php echo Sanitize::html($status); ?>"><?php echo Sanitize::html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label small text-muted" for="commentsFilterSource"><?php echo $L->g('comments-library-filter-source'); ?></label>
                    <select class="form-select" id="commentsFilterSource" data-filter-source>
                        <option value="all"><?php echo $L->g('comments-library-filter-source-all'); ?></option>
                        <?php foreach ($sources as $source): ?>
                            <option value="<?php echo Sanitize::html($source); ?>"><?php echo Sanitize::html($source); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label small text-muted" for="commentsFilterSearch"><?php echo $L->g('comments-library-filter-search'); ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-search"></span></span>
                        <input type="search" class="form-control" id="commentsFilterSearch" data-filter-search placeholder="<?php echo Sanitize::html($L->g('comments-library-search-placeholder')); ?>">
                    </div>
                </div>
                <div class="col-12 col-lg-auto">
                    <button type="button" class="btn btn-light" data-filter-reset>
                        <span class="bi bi-arrow-counterclockwise"></span>
                        <span class="ms-2"><?php echo $L->g('comments-library-filter-reset'); ?></span>
                    </button>
                </div>
            </div>
            <div class="mt-4">
                <h6 class="text-uppercase text-muted small mb-2"><?php echo $L->g('comments-library-industry-panel-title'); ?></h6>
                <ul class="list-group list-group-flush" data-industry-summary>
                    <?php if (!empty($industrySummary)): ?>
                        <?php foreach ($industrySummary as $category => $count): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-truncate" title="<?php echo Sanitize::html(mgwCommentsIndustryLabel($category, $L)); ?>"><?php echo Sanitize::html(mgwCommentsIndustryLabel($category, $L)); ?></span>
                                <span class="badge bg-primary-subtle text-primary"><?php echo (int) $count; ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted small"><?php echo $L->g('comments-library-industry-empty'); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" data-comments-table-card>
        <div class="card-header bg-white py-3 d-flex flex-column flex-lg-row gap-2 align-items-lg-center justify-content-between">
            <div>
                <h6 class="mb-0 text-uppercase text-muted small"><?php echo $L->g('comments-library-table-title'); ?></h6>
                <div class="text-muted small" data-table-summary></div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label small text-muted mb-0" for="commentsPerPageSelect"><?php echo $L->g('comments-library-table-page-size'); ?></label>
                <select class="form-select form-select-sm" id="commentsPerPageSelect" data-table-size>
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" data-comments-table>
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" class="form-check-input" data-select-all>
                            </th>
                            <th style="min-width: 200px;">&nbsp;<?php echo $L->g('comments-library-col-type'); ?></th>
                            <th style="min-width: 220px;">&nbsp;<?php echo $L->g('comments-library-col-source'); ?></th>
                            <th style="min-width: 160px;" class="text-center"><?php echo $L->g('comments-library-col-category'); ?></th>
                            <th style="width: 120px;" class="text-center"><?php echo $L->g('comments-library-col-lines'); ?></th>
                            <th style="width: 120px;" class="text-center"><?php echo $L->g('comments-library-col-usage'); ?></th>
                            <th style="width: 160px;" class="text-center"><?php echo $L->g('comments-library-col-imported'); ?></th>
                            <th style="width: 120px;" class="text-center"><?php echo $L->g('comments-library-col-status'); ?></th>
                            <th style="width: 140px;" class="text-end">&nbsp;<?php echo $L->g('comments-library-col-actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody data-table-body>
                        <tr class="table-empty">
                            <td colspan="9" class="text-center py-5 text-muted">
                                <span class="bi bi-inboxes fs-1 d-block mb-2"></span>
                                <span><?php echo $L->g('comments-library-table-empty'); ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="small text-muted" data-pagination-summary></div>
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 justify-content-md-end">
                    <nav>
                        <ul class="pagination pagination-sm mb-0" data-pagination></ul>
                    </nav>
                    <button type="button" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" data-comments-bulk-delete disabled>
                        <span class="bi bi-trash3"></span>
                        <span data-bulk-delete-label><?php echo $L->g('comments-library-bulk-delete-none'); ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="commentsUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $L->g('comments-library-upload-title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="commentsUploadForm" method="post" enctype="multipart/form-data" data-comments-upload>
                <input type="hidden" name="tokenCSRF" value="<?php echo Sanitize::html($tokenCSRF); ?>">
                <input type="hidden" name="action" value="upload">
                <input type="hidden" name="library_type" value="" data-upload-type-field>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label d-block small text-muted"><?php echo $L->g('comments-library-upload-mode'); ?></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="uploadMode" id="uploadModeContent" data-upload-type value="content">
                                <label class="form-check-label" for="uploadModeContent"><?php echo $L->g('comments-library-type-content'); ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="uploadMode" id="uploadModeNickname" data-upload-type value="nickname">
                                <label class="form-check-label" for="uploadModeNickname"><?php echo $L->g('comments-library-type-nickname'); ?></label>
                            </div>
                            <div class="form-text"><?php echo $L->g('comments-library-upload-mode-hint'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted" for="commentsUploadCategory"><?php echo $L->g('comments-library-upload-category'); ?></label>
                            <select class="form-select" id="commentsUploadCategory" name="category" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo Sanitize::html($category); ?>"><?php echo Sanitize::html(mgwCommentsIndustryLabel($category, $L)); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text"><?php echo $L->g('comments-library-upload-category-hint'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted" for="commentsUploadFiles"><?php echo $L->g('comments-library-upload-files'); ?></label>
                            <input type="file" class="form-control" id="commentsUploadFiles" name="files[]" accept=".txt" multiple required>
                            <div class="form-text"><?php echo sprintf($L->g('comments-library-upload-limit'), (int) $maxUploadFiles); ?></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo $L->g('comments-library-upload-cancel'); ?></button>
                    <button type="submit" class="btn btn-primary" data-upload-submit disabled>
                        <span class="bi bi-upload"></span>
                        <span class="ms-2"><?php echo $L->g('comments-library-upload-submit'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="commentsDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" data-detail-title><?php echo $L->g('comments-library-detail-title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="text-muted small"><?php echo $L->g('comments-library-detail-meta-type'); ?></div>
                                    <div class="fw-semibold" data-detail-type></div>
                                    <div class="small text-muted" data-detail-source></div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small"><?php echo $L->g('comments-library-detail-meta-industry'); ?></div>
                                    <div class="fw-semibold" data-detail-industry></div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small"><?php echo $L->g('comments-library-detail-meta-imported'); ?></div>
                                    <div class="fw-semibold" data-detail-imported></div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small"><?php echo $L->g('comments-library-detail-meta-usage'); ?></div>
                                    <div class="fw-semibold" data-detail-usage></div>
                                </div>
                                <div>
                                    <div class="text-muted small"><?php echo $L->g('comments-library-detail-meta-status'); ?></div>
                                    <span class="badge bg-primary-subtle text-primary" data-detail-status></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 text-uppercase text-muted small"><?php echo $L->g('comments-library-detail-preview-title'); ?></h6>
                                <span class="badge bg-light text-muted" data-detail-line-count></span>
                            </div>
                            <div class="card-body">
                                <pre class="comments-detail-preview" data-detail-preview><?php echo $L->g('comments-library-detail-empty'); ?></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo $L->g('comments-library-detail-close'); ?></button>
            </div>
        </div>
    </div>
</div>
