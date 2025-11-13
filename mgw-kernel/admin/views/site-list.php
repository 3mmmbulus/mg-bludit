<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<style>
.modal-backdrop.fade.show {
    z-index: 1990 !important;
}

.modal,
.modal-dialog,
.modal-content {
    z-index: 2000 !important;
}

.sidebar {
    z-index: 900 !important;
}

.site-groups-page .site-group-table {
    table-layout: fixed;
    min-width: 1000px;
}

.site-groups-page .site-group-table th,
.site-groups-page .site-group-table td {
    vertical-align: middle;
}

.site-groups-page .site-group-table .col-id {
    width: 70px;
}

.site-groups-page .site-group-table .col-name {
    width: 110px;
}

.site-groups-page .site-group-table .col-type {
    width: 140px;
}

.site-groups-page .site-group-table .col-domains {
    width: 130px;
}

.site-groups-page .site-group-table .col-category {
    width: 120px;
}

.site-groups-page .site-group-table .col-count {
    width: 100px;
}

.site-groups-page .site-group-table .col-actions {
    width: 110px;
}

.site-groups-page .domains-list .domain-item {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.choice-card-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 0.75rem;
}

.choice-card-stack {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.choice-card.btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 0.75rem;
    width: 100%;
    padding: 0.65rem 0.9rem;
    border-radius: 0.65rem;
    border-width: 1px;
    text-align: left;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
}

.choice-card.btn .choice-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    background-color: rgba(13, 110, 253, 0.08);
    color: #0d6efd;
    transition: all 0.2s ease-in-out;
}

.choice-card.btn .choice-label {
    flex-grow: 1;
    line-height: 1.2;
}

.choice-card.btn .choice-state {
    margin-left: auto;
    font-size: 1.1rem;
    opacity: 0.35;
    transition: all 0.2s ease-in-out;
}

.choice-card.btn.active .choice-state {
    opacity: 1;
}

.choice-card.btn-outline-primary,
.choice-card.btn-outline-secondary,
.choice-card.btn-outline-success,
.choice-card.btn-outline-danger {
    background-color: #fff;
    border-color: var(--bs-border-color);
    color: var(--bs-body-color);
}

.choice-card.btn-primary {
    background-color: rgba(13, 110, 253, 0.12);
    border-color: #0d6efd;
    color: #0d6efd;
}

.choice-card.btn-secondary {
    background-color: rgba(108, 117, 125, 0.12);
    border-color: #6c757d;
    color: #343a40;
}

.choice-card.btn-success {
    background-color: rgba(25, 135, 84, 0.12);
    border-color: #198754;
    color: #198754;
}

.choice-card.btn-danger {
    background-color: rgba(220, 53, 69, 0.12);
    border-color: #dc3545;
    color: #dc3545;
}

.choice-card.btn-primary .choice-icon {
    background-color: #0d6efd;
    color: #fff;
}

.choice-card.btn-secondary .choice-icon {
    background-color: #6c757d;
    color: #fff;
}

.choice-card.btn-success .choice-icon {
    background-color: #198754;
    color: #fff;
}

.choice-card.btn-danger .choice-icon {
    background-color: #dc3545;
    color: #fff;
}

.choice-card.btn-outline-secondary .choice-icon {
    background-color: rgba(108, 117, 125, 0.12);
    color: #6c757d;
}

.choice-card.btn-outline-success .choice-icon {
    background-color: rgba(25, 135, 84, 0.12);
    color: #198754;
}

.choice-card.btn-outline-danger .choice-icon {
    background-color: rgba(220, 53, 69, 0.12);
    color: #dc3545;
}

.choice-card.disabled,
.choice-card:disabled {
    opacity: 0.5;
    pointer-events: none;
}
</style>

