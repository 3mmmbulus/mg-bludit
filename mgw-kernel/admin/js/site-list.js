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

$(document).ready(function() {
    var $groupModal = $('#groupModal');
    var $groupModalTitle = $('#groupModalTitle');
    var $groupForm = $('#groupForm');
    var $groupDate = $('#groupDate');
    var defaultBatchDate = $groupDate.val();
    var $deleteModal = $('#deleteModal');
    var $deleteForm = $('#deleteForm');

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
        var decoded = $('<textarea/>').html(raw).text();
        return decoded.trim();
    }

    function extractDomains($row) {
        var decoded = decodeAttr($row, 'data-group-domains');
        if (!decoded) {
            return [];
        }
        try {
            var parsed = JSON.parse(decoded);
            if (Array.isArray(parsed)) {
                return parsed;
            }
        } catch (error) {
            // 忽略解析错误，使用回退逻辑
        }
        return decoded.split(/\r?\n|,/).map(function(item) {
            return item.trim();
        }).filter(function(item) {
            return item.length > 0;
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

    // 搜索过滤
    $('#searchGroups').on('keyup', function() {
        var value = ($(this).val() || '').toLowerCase();
        $('.searchItem').each(function() {
            var text = $(this).find('.searchText').text().toLowerCase();
            $(this).toggle(text.indexOf(value) > -1);
        });
    });

    // 新建分组
    $('.btn-add-group').on('click', function(e) {
        e.preventDefault();

        var addTitle = $groupModalTitle.data('title-add') || $(this).text().trim();
        $groupModalTitle.text(addTitle);

        if ($groupForm.length) {
            $groupForm[0].reset();
        }

        $('#formAction').val('create');
        $('#groupId').val('');
        $groupDate.val(defaultBatchDate);
        $('#groupStatus').val('active');
        $('#statusField').hide();
        toggleButtonBusy($groupForm.find('.btn-save-group'), false);

        var modalEl = $groupModal.get(0);
        if (modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            adjustModalStack(modalEl);
            modal.show();
        }
    });

    // 编辑分组
    $('.btn-edit-group').on('click', function() {
        var $row = $(this).closest('tr');
        if (!$row.length) {
            return;
        }

        var editTitle = $groupModalTitle.data('title-edit') || $(this).attr('title') || $groupModalTitle.text();
        $groupModalTitle.text(editTitle);

        $('#formAction').val('update');
        $('#groupId').val(decodeAttr($row, 'data-group-id'));
        $groupDate.val(decodeAttr($row, 'data-batch-date'));
        $('#groupName').val(decodeAttr($row, 'data-group-name'));
        $('#groupType').val(decodeAttr($row, 'data-group-type'));
        $('#groupMode').val(decodeAttr($row, 'data-group-mode') || 'independent');
        $('#groupNote').val(decodeAttr($row, 'data-group-note'));
        $('#groupStatus').val(decodeAttr($row, 'data-group-status') || 'active');

        var domainsList = extractDomains($row);
        $('#groupDomains').val(domainsList.join('\n'));
        $('#statusField').show();
        toggleButtonBusy($groupForm.find('.btn-save-group'), false);

        var modalEl = $groupModal.get(0);
        if (modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            adjustModalStack(modalEl);
            modal.show();
        }
    });

    // 删除分组
    $('.btn-delete-group').on('click', function() {
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

        $('#deleteGroupId').val(groupId);
        $('#deleteGroupDate').val(date);
        $('#deleteGroupName').text(groupName);
        toggleButtonBusy($deleteForm.find('.btn-delete-confirm'), false);

        var modalEl = $deleteModal.get(0);
        if (modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });

    // 表单提交状态
    $groupForm.on('submit', function() {
        toggleButtonBusy($(this).find('.btn-save-group'), true);
    });

    $deleteForm.on('submit', function() {
        toggleButtonBusy($(this).find('.btn-delete-confirm'), true);
    });

    $groupModal.on('hidden.bs.modal', function() {
        toggleButtonBusy($(this).find('.btn-save-group'), false);
    });

    $deleteModal.on('hidden.bs.modal', function() {
        toggleButtonBusy($(this).find('.btn-delete-confirm'), false);
    });

    if (typeof DEBUG_MODE !== 'undefined' && DEBUG_MODE) {
        console.log('[Site List] Script initialized successfully');
    }
});
