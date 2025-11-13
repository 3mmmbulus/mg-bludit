/**
 * 站点列表页面交互脚本
 *
 * 用途：
 * - 处理站点分组的搜索、新增、编辑、删除操作
 * 处理站点分组的搜索、新增、编辑、删除操作
 * 依赖：
 * - jQuery 3.x
 * - Bootstrap 5
 * - modal-helper.js (可选)
 *
 * 页面：/admin/site-list
 *
 * @version 1.1.0
 * @since 2025-11-13
 */

$(function() {
    var STEP_COUNT = 2;

    function getGroupModal() {
        return $('#groupModal');
    }

    function getDeleteModal() {
        return $('#deleteModal');
    }

    function adjustModalStack(modalEl) {
        if (!modalEl) {
            return;
        }

        if (modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }

        if (typeof MaigwanModal !== 'undefined' && typeof MaigwanModal.adjustStack === 'function') {
            MaigwanModal.adjustStack(modalEl);
            return;
        }

        var applyZIndex = function() {
            var backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function(el, index) {
                el.style.zIndex = String(1990 + index);
                el.style.removeProperty('display');
            });

            modalEl.style.zIndex = '2000';

            var dialog = modalEl.querySelector('.modal-dialog');
            if (dialog) {
                dialog.style.zIndex = '2001';
                dialog.style.position = 'relative';
            }

            var content = modalEl.querySelector('.modal-content');
            if (content) {
                content.style.zIndex = '2002';
            }
        };

        var ensureAccessibility = function() {
            modalEl.removeAttribute('aria-hidden');
            modalEl.setAttribute('aria-modal', 'true');

            var focusTarget = modalEl.querySelector('[data-autofocus], input:not([type="hidden" i]), textarea, select, button');
            if (focusTarget && typeof focusTarget.focus === 'function') {
                focusTarget.focus({ preventScroll: true });
            } else if (typeof modalEl.focus === 'function') {
                modalEl.focus({ preventScroll: true });
            }
        };

        if (!modalEl.dataset.mgwStackBound) {
            modalEl.addEventListener('shown.bs.modal', function() {
                applyZIndex();
                ensureAccessibility();
            });

            modalEl.addEventListener('hidden.bs.modal', function() {
                modalEl.removeAttribute('aria-modal');

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

            modalEl.dataset.mgwStackBound = '1';
        }

        setTimeout(applyZIndex, 0);
    }

    function decodeAttr($el, attributeName) {
        if (!$el || !$el.length) {
            return '';
        }
        var raw = $el.attr(attributeName);
        if (typeof raw !== 'string') {
            return '';
        }
        return $('<textarea/>').html(raw).text().trim();
    }

    function parseJSONAttribute($el, attributeName) {
        var decoded = decodeAttr($el, attributeName);
        if (!decoded) {
            return null;
        }
        try {
            return JSON.parse(decoded);
        } catch (error) {
            return null;
        }
    }

    function extractDomains($row) {
        var parsed = parseJSONAttribute($row, 'data-group-domains');
        if (Array.isArray(parsed)) {
            return parsed;
        }

        var fallback = decodeAttr($row, 'data-group-domains');
        if (!fallback) {
            return [];
        }

        return fallback.split(/\r?\n|,/).map(function(item) {
            return item.trim();
        }).filter(function(item) {
            return item.length > 0;
        });
    }

    function buildDomainTextareaValue(domains, templateMap) {
        if (!Array.isArray(domains) || !domains.length) {
            return '';
        }

        templateMap = templateMap && typeof templateMap === 'object' ? templateMap : {};

        return domains.map(function(domain) {
            var template = templateMap[domain];
            if (typeof template === 'string' && template.trim() !== '') {
                return domain + '-' + template.trim();
            }
            return domain;
        }).join('\n');
    }

    function getChoiceButtons($context, key) {
        return $context.find('[data-choice-input="' + key + '"]');
    }

    function getChoiceInput($context, key) {
        return $context.find('#' + key);
    }

    function setChoiceValue($context, key, value) {
        var $input = getChoiceInput($context, key);
        if (!$input.length) {
            return;
        }
        var normalized = (value || '').toString();
        $input.val(normalized);

        getChoiceButtons($context, key).each(function() {
            var $btn = $(this);
            var btnValue = ($btn.data('choiceValue') || '').toString();
            var variant = ($btn.data('choiceVariant') || 'primary').toString();
            var outlineClass = 'btn-outline-' + variant;
            var solidClass = 'btn-' + variant;
            var isActive = btnValue === normalized;

            if (isActive) {
                $btn.addClass('active');
                if (!$btn.hasClass(solidClass)) {
                    $btn.addClass(solidClass);
                }
                $btn.removeClass(outlineClass);
            } else {
                $btn.removeClass('active');
                $btn.removeClass(solidClass);
                if (!$btn.hasClass(outlineClass)) {
                    $btn.addClass(outlineClass);
                }
            }

            $btn.attr('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function getChoiceValue($context, key) {
        var $input = getChoiceInput($context, key);
        if (!$input.length) {
            return '';
        }
        return ($input.val() || '').toString();
    }

    function setChoiceGroupDisabled($context, key, disabled) {
        getChoiceButtons($context, key).each(function() {
            var $btn = $(this);
            if (disabled) {
                $btn.prop('disabled', true).addClass('disabled');
            } else {
                $btn.prop('disabled', false).removeClass('disabled');
            }
        });
    }

    function toggleButtonBusy($button, busy) {
        if (!$button || !$button.length) {
            return;
        }
        var $spinner = $button.find('.spinner-border');
        var $label = $button.find('.label-text');
        if (busy) {
            $button.addClass('disabled').attr('disabled', 'disabled');
            $spinner.removeClass('d-none');
            $label.addClass('d-none');
        } else {
            $button.removeClass('disabled').removeAttr('disabled');
            $spinner.addClass('d-none');
            $label.removeClass('d-none');
        }
    }

    function getDefaultBatchDate($modal) {
        var $date = $modal.find('#groupDate');
        if (!$date.length) {
            return '';
        }
        return ($date.data('default') || $date.val() || '').trim();
    }

    function updateStepIndicator($modal, step) {
        $modal.find('[data-step-indicator]').each(function() {
            var indicatorStep = parseInt(this.getAttribute('data-step-indicator'), 10);
            var $item = $(this);
            var $badge = $item.find('.step-number');
            if (indicatorStep === step) {
                $item.addClass('active').removeClass('text-muted');
                $badge.removeClass('bg-light text-dark border').addClass('bg-primary text-white');
            } else {
                $item.removeClass('active').addClass('text-muted');
                $badge.removeClass('bg-primary text-white').addClass('bg-light text-dark border');
            }
        });
    }

    function setStep($modal, step) {
        var clampedStep = Math.max(1, Math.min(STEP_COUNT, step || 1));
        $modal.data('currentStep', clampedStep);

        $modal.find('.step-pane').each(function() {
            var paneStep = parseInt(this.getAttribute('data-step'), 10);
            this.classList.toggle('d-none', paneStep !== clampedStep);
        });

        var $prev = $modal.find('.btn-prev-step');
        var $next = $modal.find('.btn-next-step');
        var $save = $modal.find('.btn-save-group');

        if (clampedStep <= 1) {
            $prev.addClass('d-none');
            $next.removeClass('d-none');
            $save.addClass('d-none');
        } else if (clampedStep >= STEP_COUNT) {
            $prev.removeClass('d-none');
            $next.addClass('d-none');
            $save.removeClass('d-none');
        } else {
            $prev.removeClass('d-none');
            $next.removeClass('d-none');
            $save.addClass('d-none');
        }

        updateStepIndicator($modal, clampedStep);
        updateNextButtonState($modal);
    }

    function isStepOneComplete($modal) {
        var name = ($modal.find('#groupName').val() || '').trim();
        var typeValue = getChoiceValue($modal, 'groupType');
        var categoryValue = ($modal.find('#groupCategory').val() || '').trim();
        var domainsValue = ($modal.find('#groupDomains').val() || '').trim();

        if (name.length < 1 || name.length > 10) {
            return false;
        }

        if (!typeValue) {
            return false;
        }

        if (!categoryValue) {
            return false;
        }

        if (domainsValue.length === 0) {
            return false;
        }

        return true;
    }

    function updateNextButtonState($modal) {
        var $next = $modal.find('.btn-next-step');
        if (!$next.length) {
            return;
        }

        var currentStep = $modal.data('currentStep') || 1;
        if (currentStep !== 1) {
            $next.removeClass('disabled').removeAttr('disabled');
            return;
        }

        var enabled = isStepOneComplete($modal);
        if (enabled) {
            $next.removeClass('disabled').removeAttr('disabled');
        } else {
            $next.addClass('disabled').attr('disabled', 'disabled');
        }
    }

    function syncImageRenameState($modal) {
        var value = getChoiceValue($modal, 'imageLocalization').toLowerCase();
        var disabled = value !== 'on';
        if (disabled) {
            setChoiceValue($modal, 'imageRename', 'off');
        }
        setChoiceGroupDisabled($modal, 'imageRename', disabled);
    }

    function resetForm($modal) {
        var $form = $modal.find('#groupForm');
        if ($form.length && $form[0]) {
            $form[0].reset();
        }

        $modal.find('#formAction').val('create');
        $modal.find('#groupId').val('');

        var defaultDate = getDefaultBatchDate($modal);
        if (defaultDate) {
            $modal.find('#groupDate').val(defaultDate);
        }

        setChoiceValue($modal, 'groupType', 'single');
        $modal.find('#groupCategory').val('enterprise');
        $modal.find('#groupDomains').val('');
        $modal.find('#groupNote').val('');
        setChoiceValue($modal, 'redirectPolicy', 'off');
        setChoiceValue($modal, 'imageLocalization', 'off');
        setChoiceValue($modal, 'imageRename', 'off');
        $modal.find('#articleImageCount').val('1');
        setChoiceValue($modal, 'articleThumbnailFirst', 'on');
        $modal.find('#groupStatus').val('active');
        $modal.find('#statusField').hide();

        syncImageRenameState($modal);
        toggleButtonBusy($modal.find('.btn-save-group'), false);
        setStep($modal, 1);
        updateNextButtonState($modal);
    }

    function populateFormForEdit($modal, $row) {
        resetForm($modal);

        var $title = $modal.find('#groupModalTitle');
        var editTitle = $title.data('title-edit') || $title.text().trim();
        $title.text(editTitle);

        $modal.find('#formAction').val('update');
        $modal.find('#groupId').val(decodeAttr($row, 'data-group-id'));
        $modal.find('#groupDate').val(decodeAttr($row, 'data-batch-date'));
        $modal.find('#groupName').val(decodeAttr($row, 'data-group-name'));
    setChoiceValue($modal, 'groupType', decodeAttr($row, 'data-group-type') || 'single');
        $modal.find('#groupCategory').val(decodeAttr($row, 'data-group-category') || 'other');
        $modal.find('#groupNote').val(decodeAttr($row, 'data-group-note'));

        var templateMap = parseJSONAttribute($row, 'data-group-domain-templates') || {};
        var domainsList = extractDomains($row);
        $modal.find('#groupDomains').val(buildDomainTextareaValue(domainsList, templateMap));

        var statusValue = decodeAttr($row, 'data-group-status') || 'active';
        $modal.find('#groupStatus').val(statusValue);
        $modal.find('#statusField').show();

        var redirectPolicy = decodeAttr($row, 'data-group-redirect') || 'off';
    setChoiceValue($modal, 'redirectPolicy', redirectPolicy);

        var imageLocalization = (decodeAttr($row, 'data-group-image-localization') || 'off').toLowerCase() === 'on' ? 'on' : 'off';
    setChoiceValue($modal, 'imageLocalization', imageLocalization);

        var imageRename = (decodeAttr($row, 'data-group-image-rename') || 'off').toLowerCase() === 'on' ? 'on' : 'off';
    setChoiceValue($modal, 'imageRename', imageRename);

        var articleImageCount = parseInt(decodeAttr($row, 'data-group-article-image-count'), 10);
        if (!Number.isFinite(articleImageCount) || articleImageCount < 1) {
            articleImageCount = 1;
        }
        $modal.find('#articleImageCount').val(String(articleImageCount));

        var thumbnailFirst = (decodeAttr($row, 'data-group-article-thumbnail-first') || 'off').toLowerCase() === 'on' ? 'on' : 'off';
        setChoiceValue($modal, 'articleThumbnailFirst', thumbnailFirst);

        syncImageRenameState($modal);
        setStep($modal, 1);
        toggleButtonBusy($modal.find('.btn-save-group'), false);
        updateNextButtonState($modal);
    }

    function validateStep($modal, step) {
        var $form = $modal.find('#groupForm');
        if (!$form.length || !$form[0] || typeof $form[0].checkValidity !== 'function') {
            return true;
        }

        var isValid = true;
        $form.find('.step-pane').each(function() {
            var paneStep = parseInt(this.getAttribute('data-step'), 10);
            if (paneStep !== step) {
                return;
            }

            $(this).find('input, select, textarea').each(function() {
                if (typeof this.checkValidity === 'function' && !this.checkValidity()) {
                    if (typeof this.reportValidity === 'function') {
                        this.reportValidity();
                    }
                    isValid = false;
                    return false;
                }
            });

            if (!isValid) {
                return false;
            }
        });

        return isValid;
    }

    $('#searchGroups').on('keyup', function() {
        var value = ($(this).val() || '').toLowerCase();
        $('.searchItem').each(function() {
            var text = $(this).find('.searchText').text().toLowerCase();
            $(this).toggle(text.indexOf(value) > -1);
        });
    });

    $(document).on('click', '.btn-add-group', function(e) {
        e.preventDefault();

        var $modal = getGroupModal();
        if (!$modal.length) {
            return;
        }

        resetForm($modal);
        var $title = $modal.find('#groupModalTitle');
        var addTitle = $title.data('title-add') || $title.text().trim();
        $title.text(addTitle);

        var modalEl = $modal.get(0);
        if (modalEl) {
            var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            adjustModalStack(modalEl);
            modalInstance.show();
        }
    });

    $(document).on('click', '.btn-edit-group', function() {
        var $row = $(this).closest('tr');
        if (!$row.length) {
            return;
        }

        var $modal = getGroupModal();
        if (!$modal.length) {
            return;
        }

        populateFormForEdit($modal, $row);

        var modalEl = $modal.get(0);
        if (modalEl) {
            var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            adjustModalStack(modalEl);
            modalInstance.show();
        }
    });

    $(document).on('click', '.btn-delete-group', function() {
        var $button = $(this);
        var $row = $button.closest('tr');
        var groupId = decodeAttr($button, 'data-id') || decodeAttr($row, 'data-group-id');
        var date = decodeAttr($button, 'data-date') || decodeAttr($row, 'data-batch-date');
        var groupName = decodeAttr($button, 'data-name') || decodeAttr($row, 'data-group-name');

        if (typeof MaigwanModal !== 'undefined' && typeof MaigwanModal.showConfirm === 'function') {
            MaigwanModal.showConfirm(
                '确定要删除此分组吗？此操作不可恢复。<br><strong>' + groupName + '</strong>',
                function() {
                    $('#deleteGroupId').val(groupId);
                    $('#deleteGroupDate').val(date);
                    $('#deleteForm').trigger('submit');
                }
            );
            return;
        }

        var $deleteModal = getDeleteModal();
        $('#deleteGroupId').val(groupId);
        $('#deleteGroupDate').val(date);
        $('#deleteGroupName').text(groupName);
        toggleButtonBusy($deleteModal.find('.btn-delete-confirm'), false);

        var modalEl = $deleteModal.get(0);
        if (modalEl) {
            var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
        }
    });

    $(document).on('click', '#groupModal .btn-next-step', function() {
        var $modal = getGroupModal();
        var currentStep = $modal.data('currentStep') || 1;
        if (currentStep === 1 && !isStepOneComplete($modal)) {
            updateNextButtonState($modal);
            return;
        }
        if (!validateStep($modal, currentStep)) {
            return;
        }
        setStep($modal, currentStep + 1);
    });

    $(document).on('click', '#groupModal .btn-prev-step', function() {
        var $modal = getGroupModal();
        var currentStep = $modal.data('currentStep') || 1;
        setStep($modal, currentStep - 1);
    });

    $(document).on('click', '[data-choice-input]', function() {
        var $btn = $(this);
        if ($btn.prop('disabled')) {
            return;
        }

        var key = $btn.data('choiceInput');
        if (!key) {
            return;
        }

        var value = $btn.data('choiceValue');
        var $modal = $btn.closest('.modal');
        if (!$modal.length) {
            $modal = getGroupModal();
        }

        setChoiceValue($modal, key, value);

        if (key === 'imageLocalization') {
            syncImageRenameState($modal);
        }

        updateNextButtonState($modal);
    });

    $(document).on('input', '#groupModal #groupName', function() {
        updateNextButtonState(getGroupModal());
    });

    $(document).on('change', '#groupModal #groupCategory', function() {
        updateNextButtonState(getGroupModal());
    });

    $(document).on('input', '#groupModal #groupDomains', function() {
        updateNextButtonState(getGroupModal());
    });

    $(document).on('submit', '#groupForm', function() {
        var $modal = getGroupModal();
        toggleButtonBusy($modal.find('.btn-save-group'), true);
    });

    $(document).on('submit', '#deleteForm', function() {
        var $modal = getDeleteModal();
        toggleButtonBusy($modal.find('.btn-delete-confirm'), true);
    });

    getGroupModal().on('hidden.bs.modal', function() {
        var $modal = $(this);
        toggleButtonBusy($modal.find('.btn-save-group'), false);
        setStep($modal, 1);
    });

    getDeleteModal().on('hidden.bs.modal', function() {
        var $modal = $(this);
        toggleButtonBusy($modal.find('.btn-delete-confirm'), false);
    });

    if (typeof DEBUG_MODE !== 'undefined' && DEBUG_MODE) {
        console.log('[Site List] Script initialized successfully');
    }
});