<div class="site-groups-page container-fluid px-0">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1">
                <span class="bi bi-globe"></span>
                <span><?php echo $L->g('site-list-title'); ?></span>
            </h2>
            <p class="text-muted mb-0 small"><?php echo $L->g('site-list-subtitle'); ?></p>
        </div>
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
            <button type="button" class="btn btn-primary btn-add-group">
                <span class="bi bi-plus-circle"></span>
                <span><?php echo $L->g('site-list-add-group'); ?></span>
            </button>
            <div class="search-box">
                <input type="text" id="searchGroups" class="form-control" placeholder="<?php echo $L->g('site-list-search-placeholder'); ?>">
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small"><?php echo $L->g('site-list-total-groups'); ?></div>
                            <div class="h4 mb-0"><?php echo (int)($siteGroupSummary['total_groups'] ?? 0); ?></div>
                        </div>
                        <span class="badge rounded-pill bg-primary fs-6"><span class="bi bi-layers"></span></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small"><?php echo $L->g('site-list-total-sites'); ?></div>
                            <div class="h4 mb-0"><?php echo (int)($siteGroupSummary['total_sites'] ?? 0); ?></div>
                        </div>
                        <span class="badge rounded-pill bg-success fs-6"><span class="bi bi-hdd-network"></span></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small"><?php echo $L->g('site-list-active-groups'); ?></div>
                            <div class="h4 mb-0"><?php echo (int)($siteGroupSummary['active_groups'] ?? 0); ?></div>
                        </div>
                        <span class="badge rounded-pill bg-info fs-6"><span class="bi bi-lightning"></span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap align-items-center gap-2 py-3">
            <h6 class="mb-0 text-uppercase text-muted"><?php echo $L->g('site-list-table-title'); ?></h6>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($siteGroups)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle site-group-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center col-id">ID</th>
                                <th class="col-name"><?php echo $L->g('site-list-col-name'); ?></th>
                                <th class="text-center d-none d-md-table-cell col-type">
                                    <?php echo $L->g('site-list-col-type'); ?>
                                </th>
                                <th class="d-none d-lg-table-cell col-domains">
                                    <?php echo $L->g('site-list-col-domains'); ?>
                                </th>
                                <th class="text-center d-none d-xl-table-cell col-category">
                                    <?php echo $L->g('site-list-col-category'); ?>
                                </th>
                                <th class="text-center d-none d-xl-table-cell col-count">
                                    <?php echo $L->g('site-list-col-count'); ?>
                                </th>
                                <th class="text-center d-none d-xl-table-cell" style="width: 160px;">
                                    <?php echo $L->g('site-list-col-created'); ?>
                                </th>
                                <th class="text-center d-none d-xxl-table-cell" style="width: 160px;">
                                    <?php echo $L->g('site-list-col-updated'); ?>
                                </th>
                                <th class="text-center col-actions">
                                    <?php echo $L->g('site-list-col-actions'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rowIndex = 0; foreach ($siteGroups as $group): $rowIndex++; ?>
                                <?php
                                    $typeKey = $group['type'] ?? 'single';
                                    $typeLabel = Sanitize::html($L->g('site-list-type-' . $typeKey));
                                    if ($typeLabel === 'site-list-type-' . $typeKey) {
                                        $typeLabel = Sanitize::html($typeKey);
                                    }

                                    $categoryKey = $group['category'] ?? ($group['mode'] ?? 'other');
                                    $categoryLabel = Sanitize::html($L->g('site-list-category-' . $categoryKey));
                                    if ($categoryLabel === 'site-list-category-' . $categoryKey) {
                                        $categoryLabel = Sanitize::html($categoryKey);
                                    }

                                    $domainTemplates = array();
                                    if (!empty($group['domain_templates']) && is_array($group['domain_templates'])) {
                                        $domainTemplates = $group['domain_templates'];
                                    }
                                ?>
                                <tr class="searchItem"
                                    data-group-id="<?php echo Sanitize::html($group['group_id']); ?>"
                                    data-batch-date="<?php echo Sanitize::html($group['batch_date']); ?>"
                                    data-group-name="<?php echo Sanitize::html($group['group_name'] ?? ''); ?>"
                                    data-group-type="<?php echo Sanitize::html($group['type'] ?? ''); ?>"
                                    data-group-category="<?php echo Sanitize::html($group['category'] ?? 'other'); ?>"
                                    data-group-status="<?php echo Sanitize::html($group['status'] ?? 'active'); ?>"
                                    data-group-note="<?php echo Sanitize::html($group['note'] ?? ''); ?>"
                                    data-group-domains="<?php echo Sanitize::html(json_encode($group['domains'] ?? array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>"
                                    data-group-domain-templates="<?php echo Sanitize::html(json_encode($group['domain_templates'] ?? array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>"
                                    data-group-redirect="<?php echo Sanitize::html($group['redirect_policy'] ?? 'off'); ?>"
                                    data-group-image-localization="<?php echo Sanitize::html($group['image_localization'] ?? 'off'); ?>"
                                    data-group-image-rename="<?php echo Sanitize::html($group['image_rename'] ?? 'off'); ?>"
                                    data-group-article-image-count="<?php echo Sanitize::html((string)($group['article_image_count'] ?? 1)); ?>"
                                    data-group-article-thumbnail-first="<?php echo Sanitize::html($group['article_thumbnail_first'] ?? 'off'); ?>"
                                >
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill"><?php echo (int)$rowIndex; ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold searchText"><?php echo Sanitize::html($group['group_name'] ?? ''); ?></div>
                                        <?php if (!empty($group['note'])): ?>
                                            <div class="text-muted small searchText"><?php echo Sanitize::html($group['note']); ?></div>
                                        <?php endif; ?>
                                        <div class="d-md-none mt-2 d-flex flex-wrap gap-1">
                                            <span class="badge bg-secondary-subtle text-dark border"><?php echo $typeLabel; ?></span>
                                            <span class="badge bg-light text-dark border"><?php echo $categoryLabel; ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center d-none d-md-table-cell">
                                        <span class="badge bg-secondary-subtle text-dark border"><?php echo $typeLabel; ?></span>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <?php
                                            $domains = $group['domains'] ?? array();
                                            $displayDomains = array_slice($domains, 0, 2);
                                            $remainingDomains = max(count($domains) - count($displayDomains), 0);
                                        ?>
                                        <?php if (!empty($displayDomains)): ?>
                                            <div class="domains-list">
                                                <?php foreach ($displayDomains as $domain): ?>
                                                    <?php
                                                        $domainLabel = Sanitize::html($domain);
                                                        $templateSlug = '';
                                                        if (!empty($domainTemplates) && isset($domainTemplates[$domain])) {
                                                            $templateSlug = $domainTemplates[$domain];
                                                        }
                                                    ?>
                                                    <div class="small searchText domain-item" title="<?php echo $domainLabel; ?>">
                                                        <?php echo $domainLabel; ?>
                                                        <?php if ($templateSlug !== ''): ?>
                                                            <span class="text-muted ms-1"><?php echo Sanitize::html($L->g('site-list-domain-template-label')); ?>: <?php echo Sanitize::html($templateSlug); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if ($remainingDomains > 0): ?>
                                                    <div class="text-muted small fw-semibold">
                                                        <?php echo sprintf($L->g('site-list-domains-more'), $remainingDomains); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center d-none d-xl-table-cell">
                                        <span class="badge bg-light text-dark border"><?php echo $categoryLabel; ?></span>
                                    </td>
                                    <td class="text-center d-none d-xl-table-cell">
                                        <span class="badge bg-secondary"><?php echo (int)($group['site_count'] ?? 0); ?></span>
                                    </td>
                                    <td class="text-center d-none d-xl-table-cell">
                                        <small><?php echo Sanitize::html($group['created_at'] ?? ''); ?></small>
                                    </td>
                                    <td class="text-center d-none d-xxl-table-cell">
                                        <small><?php echo Sanitize::html($group['updated_at'] ?? ''); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-edit-group"
                                                data-id="<?php echo Sanitize::html($group['group_id']); ?>"
                                                data-date="<?php echo Sanitize::html($group['batch_date']); ?>"
                                                title="<?php echo $L->g('site-list-action-edit'); ?>">
                                                <span class="bi bi-pencil"></span>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-delete-group"
                                                data-id="<?php echo Sanitize::html($group['group_id']); ?>"
                                                data-date="<?php echo Sanitize::html($group['batch_date']); ?>"
                                                data-name="<?php echo Sanitize::html($group['group_name'] ?? ''); ?>"
                                                title="<?php echo $L->g('site-list-action-delete'); ?>">
                                                <span class="bi bi-trash"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <span class="bi bi-inboxes fs-1 d-block mb-3"></span>
                    <p class="mb-1 fw-semibold"><?php echo $L->g('site-list-empty-title'); ?></p>
                    <p class="small mb-0"><?php echo $L->g('site-list-empty-desc'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="groupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupModalTitle"
                    data-title-add="<?php echo $L->g('site-list-modal-add-title'); ?>"
                    data-title-edit="<?php echo $L->g('site-list-modal-edit-title'); ?>"
                >
                    <?php echo $L->g('site-list-modal-add-title'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="groupForm" data-no-spa="true">
                <div class="modal-body">
                    <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="group_id" id="groupId" value="">
                    <input type="hidden" name="batch_date" id="groupDate" value="<?php echo date('Y-m-d'); ?>" data-default="<?php echo date('Y-m-d'); ?>">

                    <div class="step-indicator d-flex align-items-center gap-3 mb-4" data-step-indicator-wrapper>
                        <div class="d-flex align-items-center gap-2 step-item active" data-step-indicator="1">
                            <span class="badge rounded-pill bg-primary step-number">1</span>
                            <span class="fw-semibold step-label"><?php echo Sanitize::html($L->g('site-list-step-basic')); ?></span>
                        </div>
                        <div class="flex-grow-1 border-top opacity-50"></div>
                        <div class="d-flex align-items-center gap-2 step-item text-muted" data-step-indicator="2">
                            <span class="badge rounded-pill bg-light text-dark border step-number">2</span>
                            <span class="fw-semibold step-label"><?php echo Sanitize::html($L->g('site-list-step-advanced')); ?></span>
                        </div>
                    </div>

                    <div class="step-pane" data-step="1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="groupName" class="form-label"><?php echo $L->g('site-list-form-name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="groupName" name="group_name" data-autofocus="true" minlength="1" maxlength="10" required>
                                <div class="form-text"><?php echo $L->g('site-list-form-name-hint'); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $L->g('site-list-form-type'); ?> <span class="text-danger">*</span></label>
                                <input type="hidden" id="groupType" name="type" value="single">
                                <div class="choice-card-group" role="group" aria-label="<?php echo $L->g('site-list-form-type'); ?>">
                                    <button type="button" class="btn btn-outline-primary choice-card active" data-choice-input="groupType" data-choice-value="single" data-choice-variant="primary">
                                        <span class="choice-icon"><span class="bi bi-person-workspace"></span></span>
                                        <span class="choice-label"><?php echo $L->g('site-list-type-single'); ?></span>
                                        <span class="choice-state text-primary"><span class="bi bi-check-circle-fill"></span></span>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary choice-card" data-choice-input="groupType" data-choice-value="multi" data-choice-variant="primary">
                                        <span class="choice-icon"><span class="bi bi-diagram-3"></span></span>
                                        <span class="choice-label"><?php echo $L->g('site-list-type-multi'); ?></span>
                                        <span class="choice-state text-primary"><span class="bi bi-check-circle-fill"></span></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="groupCategory" class="form-label"><?php echo $L->g('site-list-form-category'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="groupCategory" name="category" required>
                                    <option value="enterprise"><?php echo $L->g('site-list-category-enterprise'); ?></option>
                                    <option value="blog"><?php echo $L->g('site-list-category-blog'); ?></option>
                                    <option value="news"><?php echo $L->g('site-list-category-news'); ?></option>
                                    <option value="commerce"><?php echo $L->g('site-list-category-commerce'); ?></option>
                                    <option value="community"><?php echo $L->g('site-list-category-community'); ?></option>
                                    <option value="directory"><?php echo $L->g('site-list-category-directory'); ?></option>
                                    <option value="forum"><?php echo $L->g('site-list-category-forum'); ?></option>
                                    <option value="tools"><?php echo $L->g('site-list-category-tools'); ?></option>
                                    <option value="video"><?php echo $L->g('site-list-category-video'); ?></option>
                                    <option value="image"><?php echo $L->g('site-list-category-image'); ?></option>
                                    <option value="download"><?php echo $L->g('site-list-category-download'); ?></option>
                                    <option value="document"><?php echo $L->g('site-list-category-document'); ?></option>
                                    <option value="qa"><?php echo $L->g('site-list-category-qa'); ?></option>
                                    <option value="other"><?php echo $L->g('site-list-category-other'); ?></option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="groupDomains" class="form-label"><?php echo $L->g('site-list-form-domains'); ?> <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="groupDomains" name="domains" style="width: 740px; max-width: 100%; height: 500px;" placeholder="example.com&#10;example.net-alternative" required></textarea>
                                <div class="form-text"><?php echo $L->g('site-list-form-domains-hint'); ?></div>
                                <div class="form-text"><?php echo $L->g('site-list-form-domains-hint-template'); ?></div>
                            </div>
                            <div class="col-12">
                                <label for="groupNote" class="form-label"><?php echo $L->g('site-list-form-note'); ?></label>
                                <textarea class="form-control" id="groupNote" name="note" rows="3" maxlength="50"></textarea>
                                <div class="form-text"><?php echo $L->g('site-list-form-note-hint'); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="step-pane d-none" data-step="2">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $L->g('site-list-form-redirect'); ?></label>
                                <input type="hidden" id="redirectPolicy" name="redirect_policy" value="off">
                                <div class="choice-card-group choice-card-stack" role="group" aria-label="<?php echo $L->g('site-list-form-redirect'); ?>">
                                    <button type="button" class="btn btn-outline-secondary choice-card" data-choice-input="redirectPolicy" data-choice-value="at_to_www" data-choice-variant="secondary">
                                        <span class="choice-icon"><span class="bi bi-at"></span></span>
                                        <span class="choice-label"><?php echo $L->g('site-list-redirect-at-to-www'); ?></span>
                                        <span class="choice-state text-success"><span class="bi bi-check-circle-fill"></span></span>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary choice-card" data-choice-input="redirectPolicy" data-choice-value="force_www" data-choice-variant="secondary">
                                        <span class="choice-icon"><span class="bi bi-arrow-repeat"></span></span>
                                        <span class="choice-label"><?php echo $L->g('site-list-redirect-force-www'); ?></span>
                                        <span class="choice-state text-success"><span class="bi bi-check-circle-fill"></span></span>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary choice-card active" data-choice-input="redirectPolicy" data-choice-value="off" data-choice-variant="secondary">
                                        <span class="choice-icon"><span class="bi bi-slash-circle"></span></span>
                                        <span class="choice-label"><?php echo $L->g('site-list-redirect-off'); ?></span>
                                        <span class="choice-state text-danger"><span class="bi bi-x-circle-fill"></span></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $L->g('site-list-form-image-localization'); ?></label>
                                <input type="hidden" id="imageLocalization" name="image_localization" value="off">
                                <div class="choice-card-group choice-card-stack" role="group" aria-label="<?php echo $L->g('site-list-form-image-localization'); ?>">
                                    <button type="button" class="btn btn-outline-success choice-card" data-choice-input="imageLocalization" data-choice-value="on" data-choice-variant="success">
                                        <span class="choice-icon"><span class="bi bi-image"></span></span>
                                        <span class="choice-label"><?php echo $L->g('site-list-option-on'); ?></span>
                                        <span class="choice-state text-success"><span class="bi bi-check-circle-fill"></span></span>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary choice-card active" data-choice-input="imageLocalization" data-choice-value="off" data-choice-variant="secondary">
                                        <span class="choice-icon"><span class="bi bi-image-alt"></span></span>
                                        <span class="choice-label"><?php echo $L->g('site-list-option-off'); ?></span>
                                        <span class="choice-state text-muted"><span class="bi bi-dash-circle"></span></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $L->g('site-list-form-image-rename'); ?></label>
                                <input type="hidden" id="imageRename" name="image_rename" value="off">
                                <div class="choice-card-group choice-card-stack" role="group" aria-label="<?php echo $L->g('site-list-form-image-rename'); ?>">
                                    <button type="button" class="btn btn-outline-success choice-card" data-choice-input="imageRename" data-choice-value="on" data-choice-variant="success">
                                        <span class="choice-icon"><span class="bi bi-type"></span></span>
                                        <span class="choice-label"><?php echo $L->g('site-list-option-on'); ?></span>
                                        <span class="choice-state text-success"><span class="bi bi-check-circle-fill"></span></span>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary choice-card active" data-choice-input="imageRename" data-choice-value="off" data-choice-variant="secondary">
                                        <span class="choice-icon"><span class="bi bi-card-text"></span></span>
                                        <span class="choice-label"><?php echo $L->g('site-list-option-off'); ?></span>
                                        <span class="choice-state text-muted"><span class="bi bi-dash-circle"></span></span>
                                    </button>
                                </div>
                                <div class="form-text"><?php echo $L->g('site-list-form-image-rename-hint'); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label for="articleImageCount" class="form-label"><?php echo $L->g('site-list-form-article-image-count'); ?></label>
                                <input type="number" class="form-control" id="articleImageCount" name="article_image_count" min="1" max="100" value="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $L->g('site-list-form-article-thumbnail-first'); ?></label>
                                <input type="hidden" id="articleThumbnailFirst" name="article_thumbnail_first" value="on">
                                <div class="choice-card-group choice-card-stack" role="group" aria-label="<?php echo $L->g('site-list-form-article-thumbnail-first'); ?>">
                                    <button type="button" class="btn btn-outline-success choice-card active" data-choice-input="articleThumbnailFirst" data-choice-value="on" data-choice-variant="success">
                                        <span class="choice-icon"><span class="bi bi-image-fill"></span></span>
                                        <span class="choice-label"><?php echo $L->g('site-list-option-on'); ?></span>
                                        <span class="choice-state text-success"><span class="bi bi-check-circle-fill"></span></span>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary choice-card" data-choice-input="articleThumbnailFirst" data-choice-value="off" data-choice-variant="secondary">
                                        <span class="choice-icon"><span class="bi bi-image"></span></span>
                                        <span class="choice-label"><?php echo $L->g('site-list-option-off'); ?></span>
                                        <span class="choice-state text-muted"><span class="bi bi-dash-circle"></span></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6" id="statusField" style="display: none;">
                                <label for="groupStatus" class="form-label"><?php echo $L->g('site-list-form-status'); ?></label>
                                <select class="form-select" id="groupStatus" name="status">
                                    <option value="active"><?php echo $L->g('site-list-status-active'); ?></option>
                                    <option value="inactive"><?php echo $L->g('site-list-status-inactive'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L->g('site-list-form-cancel'); ?></button>
                    <button type="button" class="btn btn-outline-primary btn-prev-step d-none"><?php echo $L->g('site-list-form-prev'); ?></button>
                    <button type="button" class="btn btn-primary btn-next-step"><?php echo $L->g('site-list-form-next'); ?></button>
                    <button type="submit" class="btn btn-primary btn-save-group d-none">
                        <span class="spinner-border spinner-border-sm align-middle me-2 d-none" role="status" aria-hidden="true"></span>
                        <span class="label-text"><?php echo $L->g('site-list-form-save'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><?php echo $L->g('site-list-delete-title'); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="deleteForm" data-no-spa="true">
                <div class="modal-body">
                    <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="group_id" id="deleteGroupId" value="">
                    <input type="hidden" name="batch_date" id="deleteGroupDate" value="">
                    <p class="mb-2"><?php echo $L->g('site-list-delete-confirm'); ?></p>
                    <p class="fw-semibold" id="deleteGroupName"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L->g('site-list-form-cancel'); ?></button>
                    <button type="submit" class="btn btn-danger btn-delete-confirm">
                        <span class="spinner-border spinner-border-sm align-middle me-2 d-none" role="status" aria-hidden="true"></span>
                        <span class="label-text"><?php echo $L->g('site-list-delete-confirm-button'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
