(function() {
    'use strict';

    var ENTITY_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif', 'tif', 'tiff', 'ico'];
    var LINK_EXTENSIONS = ['txt'];

    function buildAcceptValue(extensions) {
        return extensions.map(function(ext) {
            return '.' + ext;
        }).join(',');
    }

    var ENTITY_ACCEPT_VALUE = buildAcceptValue(ENTITY_EXTENSIONS);
    var LINK_ACCEPT_VALUE = buildAcceptValue(LINK_EXTENSIONS);

    function formatBytes(bytes) {
        var value = Number(bytes) || 0;
        if (value < 1024) {
            return value + ' B';
        }
        var units = ['KB', 'MB', 'GB', 'TB'];
        var index = -1;
        do {
            value = value / 1024;
            index++;
        } while (value >= 1024 && index < units.length - 1);
        return value.toFixed(2).replace(/\.00$/, '') + ' ' + units[index];
    }

    function serializeIds(ids) {
        var container = document.querySelector('#imageLibraryBulkDeleteForm [data-bulk-ids-container]');
        if (!container) {
            return;
        }
        container.innerHTML = '';
        ids.forEach(function(id) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            container.appendChild(input);
        });
    }

    function isValidUploadType(type) {
        return type === 'entity' || type === 'link';
    }

    function setUploadType(type, scope) {
        var context = scope || document;
        var isValid = isValidUploadType(type);

        context.querySelectorAll('[data-upload-type]').forEach(function(input) {
            var matches = isValid && input.getAttribute('data-upload-type') === type;
            if (input.type === 'radio') {
                input.checked = matches;
            } else if (matches) {
                input.setAttribute('data-upload-selected', '1');
            } else {
                input.removeAttribute('data-upload-selected');
            }
        });

        var entityHint = context.querySelector('[data-upload-hint-entity]');
        var linkHint = context.querySelector('[data-upload-hint-link]');
        if (entityHint) {
            entityHint.classList.toggle('d-none', !(isValid && type === 'entity'));
        }
        if (linkHint) {
            linkHint.classList.toggle('d-none', !(isValid && type === 'link'));
        }

        var uploadForm = document.getElementById('imageLibraryUploadForm');
        if (uploadForm) {
            var typeField = uploadForm.querySelector('input[name="library_type"]');
            var fileInput = uploadForm.querySelector('#uploadFiles');
            var submitButton = uploadForm.querySelector('button[type="submit"]');

            if (typeField) {
                typeField.value = isValid ? type : '';
            }

            if (fileInput) {
                if (isValid) {
                    if (type === 'link') {
                        fileInput.setAttribute('accept', LINK_ACCEPT_VALUE);
                    } else {
                        fileInput.setAttribute('accept', ENTITY_ACCEPT_VALUE);
                    }
                } else {
                    fileInput.removeAttribute('accept');
                }
                fileInput.value = '';
            }

            if (submitButton) {
                if (isValid) {
                    submitButton.classList.remove('disabled');
                    submitButton.removeAttribute('disabled');
                } else {
                    submitButton.classList.add('disabled');
                    submitButton.setAttribute('disabled', 'disabled');
                }
            }
        }

        return isValid;
    }

    function contextHasSelectedUploadType(scope) {
        var context = scope || document;
        var selected = context.querySelector('[data-upload-type]:checked');
        if (selected) {
            return true;
        }
        if (context !== document) {
            return Boolean(document.querySelector('[data-upload-type]:checked'));
        }
        return false;
    }

    function getSelectedIds(type, scope) {
        var context = scope || document;
        var selector = '[data-select="' + type + '"]';
        var checkboxes = context.querySelectorAll(selector);
        var ids = [];
        checkboxes.forEach(function(box) {
            if (box.checked) {
                ids.push(box.value);
            }
        });
        return ids;
    }

    function toggleSelectAll(type, checked, scope) {
        var context = scope || document;
        context.querySelectorAll('[data-select="' + type + '"]').forEach(function(box) {
            box.checked = checked;
        });
    }


    var imageLibraryState = {
        isLoading: false,
        popstateBound: false
    };

    function setPageLoading(page, loading) {
        if (!page) {
            return;
        }
        if (loading) {
            page.classList.add('is-loading');
            page.setAttribute('aria-busy', 'true');
        } else {
            page.classList.remove('is-loading');
            page.removeAttribute('aria-busy');
        }
    }

    function applyAlertFromDocument(doc) {
        if (!doc) {
            return;
        }

        var newAlert = doc.getElementById('alert');
        var currentAlert = document.getElementById('alert');
        if (newAlert && currentAlert) {
            currentAlert.className = newAlert.className;
            currentAlert.innerHTML = newAlert.innerHTML;
            currentAlert.style.display = 'none';
        }

        var scripts = doc.querySelectorAll('script');
        var alertScript = null;
        scripts.forEach(function(script) {
            if (script.textContent && script.textContent.indexOf('window.ALERT_MESSAGE') !== -1) {
                alertScript = script.textContent;
            }
        });

        if (alertScript) {
            try {
                window.ALERT_MESSAGE = '';
            } catch (error) {
                window.ALERT_MESSAGE = '';
            }
            var tempScript = document.createElement('script');
            tempScript.textContent = alertScript;
            document.head.appendChild(tempScript);
            document.head.removeChild(tempScript);
        } else {
            window.ALERT_MESSAGE = '';
        }

        if (typeof window.ALERT_MESSAGE === 'string' && window.ALERT_MESSAGE) {
            if (typeof window.showAlert === 'function') {
                window.showAlert(window.ALERT_MESSAGE);
            } else {
                window.alert(window.ALERT_MESSAGE);
            }
        }
    }

    function copyElementAttributes(target, source) {
        if (!target || !source) {
            return;
        }

        Array.prototype.slice.call(target.attributes).forEach(function(attribute) {
            if (!source.hasAttribute(attribute.name)) {
                target.removeAttribute(attribute.name);
            }
        });

        Array.prototype.slice.call(source.attributes).forEach(function(attribute) {
            target.setAttribute(attribute.name, attribute.value);
        });
    }

    function syncSection(currentRoot, newRoot, selector) {
        if (!currentRoot || !newRoot) {
            return false;
        }
        var currentEl = currentRoot.querySelector(selector);
        var newEl = newRoot.querySelector(selector);
        if (!currentEl || !newEl) {
            return false;
        }
        copyElementAttributes(currentEl, newEl);
        currentEl.innerHTML = newEl.innerHTML;
        return true;
    }

    function renderImageLibraryHtml(html, url) {
        if (typeof DOMParser === 'undefined') {
            return false;
        }

        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var newPage = doc.querySelector('.image-library-page');
        var currentPage = document.querySelector('.image-library-page');

        if (!newPage || !currentPage) {
            return false;
        }

        var syncedSections = 0;
        if (syncSection(currentPage, newPage, '[data-library-header]')) {
            syncedSections++;
        }
        if (syncSection(currentPage, newPage, '[data-library-overview]')) {
            syncedSections++;
        }
        if (syncSection(currentPage, newPage, '[data-library-table]')) {
            syncedSections++;
        }

        if (syncedSections === 3) {
            var newActiveType = newPage.getAttribute('data-active-type');
            if (newActiveType) {
                currentPage.setAttribute('data-active-type', newActiveType);
            }
            var newDefaultUploadType = newPage.getAttribute('data-default-upload-type');
            if (newDefaultUploadType !== null) {
                currentPage.setAttribute('data-default-upload-type', newDefaultUploadType);
            }
        } else {
            currentPage.replaceWith(newPage);
            currentPage = newPage;
        }

        applyAlertFromDocument(doc);

        if (doc.title) {
            document.title = doc.title;
        }

        document.dispatchEvent(new CustomEvent('spa:loaded', {
            detail: {
                url: url,
                source: 'image-library'
            }
        }));

        return true;
    }

    function ensureImageLibraryHistoryBinding() {
        if (imageLibraryState.popstateBound) {
            return;
        }
        if (!window.history || typeof window.history.pushState !== 'function') {
            return;
        }

        window.addEventListener('popstate', function(event) {
            if (!document.querySelector('.image-library-page')) {
                return;
            }
            if (event.state && event.state.mgwImageLibrary) {
                loadImageLibrary(window.location.href, { replaceState: true });
            }
        });

        imageLibraryState.popstateBound = true;
    }

    function buildImageLibraryHistoryState(extra) {
        var base = {};
        if (window.history && typeof window.history.state === 'object' && window.history.state !== null) {
            for (var key in window.history.state) {
                if (Object.prototype.hasOwnProperty.call(window.history.state, key) && key !== 'mgwImageLibrary') {
                    base[key] = window.history.state[key];
                }
            }
        }
        base.mgwImageLibrary = true;
        if (extra && typeof extra === 'object') {
            for (var extraKey in extra) {
                if (Object.prototype.hasOwnProperty.call(extra, extraKey)) {
                    base[extraKey] = extra[extraKey];
                }
            }
        }
        return base;
    }

    function loadImageLibrary(url, options) {
        options = options || {};
        var page = document.querySelector('.image-library-page');
        if (!page || imageLibraryState.isLoading) {
            return Promise.resolve(false);
        }

        imageLibraryState.isLoading = true;
        setPageLoading(page, true);

        return fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-SPA-Request': 'true'
            },
            credentials: 'same-origin'
        }).then(function(response) {
            var finalUrl = response.url || url;
            return response.text().then(function(html) {
                var rendered = renderImageLibraryHtml(html, finalUrl);
                if (!rendered) {
                    window.location.href = finalUrl;
                    return false;
                }

                if (options.pushState && window.history && typeof window.history.pushState === 'function') {
                    window.history.pushState(buildImageLibraryHistoryState(), '', finalUrl);
                } else if (options.replaceState && window.history && typeof window.history.replaceState === 'function') {
                    window.history.replaceState(buildImageLibraryHistoryState(), '', finalUrl);
                }

                return true;
            });
        }).catch(function(error) {
            console.error('Image library navigation failed:', error);
            window.location.href = url;
            return false;
        }).then(function(result) {
            imageLibraryState.isLoading = false;
            var nextPage = document.querySelector('.image-library-page');
            if (nextPage) {
                setPageLoading(nextPage, false);
            }
            return result;
        });
    }

    function submitImageLibraryAction(form) {
        var page = document.querySelector('.image-library-page');
        if (!page || !form || imageLibraryState.isLoading) {
            return Promise.resolve(false);
        }

        imageLibraryState.isLoading = true;
        setPageLoading(page, true);

        var actionUrl = form.getAttribute('action') || window.location.href;
        var method = (form.getAttribute('method') || 'POST').toUpperCase();
        var formData = new FormData(form);

        return fetch(actionUrl, {
            method: method,
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-SPA-Request': 'true'
            },
            credentials: 'same-origin'
        }).then(function(response) {
            var finalUrl = response.url || window.location.href;
            return response.text().then(function(html) {
                var rendered = renderImageLibraryHtml(html, finalUrl);
                if (!rendered) {
                    window.location.href = finalUrl;
                    return false;
                }

                if (window.history && typeof window.history.replaceState === 'function') {
                    window.history.replaceState(buildImageLibraryHistoryState(), '', finalUrl);
                }

                return true;
            });
        }).catch(function(error) {
            console.error('Image library request failed:', error);
            window.location.href = actionUrl;
            return false;
        }).then(function(result) {
            imageLibraryState.isLoading = false;
            var nextPage = document.querySelector('.image-library-page');
            if (nextPage) {
                setPageLoading(nextPage, false);
            }
            return result;
        });
    }


    function resolveModalApi() {
        if (typeof bootstrap !== 'undefined' && bootstrap && typeof bootstrap.Modal === 'function') {
            return bootstrap.Modal;
        }
        if (typeof window !== 'undefined') {
            var candidate = window.bootstrap || window.Bootstrap || window.bootstrap5 || window.Bootstrap5;
            if (candidate && typeof candidate.Modal === 'function') {
                return candidate.Modal;
            }
            if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                return {
                    getOrCreateInstance: function(element) {
                        return {
                            show: function() {
                                window.jQuery(element).modal('show');
                            },
                            hide: function() {
                                window.jQuery(element).modal('hide');
                            }
                        };
                    }
                };
            }
        }
        return null;
    }

    function resetModalStack(modal) {
        if (!modal) {
            return;
        }
        modal.style.removeProperty('z-index');
        var dialog = modal.querySelector('.modal-dialog');
        if (dialog) {
            dialog.style.removeProperty('z-index');
            dialog.style.removeProperty('position');
        }
        var content = modal.querySelector('.modal-content');
        if (content) {
            content.style.removeProperty('z-index');
        }
    }

    function adjustModalStack(modal) {
        if (!modal) {
            return;
        }

        if (modal.parentNode && modal.parentNode !== document.body) {
            document.body.appendChild(modal);
        }

        var applyZIndex = function() {
            var backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function(el, index) {
                el.style.zIndex = String(1990 + index);
                el.style.removeProperty('display');
            });

            modal.style.zIndex = '2000';

            var dialog = modal.querySelector('.modal-dialog');
            if (dialog) {
                dialog.style.zIndex = '2001';
                dialog.style.position = 'relative';
            }

            var content = modal.querySelector('.modal-content');
            if (content) {
                content.style.zIndex = '2002';
            }
        };

        var ensureAccessibility = function() {
            if (!modal.classList.contains('show')) {
                return;
            }
            modal.removeAttribute('aria-hidden');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('role', 'dialog');

            var focusTarget = modal.querySelector('[data-autofocus], input:not([type="hidden" i]), textarea, select, button');
            if (focusTarget && typeof focusTarget.focus === 'function') {
                focusTarget.focus({ preventScroll: true });
            }
        };

        if (!modal.getAttribute('data-mgw-stack-bound')) {
            modal.addEventListener('shown.bs.modal', function() {
                applyZIndex();
                ensureAccessibility();
            });

            modal.addEventListener('hidden.bs.modal', function() {
                resetModalStack(modal);
                modal.removeAttribute('aria-modal');
                modal.removeAttribute('role');

                if (!document.querySelector('.modal.show')) {
                    document.querySelectorAll('.modal-backdrop').forEach(function(el) {
                        if (el.parentNode) {
                            el.parentNode.removeChild(el);
                        }
                    });
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                }
            });

            modal.setAttribute('data-mgw-stack-bound', '1');
        }

        setTimeout(function() {
            applyZIndex();
            ensureAccessibility();
        }, 0);
    }

    function fallbackShowModal(modal) {
        if (!modal) {
            return;
        }
        document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
            if (backdrop.parentNode) {
                backdrop.parentNode.removeChild(backdrop);
            }
        });
        document.body.classList.remove('modal-open');

        if (!modal.classList.contains('show')) {
            modal.classList.add('show');
        }
        modal.style.display = 'block';
        if (!modal.hasAttribute('data-fallback-zindex')) {
            modal.setAttribute('data-fallback-zindex', modal.style.zIndex || '');
        }
        modal.style.zIndex = '1055';
        modal.style.pointerEvents = 'auto';
        modal.setAttribute('aria-hidden', 'false');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('role', 'dialog');
        modal.scrollTop = 0;

        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);

        document.body.classList.add('modal-open');

        adjustModalStack(modal);
    }

    function fallbackHideModal(modal) {
        if (!modal) {
            return;
        }
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');

        resetModalStack(modal);

        if (modal.hasAttribute('data-fallback-zindex')) {
            var previous = modal.getAttribute('data-fallback-zindex');
            if (previous) {
                modal.style.zIndex = previous;
            } else {
                modal.style.removeProperty('z-index');
            }
            modal.removeAttribute('data-fallback-zindex');
        }
        modal.style.pointerEvents = '';

        document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
            if (backdrop.parentNode) {
                backdrop.parentNode.removeChild(backdrop);
            }
        });

        if (!document.querySelector('.modal.show')) {
            document.body.classList.remove('modal-open');
        }
    }

    function showModal(modal) {
        if (!modal) {
            return;
        }
        var ModalApi = resolveModalApi();
        if (ModalApi) {
            var instance = ModalApi.getOrCreateInstance(modal);
            if (instance && typeof instance.show === 'function') {
                instance.show();
                return;
            }
        }
        adjustModalStack(modal);
        fallbackShowModal(modal);
    }

    function hideModal(modal) {
        if (!modal) {
            return;
        }
        var ModalApi = resolveModalApi();
        if (ModalApi) {
            var instance = ModalApi.getOrCreateInstance(modal);
            if (instance && typeof instance.hide === 'function') {
                instance.hide();
                return;
            }
        }
        fallbackHideModal(modal);
    }

    function setDefaultCategoryValue(form) {
        if (!form) {
            return;
        }
        var hiddenInput = form.querySelector('#uploadCategoryValue');
        var select = form.querySelector('[data-category-select]');
        var customInput = form.querySelector('[data-category-custom]');
        if (!hiddenInput) {
            return;
        }
        var defaultValue = hiddenInput.getAttribute('data-default-category') || '';
        hiddenInput.value = defaultValue;

        if (!select) {
            if (customInput) {
                customInput.classList.remove('d-none');
                customInput.value = defaultValue;
            }
            return;
        }

        var optionFound = false;
        select.querySelectorAll('option').forEach(function(option) {
            if (option.value === defaultValue) {
                optionFound = true;
            }
        });

        if (optionFound) {
            select.value = defaultValue;
            if (customInput) {
                customInput.classList.add('d-none');
                customInput.value = '';
            }
        } else {
            select.value = '__custom__';
            if (customInput) {
                customInput.classList.remove('d-none');
                customInput.value = defaultValue;
            }
        }
    }

    function ensureCheckboxMetadata(page) {
        page.querySelectorAll('table').forEach(function(table) {
            var master = table.querySelector('thead input[type="checkbox"]');
            var rows = table.querySelectorAll('tbody tr');
            var inferredType = null;

            rows.forEach(function(row) {
                var rowType = row.getAttribute('data-entry-row');
                if (rowType && !inferredType) {
                    inferredType = rowType;
                }
                var checkbox = row.querySelector('input[type="checkbox"]');
                if (!checkbox) {
                    return;
                }
                if (!checkbox.hasAttribute('data-select')) {
                    if (rowType) {
                        checkbox.setAttribute('data-select', rowType);
                    }
                }
                if (!checkbox.hasAttribute('value')) {
                    var entryId = row.getAttribute('data-entry-id');
                    if (entryId) {
                        checkbox.value = entryId;
                    }
                }
            });

            if (master) {
                if (!inferredType && master.hasAttribute('data-select-all')) {
                    inferredType = master.getAttribute('data-select-all');
                }
                if (!master.hasAttribute('data-select-all') && inferredType) {
                    master.setAttribute('data-select-all', inferredType);
                }
            }
        });
    }

    function findUploadTrigger(page) {
        var trigger = page.querySelector('[data-upload-trigger]');
        if (trigger) {
            return trigger;
        }

        trigger = page.querySelector('[data-action="image-library-upload"]');
        if (trigger) {
            return trigger;
        }

        var candidates = Array.prototype.slice.call(page.querySelectorAll('button, a.btn'));
        return candidates.find(function(btn) {
            var text = (btn.textContent || '').trim();
            return text === '上传' || text.toLowerCase() === 'upload';
        }) || null;
    }

    function ensureConfirmModal() {
        var modal = document.getElementById('imageLibraryConfirmModal');
        if (modal) {
            return modal;
        }

        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'imageLibraryConfirmModal';
        modal.tabIndex = -1;
        modal.setAttribute('aria-hidden', 'true');
        modal.setAttribute('data-bs-backdrop', 'static');
        modal.setAttribute('data-bs-keyboard', 'false');
        modal.innerHTML = '' +
            '<div class="modal-dialog modal-dialog-centered">' +
                '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        '<h5 class="modal-title" data-confirm-title></h5>' +
                        '<button type="button" class="btn-close" data-confirm-cancel aria-label="Close"></button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                        '<p class="mb-0" data-confirm-message></p>' +
                    '</div>' +
                    '<div class="modal-footer">' +
                        '<button type="button" class="btn btn-secondary" data-confirm-cancel></button>' +
                        '<button type="button" class="btn btn-danger" data-confirm-ok></button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        document.body.appendChild(modal);
        return modal;
    }

    function openConfirmationDialog(options) {
        var modal = ensureConfirmModal();
        var titleEl = modal.querySelector('[data-confirm-title]');
        var messageEl = modal.querySelector('[data-confirm-message]');
        var confirmBtn = modal.querySelector('[data-confirm-ok]');
        var cancelBtns = modal.querySelectorAll('[data-confirm-cancel]');
        var confirmLabel = options && options.confirmLabel ? options.confirmLabel : 'OK';
        var cancelLabel = options && options.cancelLabel ? options.cancelLabel : 'Cancel';
        var titleLabel = options && options.title ? options.title : '';

        if (titleEl) {
            titleEl.textContent = titleLabel || confirmLabel;
        }
        if (messageEl) {
            messageEl.textContent = (options && options.message) ? options.message : '';
        }
        if (confirmBtn) {
            confirmBtn.textContent = confirmLabel;
        }
        cancelBtns.forEach(function(btn) {
            if (btn.classList.contains('btn-close')) {
                btn.setAttribute('aria-label', cancelLabel);
                btn.setAttribute('title', cancelLabel);
            } else {
                btn.textContent = cancelLabel;
            }
        });

        return new Promise(function(resolve) {
            function cleanup(result) {
                if (confirmBtn) {
                    confirmBtn.removeEventListener('click', onConfirm);
                }
                cancelBtns.forEach(function(btn) {
                    btn.removeEventListener('click', onCancel);
                });
                hideModal(modal);
                resolve(result);
            }

            function onConfirm() {
                cleanup(true);
            }

            function onCancel() {
                cleanup(false);
            }

            if (confirmBtn) {
                confirmBtn.addEventListener('click', onConfirm, { once: true });
            }
            cancelBtns.forEach(function(btn) {
                btn.addEventListener('click', onCancel, { once: true });
            });

            adjustModalStack(modal);
            showModal(modal);
        });
    }

    function initImageLibraryPage() {
        var page = document.querySelector('.image-library-page');
        if (!page) {
            return;
        }

        document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
            if (backdrop.parentNode) {
                backdrop.parentNode.removeChild(backdrop);
            }
        });
        document.body.classList.remove('modal-open');

        if (page.getAttribute('data-image-library-initialized') === '1') {
            return;
        }

        page.setAttribute('data-image-library-initialized', '1');

        ensureImageLibraryHistoryBinding();
        if (window.history && typeof window.history.replaceState === 'function') {
            var existingState = window.history.state;
            if (!existingState || !existingState.mgwImageLibrary) {
                window.history.replaceState(buildImageLibraryHistoryState(), '', window.location.href);
            }
        }

        var confirmDeleteText = page.getAttribute('data-confirm-delete') || 'Confirm delete?';
        var confirmBulkText = page.getAttribute('data-confirm-bulk') || 'Confirm bulk delete?';
        var errorNoSelection = page.getAttribute('data-error-no-selection') || 'Please select at least one item.';
        var uploadErrorEntity = page.getAttribute('data-error-upload-entity') || '';
        var uploadErrorLink = page.getAttribute('data-error-upload-link') || '';
        var errorCategoryEmpty = page.getAttribute('data-error-category-empty') || '';
    var uploadErrorType = page.getAttribute('data-error-upload-type') || '';
        var activeListType = page.getAttribute('data-active-type') || 'entity';
    var defaultUploadType = page.getAttribute('data-default-upload-type') || '';
        var confirmDefaultTitle = page.getAttribute('data-confirm-title') || '';
        var confirmConfirmLabel = page.getAttribute('data-confirm-label') || 'Delete';
        var confirmCancelLabel = page.getAttribute('data-confirm-cancel') || 'Cancel';
        var uploadModalEl = document.getElementById('imageLibraryUploadModal');
        var uploadForm = document.getElementById('imageLibraryUploadForm');
        var categorySelect = uploadForm ? uploadForm.querySelector('[data-category-select]') : null;
        var categoryCustomInput = uploadForm ? uploadForm.querySelector('[data-category-custom]') : null;
        var categoryHiddenInput = uploadForm ? uploadForm.querySelector('#uploadCategoryValue') : null;

        function showNotification(message) {
            if (!message) {
                return;
            }
            if (typeof window.showAlert === 'function') {
                window.showAlert(message);
            } else {
                window.alert(message);
            }
        }

        function toggleCustomCategoryInput(show) {
            if (!categoryCustomInput) {
                return;
            }
            if (show) {
                categoryCustomInput.classList.remove('d-none');
                categoryCustomInput.focus();
            } else {
                categoryCustomInput.classList.add('d-none');
                categoryCustomInput.value = '';
            }
        }

        function syncCategoryHiddenValue() {
            if (!categoryHiddenInput) {
                return;
            }
            if (categorySelect && categorySelect.value === '__custom__') {
                var customValue = categoryCustomInput ? categoryCustomInput.value.trim() : '';
                categoryHiddenInput.value = customValue;
            } else if (categorySelect && categorySelect.value) {
                categoryHiddenInput.value = categorySelect.value;
            }
        }

        ensureCheckboxMetadata(page);

        if (uploadForm) {
            if (isValidUploadType(defaultUploadType)) {
                setUploadType(defaultUploadType, uploadForm);
            } else {
                setUploadType(null, uploadForm);
            }
            setDefaultCategoryValue(uploadForm);
        }
        if (categorySelect) {
            if (categorySelect.value === '__custom__') {
                toggleCustomCategoryInput(true);
            } else {
                toggleCustomCategoryInput(false);
            }
            syncCategoryHiddenValue();
        }

        var uploadTypeInputs = [];
        if (uploadForm) {
            uploadForm.querySelectorAll('[data-upload-type]').forEach(function(input) {
                uploadTypeInputs.push(input);
            });
        }
        page.querySelectorAll('[data-upload-type]').forEach(function(input) {
            if (uploadTypeInputs.indexOf(input) === -1) {
                uploadTypeInputs.push(input);
            }
        });

        uploadTypeInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                if (input.checked) {
                    setUploadType(input.getAttribute('data-upload-type'), uploadForm || page);
                } else if (!contextHasSelectedUploadType(uploadForm || page)) {
                    setUploadType(null, uploadForm || page);
                }
            });
        });

        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                if (categorySelect.value === '__custom__') {
                    toggleCustomCategoryInput(true);
                } else {
                    toggleCustomCategoryInput(false);
                }
                syncCategoryHiddenValue();
            });
        }

        if (categoryCustomInput) {
            categoryCustomInput.addEventListener('input', function() {
                if (categorySelect && categorySelect.value === '__custom__') {
                    syncCategoryHiddenValue();
                }
            });
        }

        page.querySelectorAll('[data-switch-type]').forEach(function(link) {
            link.addEventListener('click', function(event) {
                var targetUrl = link.getAttribute('href');
                if (!targetUrl) {
                    return;
                }
                event.preventDefault();
                loadImageLibrary(targetUrl, { pushState: true });
            });
        });

        var uploadTrigger = findUploadTrigger(page);
        if (uploadTrigger && uploadModalEl) {
            uploadTrigger.addEventListener('click', function() {
                if (uploadForm) {
                    uploadForm.reset();
                    setUploadType(null, uploadForm);
                    setDefaultCategoryValue(uploadForm);
                    if (categorySelect) {
                        if (categorySelect.value === '__custom__') {
                            toggleCustomCategoryInput(true);
                        } else {
                            toggleCustomCategoryInput(false);
                        }
                        syncCategoryHiddenValue();
                    }
                    var submitButton = uploadForm.querySelector('button[type="submit"]');
                    if (submitButton) {
                        var spinnerEl = submitButton.querySelector('.spinner-border');
                        var labelEl = submitButton.querySelector('.label-text');
                        if (spinnerEl) {
                            spinnerEl.classList.add('d-none');
                        }
                        if (labelEl) {
                            labelEl.classList.remove('d-none');
                        }
                    }
                }
                adjustModalStack(uploadModalEl);
                showModal(uploadModalEl);
            });
        }

        if (uploadForm) {
            var uploadSubmitButton = uploadForm.querySelector('button[type="submit"]');
            var uploadFileInput = uploadForm.querySelector('#uploadFiles');

            function getCurrentUploadType() {
                var typeField = uploadForm.querySelector('input[name="library_type"]');
                var value = typeField && typeField.value ? typeField.value : '';
                if (isValidUploadType(value)) {
                    return value;
                }
                var selected = (uploadForm || document).querySelector('[data-upload-type]:checked');
                if (selected) {
                    var inferred = selected.getAttribute('data-upload-type');
                    if (isValidUploadType(inferred)) {
                        return inferred;
                    }
                }
                return '';
            }

            function validateUploadFiles(files, mode) {
                if (!files || !files.length) {
                    return true;
                }
                if (!isValidUploadType(mode)) {
                    return false;
                }
                var allowed = mode === 'link' ? LINK_EXTENSIONS : ENTITY_EXTENSIONS;
                for (var i = 0; i < files.length; i++) {
                    var name = files[i].name || '';
                    var extension = '';
                    var dotIndex = name.lastIndexOf('.');
                    if (dotIndex !== -1) {
                        extension = name.substring(dotIndex + 1).toLowerCase();
                    }
                    if (allowed.indexOf(extension) === -1) {
                        var message = mode === 'link' ? uploadErrorLink : uploadErrorEntity;
                        if (!message) {
                            if (mode === 'link') {
                                message = 'Only TXT files are allowed.';
                            } else {
                                message = 'Only JPG, PNG, GIF, WEBP, SVG, BMP, AVIF, TIFF, or similar image files are allowed.';
                            }
                        }
                        showNotification(message);
                        return false;
                    }
                }
                return true;
            }

            if (uploadFileInput) {
                uploadFileInput.addEventListener('change', function() {
                    var mode = getCurrentUploadType();
                    if (!isValidUploadType(mode)) {
                        var typeMessage = uploadErrorType || 'Please choose an upload type first.';
                        showNotification(typeMessage);
                        uploadFileInput.value = '';
                        return;
                    }
                    if (!validateUploadFiles(uploadFileInput.files, mode)) {
                        uploadFileInput.value = '';
                    }
                });
            }

            uploadForm.addEventListener('submit', function(event) {
                var mode = getCurrentUploadType();

                if (!isValidUploadType(mode)) {
                    event.preventDefault();
                    var typeMessage = uploadErrorType || 'Please choose an upload type.';
                    showNotification(typeMessage);
                    return;
                }

                if (categorySelect) {
                    if (categorySelect.value === '__custom__') {
                        var customValue = categoryCustomInput ? categoryCustomInput.value.trim() : '';
                        if (!customValue) {
                            event.preventDefault();
                            var categoryMessage = errorCategoryEmpty || '请输入行业名称';
                            showNotification(categoryMessage);
                            if (categoryCustomInput) {
                                categoryCustomInput.focus();
                            }
                            return;
                        }
                        if (categoryHiddenInput) {
                            categoryHiddenInput.value = customValue;
                        }
                    } else if (categoryHiddenInput) {
                        categoryHiddenInput.value = categorySelect.value;
                    }
                }

                if (!validateUploadFiles(uploadFileInput ? uploadFileInput.files : null, mode)) {
                    event.preventDefault();
                    if (uploadFileInput) {
                        uploadFileInput.value = '';
                    }
                    return;
                }

                var typeField = uploadForm.querySelector('input[name="library_type"]');
                if (typeField) {
                    typeField.value = mode;
                }

                if (!uploadSubmitButton) {
                    return;
                }

                var spinner = uploadSubmitButton.querySelector('.spinner-border');
                var label = uploadSubmitButton.querySelector('.label-text');
                uploadSubmitButton.classList.add('disabled');
                uploadSubmitButton.setAttribute('disabled', 'disabled');
                if (spinner) {
                    spinner.classList.remove('d-none');
                }
                if (label) {
                    label.classList.add('d-none');
                }
            });
        }

        var bulkButton = page.querySelector('[data-bulk-delete]');

        function updateBulkDeleteState() {
            if (!bulkButton) {
                return;
            }
            var selectedIds = getSelectedIds(activeListType, page);
            var badge = bulkButton.querySelector('[data-bulk-count]');

            if (selectedIds.length > 0) {
                bulkButton.removeAttribute('disabled');
                if (badge) {
                    badge.textContent = selectedIds.length;
                    badge.classList.remove('d-none');
                }
            } else {
                bulkButton.setAttribute('disabled', 'disabled');
                if (badge) {
                    badge.textContent = '';
                    badge.classList.add('d-none');
                }
            }
        }

        function syncMasterCheckbox(type) {
            var master = page.querySelector('[data-select-all="' + type + '"]');
            if (!master) {
                return;
            }
            var items = page.querySelectorAll('[data-select="' + type + '"]');
            if (!items.length) {
                master.checked = false;
                master.indeterminate = false;
                return;
            }
            var checkedCount = 0;
            items.forEach(function(item) {
                if (item.checked) {
                    checkedCount++;
                }
            });
            master.checked = checkedCount === items.length;
            master.indeterminate = checkedCount > 0 && checkedCount < items.length;
        }



        page.querySelectorAll('input[type="checkbox"][data-select-all]').forEach(function(master) {
            master.addEventListener('change', function() {
                var type = master.getAttribute('data-select-all');
                toggleSelectAll(type, master.checked, page);
                syncMasterCheckbox(type);
                updateBulkDeleteState();
            });
        });

        page.querySelectorAll('input[type="checkbox"][data-select]').forEach(function(box) {
            box.addEventListener('change', function() {
                var type = box.getAttribute('data-select');
                syncMasterCheckbox(type);
                updateBulkDeleteState();
            });
        });

        page.querySelectorAll('[data-delete]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var type = btn.getAttribute('data-delete');
                var id = btn.getAttribute('data-entry-id');
                if (!type || !id) {
                    return;
                }
                openConfirmationDialog({
                    title: confirmDefaultTitle || confirmDeleteText,
                    message: confirmDeleteText,
                    confirmLabel: confirmConfirmLabel,
                    cancelLabel: confirmCancelLabel
                }).then(function(confirmed) {
                    if (!confirmed) {
                        return;
                    }
                    var form = document.getElementById('imageLibraryDeleteForm');
                    if (!form) {
                        return;
                    }
                    form.querySelector('input[name="library_type"]').value = type;
                    form.querySelector('input[name="id"]').value = id;
                    submitImageLibraryAction(form);
                });
            });
        });

        if (bulkButton) {
            bulkButton.addEventListener('click', function() {
                var ids = getSelectedIds(activeListType, page);
                if (!ids.length) {
                    showNotification(errorNoSelection);
                    return;
                }
                openConfirmationDialog({
                    title: confirmDefaultTitle || confirmBulkText,
                    message: confirmBulkText,
                    confirmLabel: confirmConfirmLabel,
                    cancelLabel: confirmCancelLabel
                }).then(function(confirmed) {
                    if (!confirmed) {
                        return;
                    }
                    var form = document.getElementById('imageLibraryBulkDeleteForm');
                    if (!form) {
                        return;
                    }
                    form.querySelector('input[name="library_type"]').value = activeListType;
                    serializeIds(ids);
                    submitImageLibraryAction(form);
                });
            });
        }

        syncMasterCheckbox(activeListType);
        updateBulkDeleteState();

        var perPageSelect = page.querySelector('[data-per-page-select]');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                var form = perPageSelect.closest('form');
                if (!form) {
                    return;
                }
                var pageInput = form.querySelector('input[name="page"]');
                if (pageInput) {
                    pageInput.value = '1';
                }
                form.submit();
            });
        }

    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initImageLibraryPage);
    } else {
        initImageLibraryPage();
    }

    document.addEventListener('spa:loaded', function() {
        var page = document.querySelector('.image-library-page');
        if (page) {
            page.removeAttribute('data-image-library-initialized');
        }
        initImageLibraryPage();
    });

    document.addEventListener('click', function(event) {
        var dismissBtn = event.target.closest('[data-bs-dismiss="modal"]');
        if (!dismissBtn) {
            return;
        }
        var modal = dismissBtn.closest('.modal');
        if (modal) {
            hideModal(modal);
        }
    });
})();
