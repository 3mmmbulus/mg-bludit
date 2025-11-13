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
    width: 220px;
}

.site-groups-page .site-group-table .col-type {
    width: 140px;
}

.site-groups-page .site-group-table .col-domains {
    width: 260px;
}

.site-groups-page .site-group-table .col-mode {
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
            <?php if (!empty($siteGroupDates)): ?>
                <span class="badge bg-light text-dark ms-auto">
                    <span class="bi bi-calendar-week"></span>
                    <span><?php echo $L->g('site-list-available-batches'); ?>:</span>
                    <?php echo implode(', ', $siteGroupDates); ?>
                </span>
            <?php endif; ?>
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
                                <th class="text-center d-none d-xl-table-cell col-mode">
                                    <?php echo $L->g('site-list-col-mode'); ?>
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
                            <?php foreach ($siteGroups as $group): ?>
                                <tr class="searchItem"
                                    data-group-id="<?php echo Sanitize::html($group['group_id']); ?>"
                                    data-batch-date="<?php echo Sanitize::html($group['batch_date']); ?>"
                                    data-group-name="<?php echo Sanitize::html($group['group_name'] ?? ''); ?>"
                                    data-group-type="<?php echo Sanitize::html($group['type'] ?? ''); ?>"
                                    data-group-mode="<?php echo Sanitize::html($group['mode'] ?? 'independent'); ?>"
                                    data-group-status="<?php echo Sanitize::html($group['status'] ?? 'active'); ?>"
                                    data-group-note="<?php echo Sanitize::html($group['note'] ?? ''); ?>"
                                    data-group-domains="<?php echo Sanitize::html(json_encode($group['domains'] ?? array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>"
                                >
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill"><?php echo Sanitize::html($group['group_id']); ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold searchText"><?php echo Sanitize::html($group['group_name'] ?? ''); ?></div>
                                        <?php if (!empty($group['note'])): ?>
                                            <div class="text-muted small searchText"><?php echo Sanitize::html($group['note']); ?></div>
                                        <?php endif; ?>
                                        <div class="d-md-none mt-2 d-flex flex-wrap gap-1">
                                            <span class="badge bg-secondary-subtle text-dark border"><?php echo Sanitize::html($group['type'] ?? ''); ?></span>
                                            <span class="badge bg-light text-dark border"><?php echo $L->g('site-list-mode-' . ($group['mode'] ?? 'independent')); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center d-none d-md-table-cell">
                                        <span class="badge bg-secondary-subtle text-dark border"><?php echo Sanitize::html($group['type'] ?? ''); ?></span>
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
                                                    <div class="small searchText domain-item" title="<?php echo Sanitize::html($domain); ?>"><?php echo Sanitize::html($domain); ?></div>
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
                                        <span class="badge bg-light text-dark border"><?php echo $L->g('site-list-mode-' . ($group['mode'] ?? 'independent')); ?></span>
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
                    <input type="hidden" name="batch_date" id="groupDate" value="<?php echo date('Y-m-d'); ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="groupName" class="form-label"><?php echo $L->g('site-list-form-name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="groupName" name="group_name" data-autofocus="true" required>
                        </div>
                        <div class="col-md-6">
                            <label for="groupType" class="form-label"><?php echo $L->g('site-list-form-type'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="groupType" name="type" required>
                        </div>
                        <div class="col-md-6">
                            <label for="groupMode" class="form-label"><?php echo $L->g('site-list-form-mode'); ?></label>
                            <select class="form-select" id="groupMode" name="mode">
                                <option value="independent"><?php echo $L->g('site-list-mode-independent'); ?></option>
                                <option value="shared"><?php echo $L->g('site-list-mode-shared'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-6" id="statusField" style="display: none;">
                            <label for="groupStatus" class="form-label"><?php echo $L->g('site-list-form-status'); ?></label>
                            <select class="form-select" id="groupStatus" name="status">
                                <option value="active"><?php echo $L->g('site-list-status-active'); ?></option>
                                <option value="inactive"><?php echo $L->g('site-list-status-inactive'); ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="groupDomains" class="form-label"><?php echo $L->g('site-list-form-domains'); ?></label>
                            <textarea class="form-control" id="groupDomains" name="domains" rows="4" placeholder="example.com&#10;www.example.com"></textarea>
                            <div class="form-text"><?php echo $L->g('site-list-form-domains-hint'); ?></div>
                        </div>
                        <div class="col-12">
                            <label for="groupNote" class="form-label"><?php echo $L->g('site-list-form-note'); ?></label>
                            <textarea class="form-control" id="groupNote" name="note" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L->g('site-list-form-cancel'); ?></button>
                    <button type="submit" class="btn btn-primary btn-save-group">
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
