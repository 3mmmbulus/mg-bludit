(function (global) {
    'use strict';

    function createContainer(html) {
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        return wrapper.firstChild;
    }

    function applyStacking(modalEl) {
        if (!modalEl) {
            return;
        }

        if (modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }

        var update = function () {
            var backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function (backdrop, index) {
                backdrop.style.zIndex = String(1990 + index);
                backdrop.style.removeProperty('display');
            });

            modalEl.style.zIndex = modalEl.classList.contains('show') ? '2000' : '';

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

        var ensureFocusAndAria = function () {
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
            modalEl.addEventListener('shown.bs.modal', function () {
                update();
                ensureFocusAndAria();
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                modalEl.removeAttribute('aria-modal');

                if (!document.querySelector('.modal.show')) {
                    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
                        if (backdrop.parentNode) {
                            backdrop.parentNode.removeChild(backdrop);
                        }
                    });
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                }
            });

            modalEl.dataset.mgwStackBound = '1';
        }

        setTimeout(update, 0);
    }

    function showModal(options) {
        var defaults = {
            id: 'maigwanModal-' + Date.now(),
            title: '',
            message: '',
            confirmText: 'OK',
            cancelText: 'Cancel',
            showCancel: false,
            confirmClass: 'btn-primary',
            cancelClass: 'btn-secondary',
            backdrop: 'static',
            keyboard: true,
            onConfirm: null,
            onCancel: null
        };

        var config = Object.assign({}, defaults, options || {});
        var template = '' +
            '<div class="modal fade" tabindex="-1" id="' + config.id + '">' +
            '  <div class="modal-dialog">' +
            '    <div class="modal-content">' +
            '      <div class="modal-header">' +
            '        <h5 class="modal-title">' + config.title + '</h5>' +
            '        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
            '      </div>' +
            '      <div class="modal-body">' + config.message + '</div>' +
            '      <div class="modal-footer">' +
            (config.showCancel ? '        <button type="button" class="btn ' + config.cancelClass + '" data-role="cancel">' + config.cancelText + '</button>' : '') +
            '        <button type="button" class="btn ' + config.confirmClass + '" data-role="confirm">' + config.confirmText + '</button>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '</div>';

        var modalEl = createContainer(template);
        document.body.appendChild(modalEl);

        var instance = new bootstrap.Modal(modalEl, {
            backdrop: config.backdrop,
            keyboard: config.keyboard
        });

        var confirmed = false;
        var confirmButton = modalEl.querySelector('[data-role="confirm"]');
        if (confirmButton) {
            confirmButton.addEventListener('click', function () {
                confirmed = true;
                if (typeof config.onConfirm === 'function') {
                    config.onConfirm();
                }
                instance.hide();
            });
        }

        var cancelButton = modalEl.querySelector('[data-role="cancel"]');
        if (cancelButton) {
            cancelButton.addEventListener('click', function () {
                if (!confirmed && typeof config.onCancel === 'function') {
                    config.onCancel();
                }
                instance.hide();
            });
        }

        modalEl.addEventListener('hidden.bs.modal', function () {
            if (!confirmed && typeof config.onCancel === 'function') {
                config.onCancel();
            }
            if (modalEl.parentNode) {
                modalEl.parentNode.removeChild(modalEl);
            }
        }, { once: true });

        applyStacking(modalEl);
        instance.show();
        return instance;
    }

    function showAlert(message, type, callback) {
        var styles = {
            info: { title: '提示', confirmClass: 'btn-primary' },
            success: { title: '成功', confirmClass: 'btn-success' },
            warning: { title: '警告', confirmClass: 'btn-warning' },
            error: { title: '错误', confirmClass: 'btn-danger' }
        };

        var setup = styles[type] || styles.info;

        return showModal({
            title: setup.title,
            message: message,
            confirmClass: setup.confirmClass,
            confirmText: '确定',
            showCancel: false,
            onConfirm: callback
        });
    }

    function showConfirm(message, onConfirm, onCancel) {
        return showModal({
            title: '确认操作',
            message: message,
            confirmText: '确定',
            cancelText: '取消',
            showCancel: true,
            onConfirm: onConfirm,
            onCancel: onCancel
        });
    }

    global.MaigwanModal = {
        showAlert: showAlert,
        showConfirm: showConfirm,
        showCustom: showModal,
        adjustStack: applyStacking
    };

})(window);
