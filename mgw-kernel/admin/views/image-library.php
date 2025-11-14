<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$baseUrl = HTML_PATH_ADMIN_ROOT . 'image-library';
$activeType = isset($activeType) ? $activeType : 'entity';
$currentPerPage = isset($currentPerPage) ? (int)$currentPerPage : 20;
$perPageOptions = isset($perPageOptions) ? $perPageOptions : array(20, 50, 100, 200);
$activePagination = isset($activePagination) ? $activePagination : array(
	'entries' => array(),
	'total' => 0,
	'page' => 1,
	'perPage' => $currentPerPage,
	'totalPages' => 1,
	'offset' => 0
);
$entries = isset($activePagination['entries']) ? $activePagination['entries'] : array();
$totalItems = isset($activePagination['total']) ? (int)$activePagination['total'] : count($entries);
$pageOffset = isset($activePagination['offset']) ? (int)$activePagination['offset'] : 0;
$startIndex = $totalItems > 0 ? $pageOffset + 1 : 0;
$endIndex = $totalItems > 0 ? min($pageOffset + count($entries), $totalItems) : 0;
$currentPageNumber = isset($currentPageNumber) ? (int)$currentPageNumber : 1;
$totalPages = isset($totalPages) ? (int)$totalPages : 1;
$entityTotal = isset($entityStats['count']) ? (int)$entityStats['count'] : 0;
$linkTotal = isset($linkStats['count']) ? (int)$linkStats['count'] : 0;
$paginationSummary = $totalItems > 0
	? sprintf($L->g('image-library-pagination-summary'), $startIndex, $endIndex, $totalItems)
	: $L->g('image-library-pagination-empty');

$buildUrl = function ($type, $page, $perPage) use ($baseUrl) {
	$query = array(
		'type' => $type,
		'page' => max(1, (int)$page),
		'perPage' => max(1, (int)$perPage)
	);
	return $baseUrl . '?' . http_build_query($query);
};

