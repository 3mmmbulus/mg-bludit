(function () {
    'use strict';

    var root = document.querySelector('[data-comments-root]');
    if (!root) {
        return;
    }

    var dataset = parseJSON(root.getAttribute('data-dataset')) || [];
    var statusOptions = parseJSON(root.getAttribute('data-status-options')) || [];
    var categories = parseJSON(root.getAttribute('data-categories')) || [];
    var sources = parseJSON(root.getAttribute('data-sources')) || [];
    var translations = parseJSON(root.getAttribute('data-translations')) || {};
    var endpoint = root.getAttribute('data-endpoint');
    var token = root.getAttribute('data-token');
    var maxUpload = Number(root.getAttribute('data-max-upload') || 100) || 100;

    var tableBody = root.querySelector('[data-table-body]');
    var pagination = root.querySelector('[data-pagination]');
    var paginationSummary = root.querySelector('[data-pagination-summary]');
    var tableSummary = root.querySelector('[data-table-summary]');
    var selectAll = root.querySelector('[data-select-all]');
    var bulkDeleteButton = root.querySelector('[data-comments-bulk-delete]');
    var bulkDeleteLabel = bulkDeleteButton ? bulkDeleteButton.querySelector('[data-bulk-delete-label]') : null;
    var industrySummaryList = root.querySelector('[data-industry-summary]');
    var metricTotal = root.querySelector('[data-metric-total]');
    var metricActive = root.querySelector('[data-metric-active]');
    var metricContent = root.querySelector('[data-metric-content]');
    var metricNickname = root.querySelector('[data-metric-nickname]');
    var filterTypeGroup = root.querySelector('[data-comments-type-switch]');
    var filterCategory = root.querySelector('[data-filter-category]');
    var filterStatus = root.querySelector('[data-filter-status]');
    var filterSource = root.querySelector('[data-filter-source]');
    var filterSearch = root.querySelector('[data-filter-search]');
    var filterReset = root.querySelector('[data-filter-reset]');
    var tableSizeSelect = root.querySelector('[data-table-size]');
    var uploadTrigger = root.querySelector('[data-comments-upload-trigger]');
    var uploadModalElement = document.getElementById('commentsUploadModal');
    var uploadForm = document.getElementById('commentsUploadForm');
    var uploadSubmit = uploadForm ? uploadForm.querySelector('[data-upload-submit]') : null;
    var uploadTypeField = uploadForm ? uploadForm.querySelector('[data-upload-type-field]') : null;
    var uploadRadios = uploadForm ? uploadForm.querySelectorAll('[data-upload-type]') : [];
    var uploadFileInput = uploadForm ? uploadForm.querySelector('#commentsUploadFiles') : null;
    var detailModalElement = document.getElementById('commentsDetailModal');

    var uploadModal = uploadModalElement ? new bootstrap.Modal(uploadModalElement) : null;
    var detailModal = detailModalElement ? new bootstrap.Modal(detailModalElement) : null;

    var state = {
        records: dataset.slice(),
        filtered: [],
        currentType: 'all',
        currentCategory: 'all',
        currentStatus: 'all',
        currentSource: 'all',
        search: '',
        pageSize: Number(tableSizeSelect ? tableSizeSelect.value : 25) || 25,
        currentPage: 1,
        selections: new Map()
    };

    renderAll();
    bindEvents();

    function bindEvents() {
        if (filterTypeGroup) {
            filterTypeGroup.addEventListener('click', function (event) {
                var button = event.target.closest('[data-type]');
                if (!button) {
                    return;
                }
                var type = button.getAttribute('data-type');
                setActiveTypeButton(type);
                state.currentType = type || 'all';
                state.currentPage = 1;
                renderTable();
            });
        }

        if (filterCategory) {
            filterCategory.addEventListener('change', function () {
                state.currentCategory = filterCategory.value || 'all';
                state.currentPage = 1;
                renderTable();
            });
        }

        if (filterStatus) {
            filterStatus.addEventListener('change', function () {
                state.currentStatus = filterStatus.value || 'all';
                state.currentPage = 1;
                renderTable();
            });
        }

        if (filterSource) {
            filterSource.addEventListener('change', function () {
                state.currentSource = filterSource.value || 'all';
                state.currentPage = 1;
                renderTable();
            });
        }

        if (filterSearch) {
            var searchDebounce;
            filterSearch.addEventListener('input', function () {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(function () {
                    state.search = filterSearch.value || '';
                    state.currentPage = 1;
                    renderTable();
                }, 200);
            });
        }

        if (filterReset) {
            filterReset.addEventListener('click', function () {
                state.currentType = 'all';
                state.currentCategory = 'all';
                state.currentStatus = 'all';
                state.currentSource = 'all';
                state.search = '';
                state.currentPage = 1;
                if (filterCategory) filterCategory.value = 'all';
                if (filterStatus) filterStatus.value = 'all';
                if (filterSource) filterSource.value = 'all';
                if (filterSearch) filterSearch.value = '';
                setActiveTypeButton('all');
                renderTable();
            });
        }

        if (tableSizeSelect) {
            tableSizeSelect.addEventListener('change', function () {
                state.pageSize = Number(tableSizeSelect.value) || 25;
                state.currentPage = 1;
                renderTable();
            });
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                var checked = selectAll.checked;
                state.selections.clear();
                if (checked) {
                    state.filtered.forEach(function (record) {
                        state.selections.set(record.id, { id: record.id, type: record.type });
                    });
                }
                renderTable();
                updateBulkDeleteState();
            });
        }

        if (tableBody) {
            tableBody.addEventListener('click', function (event) {
                var target = event.target;
                if (target.matches('[data-action="detail"]') || target.closest('[data-action="detail"]')) {
                    var button = target.closest('[data-action="detail"]');
                    openDetail(button.getAttribute('data-id'), button.getAttribute('data-type'));
                    event.preventDefault();
                    return;
                }
                if (target.matches('[data-action="delete"]') || target.closest('[data-action="delete"]')) {
                    var btn = target.closest('[data-action="delete"]');
                    deleteSingle(btn.getAttribute('data-id'), btn.getAttribute('data-type'));
                    event.preventDefault();
                    return;
                }
                if (target.matches('[data-status-option]')) {
                    var status = target.getAttribute('data-status-option');
                    var recordId = target.getAttribute('data-id');
                    var recordType = target.getAttribute('data-type');
                    updateStatus(recordId, recordType, status);
                    event.preventDefault();
                    return;
                }
            });

            tableBody.addEventListener('change', function (event) {
                if (event.target && event.target.matches('[data-record-select]')) {
                    var checkbox = event.target;
                    var id = checkbox.getAttribute('data-id');
                    var type = checkbox.getAttribute('data-type');
                    if (checkbox.checked) {
                        state.selections.set(id, { id: id, type: type });
                    } else {
                        state.selections.delete(id);
                    }
                    updateBulkDeleteState();
                }
            });
        }

        if (pagination) {
            pagination.addEventListener('click', function (event) {
                var link = event.target.closest('[data-page]');
                if (!link) {
                    return;
                }
                event.preventDefault();
                var page = Number(link.getAttribute('data-page'));
                if (!page || page === state.currentPage) {
                    return;
                }
                state.currentPage = page;
                renderTable();
            });
        }

        if (bulkDeleteButton) {
            bulkDeleteButton.addEventListener('click', function () {
                if (state.selections.size === 0) {
                    return;
                }
                var message = translations.confirmDeleteBulk || 'Delete selected files?';
                if (!window.confirm(message)) {
                    return;
                }
                bulkDelete();
            });
        }

        if (uploadTrigger && uploadModal) {
            uploadTrigger.addEventListener('click', function () {
                resetUploadForm();
                uploadModal.show();
            });
        }

        if (uploadRadios && uploadRadios.length) {
            uploadRadios.forEach(function (radio) {
                radio.addEventListener('change', handleUploadModeChange);
            });
        }

        if (uploadFileInput) {
            uploadFileInput.addEventListener('change', function () {
                validateUploadForm();
            });
        }

        if (uploadForm) {
            uploadForm.addEventListener('submit', function (event) {
                event.preventDefault();
                submitUploadForm();
            });
        }
    }

    function renderAll() {
        computeFilters();
        setActiveTypeButton(state.currentType);
        renderTable();
        updateIndustrySummary();
        updateMetrics();
        updateBulkDeleteState();
    }

    function computeFilters() {
        // ensure filter options include latest sources
        if (filterSource) {
            var current = filterSource.value;
            filterSource.innerHTML = '';
            addOption(filterSource, 'all', translations.filterAllSources || 'All sources');
            var uniqueSources = getUnique(state.records, 'sourceFile');
            uniqueSources.forEach(function (source) {
                addOption(filterSource, source, source);
            });
            if (current && uniqueSources.indexOf(current) >= 0) {
                filterSource.value = current;
            } else {
                filterSource.value = 'all';
                state.currentSource = 'all';
            }
        }
    }

    function renderTable() {
        state.filtered = state.records.filter(function (record) {
            if (state.currentType !== 'all' && record.type !== state.currentType) {
                return false;
            }
            if (state.currentCategory !== 'all' && record.category !== state.currentCategory) {
                return false;
            }
            if (state.currentStatus !== 'all' && record.status !== state.currentStatus) {
                return false;
            }
            if (state.currentSource !== 'all' && record.sourceFile !== state.currentSource) {
                return false;
            }
            if (state.search) {
                var keyword = state.search.toLowerCase();
                var haystack = [record.sourceFile, record.originalName, record.categoryLabel].join(' ').toLowerCase();
                if (haystack.indexOf(keyword) === -1) {
                    return false;
                }
            }
            return true;
        });

        var total = state.filtered.length;
        var totalPages = Math.max(1, Math.ceil(total / state.pageSize));
        if (state.currentPage > totalPages) {
            state.currentPage = totalPages;
        }
        var start = (state.currentPage - 1) * state.pageSize;
        var end = start + state.pageSize;
        var pageItems = state.filtered.slice(start, end);

        if (tableSummary) {
            if (total > 0) {
                tableSummary.textContent = formatTableSummary(start + 1, Math.min(end, total), total);
            } else {
                tableSummary.textContent = translations.tableEmpty || 'No records found.';
            }
        }

        if (tableBody) {
            if (pageItems.length === 0) {
                tableBody.innerHTML = '<tr class="table-empty"><td colspan="9" class="text-center py-5 text-muted"><span class="bi bi-inboxes fs-1 d-block mb-2"></span>' + escapeHtml(translations.tableEmpty || 'No records found.') + '</td></tr>';
            } else {
                var rows = pageItems.map(renderRow).join('');
                tableBody.innerHTML = rows;
                tableBody.querySelectorAll('[data-record-select]').forEach(function (checkbox) {
                    var id = checkbox.getAttribute('data-id');
                    if (state.selections.has(id)) {
                        checkbox.checked = true;
                    }
                });
            }
        }

        if (pagination) {
            pagination.innerHTML = buildPagination(state.currentPage, totalPages);
        }

        if (paginationSummary) {
            if (total > 0) {
                paginationSummary.textContent = formatTableSummary(start + 1, Math.min(end, total), total);
            } else {
                paginationSummary.textContent = translations.tableEmpty || 'No records found.';
            }
        }

        if (selectAll) {
            selectAll.checked = state.filtered.length > 0 && state.filtered.every(function (record) {
                return state.selections.has(record.id);
            });
        }

        updateMetrics();
        updateIndustrySummary();
        updateBulkDeleteState();
    }

    function renderRow(record) {
        var statusLabelKey = 'status' + capitalize(record.status);
        var statusLabel = translations['status' + capitalize(record.status)] || record.status;
        var statusClass = getStatusBadgeClass(record.status);
        var typeLabel = record.type === 'content' ? (translations.tableTypeContent || 'Content') : (translations.tableTypeNickname || 'Nickname');
        var usageBadgeClass = record.usageCount > 0 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary';

        return '<tr data-record-row data-id="' + escapeHtml(record.id) + '">' +
            '<td class="text-center"><input type="checkbox" class="form-check-input" data-record-select data-id="' + escapeHtml(record.id) + '" data-type="' + escapeHtml(record.type) + '"></td>' +
            '<td><div class="fw-semibold">' + escapeHtml(typeLabel) + '</div><div class="small text-muted text-truncate">' + escapeHtml(record.originalName) + '</div></td>' +
            '<td><div class="fw-semibold text-truncate" title="' + escapeHtml(record.sourceFile) + '">' + escapeHtml(record.sourceFile) + '</div><div class="small text-muted">' + escapeHtml(record.storedName) + '</div></td>' +
            '<td class="text-center"><span class="badge bg-primary-subtle text-primary text-truncate" title="' + escapeHtml(record.categoryLabel) + '">' + escapeHtml(record.categoryLabel) + '</span></td>' +
            '<td class="text-center"><span class="badge bg-light text-dark border">' + record.lineCount + '</span></td>' +
            '<td class="text-center"><span class="badge ' + usageBadgeClass + '">' + record.usageCount + '</span></td>' +
            '<td class="text-center"><span class="small text-muted">' + escapeHtml(record.importedAt || record.uploadedAt) + '</span></td>' +
            '<td class="text-center"><span class="badge ' + statusClass + '">' + escapeHtml(statusLabel) + '</span></td>' +
            '<td class="text-end">' +
                '<div class="btn-group btn-group-sm" role="group">' +
                    '<button type="button" class="btn btn-outline-primary" data-action="detail" data-id="' + escapeHtml(record.id) + '" data-type="' + escapeHtml(record.type) + '"><span class="bi bi-eye"></span></button>' +
                    '<button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false"><span class="visually-hidden">Toggle Dropdown</span></button>' +
                    '<ul class="dropdown-menu dropdown-menu-end">' + buildStatusMenu(record) + '</ul>' +
                    '<button type="button" class="btn btn-outline-danger" data-action="delete" data-id="' + escapeHtml(record.id) + '" data-type="' + escapeHtml(record.type) + '"><span class="bi bi-trash"></span></button>' +
                '</div>' +
            '</td>' +
        '</tr>';
    }

    function buildStatusMenu(record) {
        return statusOptions.map(function (status) {
            var label = translations['statusMenu' + capitalize(status)] || status;
            return '<li><button class="dropdown-item" type="button" data-status-option="' + escapeHtml(status) + '" data-id="' + escapeHtml(record.id) + '" data-type="' + escapeHtml(record.type) + '">' + escapeHtml(label) + '</button></li>';
        }).join('');
    }

    function buildPagination(current, total) {
        if (total <= 1) {
            return '';
        }
        var items = [];
        var prev = Math.max(1, current - 1);
        var next = Math.min(total, current + 1);
        items.push('<li class="page-item' + (current === 1 ? ' disabled' : '') + '"><a class="page-link" href="#" data-page="' + prev + '">&laquo;</a></li>');
        var start = Math.max(1, current - 2);
        var end = Math.min(total, current + 2);
        if (start > 1) {
            items.push('<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>');
            if (start > 2) {
                items.push('<li class="page-item disabled"><span class="page-link">&hellip;</span></li>');
            }
        }
        for (var page = start; page <= end; page++) {
            items.push('<li class="page-item' + (page === current ? ' active' : '') + '"><a class="page-link" href="#" data-page="' + page + '">' + page + '</a></li>');
        }
        if (end < total) {
            if (end < total - 1) {
                items.push('<li class="page-item disabled"><span class="page-link">&hellip;</span></li>');
            }
            items.push('<li class="page-item"><a class="page-link" href="#" data-page="' + total + '">' + total + '</a></li>');
        }
        items.push('<li class="page-item' + (current === total ? ' disabled' : '') + '"><a class="page-link" href="#" data-page="' + next + '">&raquo;</a></li>');
        return items.join('');
    }

    function updateIndustrySummary() {
        if (!industrySummaryList) {
            return;
        }
        var summary = {};
        state.records.forEach(function (record) {
            summary[record.category] = (summary[record.category] || 0) + 1;
        });
        var rows = Object.keys(summary).sort(function (a, b) {
            return summary[b] - summary[a];
        }).map(function (category) {
            return '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                '<span class="text-truncate" title="' + escapeHtml(recordCategoryLabel(category)) + '">' + escapeHtml(recordCategoryLabel(category)) + '</span>' +
                '<span class="badge bg-primary-subtle text-primary">' + summary[category] + '</span>' +
            '</li>';
        }).join('');
        if (!rows) {
            rows = '<li class="list-group-item text-muted small">' + escapeHtml(translations.industryEmpty || 'No industry data yet.') + '</li>';
        }
        industrySummaryList.innerHTML = rows;
    }

    function updateMetrics() {
        var totals = {
            total: state.records.length,
            active: state.records.filter(function (record) { return record.status === 'active'; }).length,
            content: state.records.filter(function (record) { return record.type === 'content'; }).length,
            nickname: state.records.filter(function (record) { return record.type === 'nickname'; }).length
        };
        if (metricTotal) metricTotal.textContent = totals.total;
        if (metricActive) metricActive.textContent = totals.active;
        if (metricContent) metricContent.textContent = totals.content;
        if (metricNickname) metricNickname.textContent = totals.nickname;

        var contentBadge = root.querySelector('[data-type-count="content"]');
        if (contentBadge) {
            contentBadge.textContent = totals.content;
        }
        var nicknameBadge = root.querySelector('[data-type-count="nickname"]');
        if (nicknameBadge) {
            nicknameBadge.textContent = totals.nickname;
        }
    }

    function updateBulkDeleteState() {
        var count = state.selections.size;
        if (!bulkDeleteButton) {
            return;
        }
        bulkDeleteButton.disabled = count === 0;
        if (bulkDeleteLabel) {
            if (count === 0) {
                bulkDeleteLabel.textContent = translations.bulkDeleteNone || 'Select files to delete';
            } else {
                var label = translations.bulkDeleteLabel || '%d files selected';
                bulkDeleteLabel.textContent = label.replace('%d', count);
            }
        }
    }

    function setRecords(records) {
        state.records = records.slice();
        // remove selections that no longer exist
        state.selections.forEach(function (value, key) {
            if (!state.records.some(function (record) { return record.id === key; })) {
                state.selections.delete(key);
            }
        });
        renderAll();
    }

    function deleteSingle(id, type) {
        var message = translations.confirmDeleteSingle || 'Delete this file?';
        if (!window.confirm(message)) {
            return;
        }
        sendRequest('delete', {
            id: id,
            type: type,
            tokenCSRF: token
        }).then(function (response) {
            if (response && response.data && Array.isArray(response.data.records)) {
                setRecords(response.data.records);
            }
            showToast(response.message || 'Deleted successfully.');
        }).catch(handleError);
    }

    function bulkDelete() {
        var items = [];
        state.selections.forEach(function (value) {
            items.push(value);
        });
        sendRequest('bulk-delete', {
            items: items,
            tokenCSRF: token
        }).then(function (response) {
            state.selections.clear();
            if (response && response.data && Array.isArray(response.data.records)) {
                setRecords(response.data.records);
            }
            showToast(response.message || 'Deleted.');
        }).catch(handleError);
    }

    function updateStatus(id, type, status) {
        sendRequest('update-status', {
            id: id,
            type: type,
            status: status,
            tokenCSRF: token
        }).then(function (response) {
            if (response && response.data && Array.isArray(response.data.records)) {
                setRecords(response.data.records);
            }
            showToast(response.message || 'Status updated.');
        }).catch(handleError);
    }

    function openDetail(id, type) {
        if (!detailModalElement) {
            return;
        }
        detailModalElement.querySelector('[data-detail-title]').textContent = translations.detailTitle || 'File detail';
        detailModalElement.querySelector('[data-detail-type]').textContent = '—';
        detailModalElement.querySelector('[data-detail-source]').textContent = '';
        detailModalElement.querySelector('[data-detail-industry]').textContent = '—';
        detailModalElement.querySelector('[data-detail-imported]').textContent = '—';
        detailModalElement.querySelector('[data-detail-usage]').textContent = '0';
        detailModalElement.querySelector('[data-detail-status]').textContent = '';
        detailModalElement.querySelector('[data-detail-line-count]').textContent = '';
        detailModalElement.querySelector('[data-detail-preview]').textContent = translations.detailEmptyPreview || 'No preview available.';
        detailModal.show();

        sendRequest('detail', {
            id: id,
            type: type,
            limit: 100
        }, 'GET').then(function (response) {
            if (!response || !response.data) {
                return;
            }
            var entry = response.data.entry || {};
            var metadata = response.data.metadata || {};
            detailModalElement.querySelector('[data-detail-title]').textContent = entry.original_name || translations.detailTitle || 'File detail';
            var typeLabel = entry.source_type === 'nickname' ? (translations.tableTypeNickname || 'Nickname') : (translations.tableTypeContent || 'Content');
            detailModalElement.querySelector('[data-detail-type]').textContent = typeLabel;
            detailModalElement.querySelector('[data-detail-source]').textContent = entry.source_file || entry.original_name || '';
            detailModalElement.querySelector('[data-detail-industry]').textContent = recordCategoryLabel(entry.category || '');
            detailModalElement.querySelector('[data-detail-imported]').textContent = metadata.imported_at || entry.imported_at || entry.uploaded_at || '—';
            detailModalElement.querySelector('[data-detail-usage]').textContent = String(metadata.usage_count || entry.usage_count || 0);
            var statusLabel = translations['status' + capitalize(metadata.status || entry.status || 'active')] || (metadata.status || entry.status || 'active');
            detailModalElement.querySelector('[data-detail-status]').textContent = statusLabel;
            detailModalElement.querySelector('[data-detail-status]').className = 'badge ' + getStatusBadgeClass(metadata.status || entry.status || 'active');
            detailModalElement.querySelector('[data-detail-line-count]').textContent = (entry.line_count || 0) + ' / ' + (translations.detailPreviewCount || 'Preview lines');
            if (Array.isArray(response.data.preview) && response.data.preview.length) {
                detailModalElement.querySelector('[data-detail-preview]').textContent = response.data.preview.join('\n');
            } else {
                detailModalElement.querySelector('[data-detail-preview]').textContent = translations.detailEmptyPreview || 'No preview available.';
            }
        }).catch(handleError);
    }

    function submitUploadForm() {
        if (!uploadForm || !uploadFileInput) {
            return;
        }
        if (!uploadTypeField || !uploadTypeField.value) {
            handleError(new Error(translations.uploadErrorType || 'Please select an upload type.'));
            return;
        }
        if (!uploadForm.category.value) {
            handleError(new Error(translations.uploadErrorCategory || 'Please choose a category.'));
            return;
        }
        if (!uploadFileInput.files || !uploadFileInput.files.length) {
            handleError(new Error(translations.uploadErrorFiles || 'Please select at least one file.'));
            return;
        }
        if (uploadFileInput.files.length > maxUpload) {
            handleError(new Error((translations.uploadLimitExceeded || 'You can upload up to %d files at once.').replace('%d', maxUpload)));
            return;
        }

        var formData = new FormData(uploadForm);
        formData.set('library_type', uploadTypeField.value);
        formData.set('tokenCSRF', token);

        uploadSubmit.disabled = true;
        uploadSubmit.classList.add('disabled');

        sendRequest('upload', formData, 'POST', true).then(function (response) {
            if (response && response.data && Array.isArray(response.data.records)) {
                setRecords(response.data.records);
            }
            showToast(response.message || translations.uploadSuccess || 'Upload completed.');
            if (uploadModal) {
                uploadModal.hide();
            }
        }).catch(handleError).finally(function () {
            uploadSubmit.disabled = false;
            uploadSubmit.classList.remove('disabled');
            uploadForm.reset();
            if (uploadTypeField) {
                uploadTypeField.value = '';
            }
        });
    }

    function resetUploadForm() {
        if (!uploadForm) {
            return;
        }
        uploadForm.reset();
        if (uploadTypeField) {
            uploadTypeField.value = '';
        }
        if (uploadRadios && uploadRadios.length) {
            uploadRadios.forEach(function (radio) {
                radio.checked = false;
            });
        }
        if (uploadSubmit) {
            uploadSubmit.disabled = true;
            uploadSubmit.classList.add('disabled');
        }
    }

    function handleUploadModeChange(event) {
        if (!uploadTypeField) {
            return;
        }
        uploadTypeField.value = event.target.value;
        validateUploadForm();
    }

    function validateUploadForm() {
        if (!uploadSubmit) {
            return;
        }
        var hasType = Boolean(uploadTypeField && uploadTypeField.value);
        var hasFiles = Boolean(uploadFileInput && uploadFileInput.files && uploadFileInput.files.length);
        uploadSubmit.disabled = !(hasType && hasFiles);
        uploadSubmit.classList.toggle('disabled', uploadSubmit.disabled);
    }

    function sendRequest(action, payload, method, isFormData) {
        method = method || 'POST';
        var url = endpoint + '?action=' + encodeURIComponent(action);
        var options = { method: method };

        if (method === 'GET') {
            if (payload) {
                var params = new URLSearchParams();
                Object.keys(payload).forEach(function (key) {
                    params.append(key, payload[key]);
                });
                url += '&' + params.toString();
            }
        } else {
            if (isFormData && payload instanceof FormData) {
                options.body = payload;
            } else {
                var formData = new FormData();
                Object.keys(payload || {}).forEach(function (key) {
                    var value = payload[key];
                    if (value === undefined || value === null) {
                        return;
                    }
                    if (value instanceof File || value instanceof Blob) {
                        formData.append(key, value);
                        return;
                    }
                    if (typeof value === 'object') {
                        formData.append(key, JSON.stringify(value));
                    } else {
                        formData.append(key, value);
                    }
                });
                options.body = formData;
            }
        }

        return fetch(url, options)
            .then(function (response) {
                if (!response.ok) {
                    return response.json().then(function (data) {
                        var error = new Error(data.message || 'Request failed');
                        error.data = data;
                        throw error;
                    });
                }
                return response.json();
            });
    }

    function handleError(error) {
        var message = (error && error.message) ? error.message : 'Operation failed.';
        showToast(message, true);
    }

    function showToast(message, isError) {
        message = message || translations.toastTitle || 'Notification';
        if (window.showAlert) {
            window.showAlert(message, isError ? 'danger' : 'success');
            return;
        }
        if (window.bootstrap) {
            var toastContainer = document.getElementById('genericToast');
            if (!toastContainer) {
                console.log(message);
                return;
            }
        }
        console.log(message);
    }

    function formatTableSummary(start, end, total) {
        var template = translations.tableSummary || '%d-%d of %d files';
        return template.replace('%d', start).replace('%d', end).replace('%d', total);
    }

    function getStatusBadgeClass(status) {
        switch (status) {
            case 'active':
                return 'bg-success-subtle text-success';
            case 'paused':
                return 'bg-warning-subtle text-warning';
            case 'archived':
                return 'bg-secondary-subtle text-secondary';
            default:
                return 'bg-light text-muted';
        }
    }

    function setActiveTypeButton(type) {
        if (!filterTypeGroup) {
            return;
        }
        filterTypeGroup.querySelectorAll('[data-type]').forEach(function (button) {
            var buttonType = button.getAttribute('data-type');
            var variant = button.getAttribute('data-variant') || 'primary';
            var solidClass = 'btn-' + variant;
            var outlineClass = 'btn-outline-' + variant;
            var isActive = buttonType === type;

            button.classList.toggle('active', isActive);
            button.classList.remove('btn-primary', 'btn-outline-primary', 'btn-success', 'btn-outline-success', 'btn-secondary', 'btn-outline-secondary');
            if (isActive) {
                button.classList.add(solidClass);
            } else {
                button.classList.add(outlineClass);
            }
        });
    }

    function parseJSON(value) {
        if (!value) {
            return null;
        }
        try {
            return JSON.parse(value);
        } catch (error) {
            return null;
        }
    }

    function addOption(select, value, label) {
        var option = document.createElement('option');
        option.value = value;
        option.textContent = label;
        select.appendChild(option);
    }

    function getUnique(list, field) {
        var map = {};
        list.forEach(function (item) {
            if (item[field]) {
                map[item[field]] = true;
            }
        });
        return Object.keys(map);
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) {
            return '';
        }
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function capitalize(value) {
        if (!value) {
            return '';
        }
        return value.charAt(0).toUpperCase() + value.slice(1);
    }

    function recordCategoryLabel(category) {
        var match = state.records.find(function (record) {
            return record.category === category;
        });
        if (match) {
            return match.categoryLabel;
        }
        return category;
    }
})();