$fixedCategoryOptions = array(
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
$defaultCategoryValue = 'other';
if (!in_array($defaultCategoryValue, $fixedCategoryOptions, true) && !empty($fixedCategoryOptions)) {
    $defaultCategoryValue = $fixedCategoryOptions[0];
}
?>

<div class="image-library-page container-fluid px-0"
	data-admin-root="<?php echo HTML_PATH_ADMIN_ROOT; ?>"
	data-confirm-delete="<?php echo Sanitize::html($L->g('image-library-confirm-delete')); ?>"
	data-confirm-bulk="<?php echo Sanitize::html($L->g('image-library-confirm-bulk')); ?>"
    data-confirm-title="<?php echo Sanitize::html($L->g('image-library-confirm-title')); ?>"
    data-confirm-label="<?php echo Sanitize::html($L->g('image-library-bulk-delete')); ?>"
    data-confirm-cancel="<?php echo Sanitize::html($L->g('image-library-upload-cancel')); ?>"
	data-error-no-selection="<?php echo Sanitize::html($L->g('image-library-error-no-selection')); ?>"
    data-error-upload-entity="<?php echo Sanitize::html($L->g('image-library-error-upload-entity-files')); ?>"
    data-error-upload-link="<?php echo Sanitize::html($L->g('image-library-error-upload-link-files')); ?>"
    data-error-category-empty="<?php echo Sanitize::html($L->g('image-library-error-category-empty')); ?>"
    data-error-upload-type="<?php echo Sanitize::html($L->g('image-library-error-upload-type')); ?>"
	data-active-type="<?php echo Sanitize::html($activeType); ?>"
	data-default-upload-type="none"
>
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4" data-library-header>
        <div>
            <h2 class="mb-1">
                <span class="bi bi-images"></span>
                <span><?php echo $L->g('image-library-title'); ?></span>
            </h2>
            <p class="text-muted mb-0 small"><?php echo $L->g('image-library-subtitle'); ?></p>
        </div>
        <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-primary" data-upload-trigger>
                    <span class="bi bi-upload me-1"></span>
                    <span><?php echo $L->g('image-library-upload-open'); ?></span>
                </button>
                <div class="btn-group" role="group" data-type-switches>
                    <a href="<?php echo Sanitize::html($buildUrl('entity', 1, $currentPerPage)); ?>"
                        class="btn <?php echo $activeType === 'entity' ? 'btn-primary' : 'btn-outline-primary'; ?>"
                        data-switch-type="entity"
                        data-type-button="entity">
                        <span class="bi bi-image me-1"></span>
                        <span><?php echo $L->g('image-library-type-entity'); ?></span>
                        <span class="badge bg-light text-dark border ms-2" data-type-count="entity"><?php echo $entityTotal; ?></span>
                    </a>
                    <a href="<?php echo Sanitize::html($buildUrl('link', 1, $currentPerPage)); ?>"
                        class="btn <?php echo $activeType === 'link' ? 'btn-success' : 'btn-outline-success'; ?>"
                        data-switch-type="link"
                        data-type-button="link">
                        <span class="bi bi-link-45deg me-1"></span>
                        <span><?php echo $L->g('image-library-type-link'); ?></span>
                        <span class="badge bg-light text-dark border ms-2" data-type-count="link"><?php echo $linkTotal; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" data-library-overview>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1"><?php echo $L->g('image-library-entity-count'); ?></div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="h4 mb-0" data-statistic="entity-count"><?php echo (int)($entityStats['count'] ?? 0); ?></div>
                        <span class="badge rounded-pill bg-primary"><span class="bi bi-collection"></span></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1"><?php echo $L->g('image-library-entity-size'); ?></div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="h4 mb-0" data-statistic="entity-size"><?php echo Sanitize::html($imageLibrary->formatBytes($entityStats['size'] ?? 0)); ?></div>
                        <span class="badge rounded-pill bg-primary"><span class="bi bi-database"></span></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1"><?php echo $L->g('image-library-link-count'); ?></div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="h4 mb-0" data-statistic="link-count"><?php echo (int)($linkStats['count'] ?? 0); ?></div>
                        <span class="badge rounded-pill bg-success"><span class="bi bi-card-list"></span></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1"><?php echo $L->g('image-library-link-size'); ?></div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="h4 mb-0" data-statistic="link-size"><?php echo Sanitize::html($imageLibrary->formatBytes($linkStats['size'] ?? 0)); ?></div>
                        <span class="badge rounded-pill bg-success"><span class="bi bi-hdd"></span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" data-library-table>
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 text-uppercase text-muted">
                <?php echo $activeType === 'entity' ? $L->g('image-library-entities-title') : $L->g('image-library-links-title'); ?>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <?php if ($activeType === 'entity'): ?>
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 280px;"><?php echo $L->g('image-library-col-name'); ?></th>
                                <th style="width: 200px;"><?php echo $L->g('image-library-col-category'); ?></th>
                                <th class="text-center" style="width: 120px;"><?php echo $L->g('image-library-col-size'); ?></th>
                                <th class="text-center" style="width: 180px;"><?php echo $L->g('image-library-col-uploaded'); ?></th>
                                <th class="text-center" style="width: 120px;"><?php echo $L->g('image-library-col-actions'); ?></th>
                                <th style="width: 40px;" class="text-center">
                                    <input type="checkbox" class="form-check-input" data-select-all="entity">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($entries)): ?>
                                <?php foreach ($entries as $entry): ?>
                                    <tr data-entry-row="entity" data-entry-id="<?php echo Sanitize::html($entry['id']); ?>">
                                        <td>
                                            <div class="fw-semibold text-truncate d-block" style="max-width: 240px;" title="<?php echo Sanitize::html($entry['original_name']); ?>"><?php echo Sanitize::html($entry['original_name']); ?></div>
                                            <div class="small text-muted text-truncate d-block" style="max-width: 240px;" title="<?php echo Sanitize::html($entry['stored_name']); ?>"><?php echo Sanitize::html($entry['stored_name']); ?></div>
                                        </td>
                                        <td>
                                            <?php $categoryLabel = isset($entry['category_label']) ? $entry['category_label'] : $L->g('image-library-category-' . $entry['category']); ?>
                                            <span class="badge bg-primary-subtle text-primary text-truncate d-inline-block" style="max-width: 160px;" title="<?php echo Sanitize::html($categoryLabel); ?>"><?php echo Sanitize::html($categoryLabel); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"><?php echo Sanitize::html($imageLibrary->formatBytes($entry['size'])); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="small text-muted"><?php echo Sanitize::html($entry['uploaded_at']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-danger" data-delete="entity" data-entry-id="<?php echo Sanitize::html($entry['id']); ?>">
                                                    <span class="bi bi-trash"></span>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input" data-select="entity" value="<?php echo Sanitize::html($entry['id']); ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <span class="bi bi-inboxes fs-1 d-block mb-2"></span>
                                        <span><?php echo $L->g('image-library-empty-entities'); ?></span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 320px;"><?php echo $L->g('image-library-col-name'); ?></th>
                                <th style="width: 120px;" class="text-center"><?php echo $L->g('image-library-col-category'); ?></th>
                                <th style="width: 120px;" class="text-center"><?php echo $L->g('image-library-col-quantity'); ?></th>
                                <th style="width: 110px;" class="text-center"><?php echo $L->g('image-library-col-size'); ?></th>
                                <th style="width: 160px;" class="text-center"><?php echo $L->g('image-library-col-uploaded'); ?></th>
                                <th style="width: 120px;" class="text-center"><?php echo $L->g('image-library-col-actions'); ?></th>
                                <th style="width: 40px;" class="text-center">
                                    <input type="checkbox" class="form-check-input" data-select-all="link">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($entries)): ?>
                                <?php foreach ($entries as $entry): ?>
                                    <tr data-entry-row="link" data-entry-id="<?php echo Sanitize::html($entry['id']); ?>">
                                        <td>
                                            <div class="fw-semibold text-truncate d-block" style="max-width: 320px;" title="<?php echo Sanitize::html($entry['original_name']); ?>"><?php echo Sanitize::html($entry['original_name']); ?></div>
                                            <div class="small text-muted text-truncate d-block" style="max-width: 320px;" title="<?php echo Sanitize::html($entry['stored_name']); ?>"><?php echo Sanitize::html($entry['stored_name']); ?></div>
                                        </td>
                                        <td class="text-center">
                                            <?php $categoryLabel = isset($entry['category_label']) ? $entry['category_label'] : $L->g('image-library-category-' . $entry['category']); ?>
                                            <span class="badge bg-success-subtle text-success text-truncate d-inline-block" style="max-width: 160px;" title="<?php echo Sanitize::html($categoryLabel); ?>"><?php echo Sanitize::html($categoryLabel); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"><?php echo (int)$entry['quantity']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"><?php echo Sanitize::html($imageLibrary->formatBytes($entry['size'])); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="small text-muted"><?php echo Sanitize::html($entry['uploaded_at']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-danger" data-delete="link" data-entry-id="<?php echo Sanitize::html($entry['id']); ?>">
                                                    <span class="bi bi-trash"></span>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input" data-select="link" value="<?php echo Sanitize::html($entry['id']); ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <span class="bi bi-cloud-slash fs-1 d-block mb-2"></span>
                                        <span><?php echo $L->g('image-library-empty-links'); ?></span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="small text-muted" data-pagination-summary><?php echo $paginationSummary; ?></div>
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-end flex-wrap gap-2">
                    <form id="imageLibraryPerPageForm" method="get" class="d-flex align-items-center gap-2 mb-0">
                        <input type="hidden" name="type" value="<?php echo Sanitize::html($activeType); ?>">
                        <input type="hidden" name="page" value="<?php echo $currentPageNumber; ?>">
                        <label class="form-label mb-0 small text-muted text-nowrap" for="imageLibraryPerPageSelect"><?php echo $L->g('image-library-pagination-per-page'); ?></label>
                        <select id="imageLibraryPerPageSelect" name="perPage" class="form-select form-select-sm" data-per-page-select>
                            <?php foreach ($perPageOptions as $option): ?>
                                <option value="<?php echo (int)$option; ?>" <?php echo $option == $currentPerPage ? 'selected' : ''; ?>><?php echo (int)$option; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <?php if ($totalPages > 1): ?>
                        <nav class="order-0">
                            <ul class="pagination pagination-sm mb-0 justify-content-center justify-content-md-end">
                                <?php
                                $prevPage = max(1, $currentPageNumber - 1);
                                if ($currentPageNumber === 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">' . Sanitize::html($L->g('image-library-pagination-prev')) . '</span></li>';
                                } else {
                                    echo '<li class="page-item"><a class="page-link" href="' . Sanitize::html($buildUrl($activeType, $prevPage, $currentPerPage)) . '">' . Sanitize::html($L->g('image-library-pagination-prev')) . '</a></li>';
                                }

                                $range = 2;
                                $startPage = max(1, $currentPageNumber - $range);
                                $endPage = min($totalPages, $currentPageNumber + $range);

                                if ($startPage > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="' . Sanitize::html($buildUrl($activeType, 1, $currentPerPage)) . '">1</a></li>';
                                    if ($startPage > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                    }
                                }

                                for ($page = $startPage; $page <= $endPage; $page++) {
                                    $activeClass = $page === $currentPageNumber ? ' active' : '';
                                    echo '<li class="page-item' . $activeClass . '"><a class="page-link" href="' . Sanitize::html($buildUrl($activeType, $page, $currentPerPage)) . '">' . $page . '</a></li>';
                                }

                                if ($endPage < $totalPages) {
                                    if ($endPage < $totalPages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="' . Sanitize::html($buildUrl($activeType, $totalPages, $currentPerPage)) . '">' . $totalPages . '</a></li>';
                                }

                                $nextPage = min($totalPages, $currentPageNumber + 1);
                                if ($currentPageNumber === $totalPages) {
                                    echo '<li class="page-item disabled"><span class="page-link">' . Sanitize::html($L->g('image-library-pagination-next')) . '</span></li>';
                                } else {
                                    echo '<li class="page-item"><a class="page-link" href="' . Sanitize::html($buildUrl($activeType, $nextPage, $currentPerPage)) . '">' . Sanitize::html($L->g('image-library-pagination-next')) . '</a></li>';
                                }
                                ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bulk-delete disabled>
                        <span class="bi bi-trash3"></span>
                        <span class="ms-1"><?php echo $L->g('image-library-bulk-delete'); ?></span>
                        <span class="badge bg-danger-subtle text-danger ms-2 d-none" data-bulk-count></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="imageLibraryUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $L->g('image-library-upload-title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="imageLibraryUploadForm" method="post" enctype="multipart/form-data" data-no-spa="true">
                <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>">
                <input type="hidden" name="action" value="upload">
                <input type="hidden" name="library_type" value="" data-upload-library-type>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <span class="form-label d-block"><?php echo $L->g('image-library-upload-mode'); ?></span>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="uploadLibraryMode" id="uploadTypeEntity" data-upload-type="entity">
                                <label class="form-check-label" for="uploadTypeEntity"><?php echo $L->g('image-library-upload-mode-entity'); ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="uploadLibraryMode" id="uploadTypeLink" data-upload-type="link">
                                <label class="form-check-label" for="uploadTypeLink"><?php echo $L->g('image-library-upload-mode-link'); ?></label>
                            </div>
                            <div class="form-text"><?php echo $L->g('image-library-upload-mode-hint'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <label for="uploadCategorySelect" class="form-label"><?php echo $L->g('image-library-upload-category'); ?></label>
                            <select class="form-select" id="uploadCategorySelect" data-category-select>
                                <?php foreach ($fixedCategoryOptions as $option): ?>
                                    <option value="<?php echo Sanitize::html($option); ?>" <?php echo $option === $defaultCategoryValue ? 'selected' : ''; ?>>
                                        <?php echo Sanitize::html($L->g('image-library-category-' . $option)); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="__custom__"><?php echo Sanitize::html($L->g('image-library-upload-category-custom')); ?></option>
                            </select>
                            <input type="text" class="form-control mt-2 d-none" id="uploadCategoryCustom" data-category-custom placeholder="<?php echo Sanitize::html($L->g('image-library-upload-category-placeholder')); ?>">
                            <input type="hidden" id="uploadCategoryValue" name="category" value="<?php echo Sanitize::html($defaultCategoryValue); ?>" data-default-category="<?php echo Sanitize::html($defaultCategoryValue); ?>">
                            <div class="form-text"><?php echo $L->g('image-library-upload-category-hint'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <label for="uploadFiles" class="form-label"><?php echo $L->g('image-library-upload-files'); ?></label>
                            <input type="file" class="form-control" id="uploadFiles" name="files[]" multiple required>
                            <div class="form-text d-none" data-upload-hint-entity><?php echo $L->g('image-library-upload-entity-desc'); ?></div>
                            <div class="form-text d-none" data-upload-hint-link><?php echo $L->g('image-library-upload-link-desc'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L->g('image-library-upload-cancel'); ?></button>
                    <button type="submit" class="btn btn-primary disabled" disabled>
                        <span class="spinner-border spinner-border-sm align-middle me-2 d-none" role="status" aria-hidden="true"></span>
                        <span class="label-text"><?php echo $L->g('image-library-upload-submit'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="imageLibraryDeleteForm" method="post" class="d-none" data-no-spa="true">
    <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="library_type" value="">
    <input type="hidden" name="id" value="">
</form>

<form id="imageLibraryBulkDeleteForm" method="post" class="d-none" data-no-spa="true">
    <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>">
    <input type="hidden" name="action" value="bulk-delete">
    <input type="hidden" name="library_type" value="">
    <div data-bulk-ids-container></div>
</form>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>image-library.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">
