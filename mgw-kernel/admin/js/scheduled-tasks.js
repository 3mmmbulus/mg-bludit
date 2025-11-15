document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var root = document.querySelector('[data-scheduled-root]');
    if (!root) {
        return;
    }

    var parseData = function (value, fallback) {
        if (!value) {
            return fallback;
        }
        try {
            return JSON.parse(value);
        } catch (error) {
            console.warn('Unable to parse scheduler dataset', error);
            return fallback;
        }
    };

    var schemas = parseData(root.getAttribute('data-task-schemas'), {});
    var actions = parseData(root.getAttribute('data-task-actions'), []);
    var statuses = parseData(root.getAttribute('data-task-statuses'), {});
    var priorities = parseData(root.getAttribute('data-task-priorities'), {});
    var logs = parseData(root.getAttribute('data-task-logs'), []);
    var cronPresets = parseData(root.getAttribute('data-cron-presets'), []);
    var sites = parseData(root.getAttribute('data-task-sites'), []);
    var categories = parseData(root.getAttribute('data-task-categories'), {});
    var triggers = parseData(root.getAttribute('data-task-triggers'), {});
    var retryBackoff = parseData(root.getAttribute('data-retry-backoff'), {});
    var translations = parseData(root.getAttribute('data-translations'), {});

    var table = root.querySelector('[data-task-table]');
    var taskRows = table ? Array.from(table.querySelectorAll('[data-task-row]')) : [];
    var emptyRow = table ? table.querySelector('[data-task-empty]') : null;
    var countNode = root.querySelector('[data-task-count]');

    var searchInput = root.querySelector('[data-filter-search]');
    var statusSelect = root.querySelector('[data-filter-status]');
    var typeSelect = root.querySelector('[data-filter-type]');
    var siteSelect = root.querySelector('[data-filter-site]');
    var resetButton = root.querySelector('[data-filter-reset]');
    var categoryLinks = root.querySelectorAll('[data-task-category-link]');

    var currentCategory = 'all';
    root.setAttribute('data-active-category', currentCategory);

    var applyTaskFilters = function () {
        var term = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var statusValue = statusSelect ? statusSelect.value : '';
        var typeValue = typeSelect ? typeSelect.value : '';
        var siteValue = siteSelect ? siteSelect.value : '';
        var visibleCount = 0;

        taskRows.forEach(function (row) {
            var matches = true;
            if (term) {
                var text = row.textContent || '';
                if (text.toLowerCase().indexOf(term) === -1) {
                    matches = false;
                }
            }
            if (matches && statusValue) {
                matches = row.getAttribute('data-task-status') === statusValue;
            }
            if (matches && typeValue) {
                matches = row.getAttribute('data-task-category') === typeValue;
            }
            if (matches && siteValue) {
                matches = row.getAttribute('data-task-site') === siteValue;
            }
            if (matches && currentCategory !== 'all') {
                matches = row.getAttribute('data-task-category') === currentCategory;
            }
            row.classList.toggle('d-none', !matches);
            if (matches) {
                visibleCount += 1;
            }
        });

        if (emptyRow) {
            emptyRow.classList.toggle('d-none', visibleCount > 0);
        }
        if (countNode) {
            countNode.textContent = visibleCount;
        }
    };

    if (searchInput) {
        searchInput.addEventListener('input', applyTaskFilters);
    }
    if (statusSelect) {
        statusSelect.addEventListener('change', applyTaskFilters);
    }
    if (typeSelect) {
        typeSelect.addEventListener('change', applyTaskFilters);
    }
    if (siteSelect) {
        siteSelect.addEventListener('change', applyTaskFilters);
    }
    if (resetButton) {
        resetButton.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = '';
            }
            if (statusSelect) {
                statusSelect.value = '';
            }
            if (typeSelect) {
                typeSelect.value = '';
            }
            if (siteSelect) {
                siteSelect.value = '';
            }
            currentCategory = 'all';
            root.setAttribute('data-active-category', currentCategory);
            categoryLinks.forEach(function (button) {
                button.classList.toggle('active', button.getAttribute('data-task-category') === 'all');
            });
            applyTaskFilters();
        });
    }
    categoryLinks.forEach(function (button) {
        button.addEventListener('click', function () {
            categoryLinks.forEach(function (item) {
                item.classList.remove('active');
            });
            button.classList.add('active');
            currentCategory = button.getAttribute('data-task-category') || 'all';
            root.setAttribute('data-active-category', currentCategory);
            applyTaskFilters();
        });
    });

    applyTaskFilters();

    var logsTable = root.querySelector('[data-log-table]');
    var logRows = logsTable ? Array.from(logsTable.querySelectorAll('[data-log-row]')) : [];
    var logEmpty = logsTable ? logsTable.querySelector('[data-log-empty]') : null;
    var logSearch = root.querySelector('[data-log-search]');
    var logStatus = root.querySelector('[data-log-status]');
    var logCountNode = root.querySelector('[data-log-count]');

    var applyLogFilters = function () {
        var term = logSearch ? logSearch.value.trim().toLowerCase() : '';
        var statusValue = logStatus ? logStatus.value : '';
        var visible = 0;
        logRows.forEach(function (row) {
            var show = true;
            if (term) {
                var text = row.textContent || '';
                if (text.toLowerCase().indexOf(term) === -1) {
                    show = false;
                }
            }
            if (show && statusValue) {
                show = row.getAttribute('data-log-status') === statusValue;
            }
            row.classList.toggle('d-none', !show);
            if (show) {
                visible += 1;
            }
        });
        if (logEmpty) {
            logEmpty.classList.toggle('d-none', visible > 0);
        }
        if (logCountNode) {
            logCountNode.textContent = visible;
        }
    };

    if (logSearch) {
        logSearch.addEventListener('input', applyLogFilters);
    }
    if (logStatus) {
        logStatus.addEventListener('change', applyLogFilters);
    }

    applyLogFilters();

    var offcanvasElement = document.getElementById('mgwScheduledLogsOffcanvas');
    var offcanvasList = offcanvasElement ? offcanvasElement.querySelector('[data-log-offcanvas-list]') : null;
    var offcanvasEmpty = offcanvasElement ? offcanvasElement.querySelector('[data-log-offcanvas-empty]') : null;
    var offcanvasTitle = offcanvasElement ? offcanvasElement.querySelector('[data-log-offcanvas-title]') : null;
    var offcanvasMeta = offcanvasElement ? offcanvasElement.querySelector('[data-log-offcanvas-meta]') : null;

    var renderTaskParameters = function (type) {
        var wrapper = document.querySelector('[data-task-parameters-list]');
        var emptyState = document.querySelector('[data-task-parameters-empty]');
        if (!wrapper) {
            return;
        }
        wrapper.innerHTML = '';
        var schema = schemas[type] || [];
        if (!schema.length) {
            if (emptyState) {
                emptyState.classList.remove('d-none');
            }
            return;
        }
        if (emptyState) {
            emptyState.classList.add('d-none');
        }
        schema.forEach(function (field) {
            var group = document.createElement('div');
            group.className = 'mb-3';
            var fieldId = 'mgw-task-param-' + field.name;
            var label = document.createElement('label');
            label.className = 'form-label';
            label.setAttribute('for', fieldId);
            label.textContent = field.label || field.name;
            if (field.type === 'checkbox') {
                group = document.createElement('div');
                group.className = 'form-check mb-3';
                var input = document.createElement('input');
                input.type = 'checkbox';
                input.className = 'form-check-input';
                input.id = fieldId;
                input.name = 'parameter[' + field.name + ']';
                if (field.default) {
                    input.checked = true;
                }
                var checkboxLabel = document.createElement('label');
                checkboxLabel.className = 'form-check-label';
                checkboxLabel.setAttribute('for', fieldId);
                checkboxLabel.textContent = field.label || field.name;
                group.appendChild(input);
                group.appendChild(checkboxLabel);
                if (field.help) {
                    var help = document.createElement('div');
                    help.className = 'form-text';
                    help.textContent = field.help;
                    group.appendChild(help);
                }
                wrapper.appendChild(group);
                return;
            }
            var input;
            if (field.type === 'select') {
                input = document.createElement('select');
                input.className = 'form-select';
                (field.options || []).forEach(function (option) {
                    var opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.label || option.value;
                    input.appendChild(opt);
                });
            } else if (field.type === 'textarea') {
                input = document.createElement('textarea');
                input.className = 'form-control';
                input.rows = field.rows || 4;
            } else {
                input = document.createElement('input');
                input.type = field.type || 'text';
                input.className = 'form-control';
            }
            input.id = fieldId;
            input.name = 'parameter[' + field.name + ']';
            if (field.placeholder) {
                input.placeholder = field.placeholder;
            }
            if (typeof field.default !== 'undefined' && field.type !== 'checkbox') {
                input.value = field.default;
            }
            group.appendChild(label);
            group.appendChild(input);
            if (field.help) {
                var helpText = document.createElement('div');
                helpText.className = 'form-text';
                helpText.textContent = field.help;
                group.appendChild(helpText);
            }
            wrapper.appendChild(group);
        });
    };

    var taskTypeSelect = document.getElementById('mgw-task-type');
    if (taskTypeSelect) {
        renderTaskParameters(taskTypeSelect.value);
        taskTypeSelect.addEventListener('change', function () {
            renderTaskParameters(taskTypeSelect.value);
        });
    }

    var cronPresetButtons = root.querySelectorAll('[data-cron-preset]');
    var cronInput = document.querySelector('[data-task-input="cron"]');
    cronPresetButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var value = button.getAttribute('data-cron-preset');
            if (cronInput) {
                cronInput.value = value;
                cronInput.dispatchEvent(new Event('input'));
            }
            cronPresetButtons.forEach(function (item) {
                item.classList.remove('active');
            });
            button.classList.add('active');
        });
    });

    root.addEventListener('click', function (event) {
        var target = event.target.closest('[data-task-action]');
        if (!target) {
            return;
        }
        var action = target.getAttribute('data-task-action');
        var row = target.closest('[data-task-row]');
        if (!row) {
            return;
        }
        var taskId = row.getAttribute('data-task-id');
        if (action === 'logs') {
            if (!offcanvasElement || !offcanvasList) {
                return;
            }
            offcanvasList.innerHTML = '';
            var taskLogs = logs.filter(function (entry) {
                return entry.taskId === taskId;
            }).sort(function (a, b) {
                return (b.startedAt || 0) - (a.startedAt || 0);
            });
            if (offcanvasTitle) {
                var nameNode = row.querySelector('[data-task-name]');
                offcanvasTitle.textContent = nameNode ? nameNode.textContent : taskId;
            }
            if (offcanvasMeta) {
                var metaParts = ['#' + taskId, taskLogs.length + ' ' + mgwScheduledLang('scheduled-tasks-log-run-count', 'executions')];
                if (taskLogs.length && taskLogs[0].startedAt) {
                    metaParts.push(mgwScheduledLang('scheduled-tasks-log-last-execution', 'Last run') + ': ' + mgwScheduledFormatDate(taskLogs[0].startedAt));
                }
                offcanvasMeta.textContent = metaParts.join(' · ');
            }
            if (taskLogs.length === 0) {
                if (offcanvasEmpty) {
                    offcanvasEmpty.classList.remove('d-none');
                }
            } else {
                if (offcanvasEmpty) {
                    offcanvasEmpty.classList.add('d-none');
                }
                taskLogs.forEach(function (entry) {
                    var item = document.createElement('div');
                    item.className = 'list-group-item';
                    var header = document.createElement('div');
                    header.className = 'd-flex justify-content-between align-items-center mb-1';
                    var when = document.createElement('span');
                    when.className = 'fw-semibold';
                    when.textContent = mgwScheduledFormatDate(entry.startedAt);
                    var statusBadge = document.createElement('span');
                    var statusMeta = statuses[entry.status] || { label: entry.status, badge: 'secondary' };
                    statusBadge.className = 'badge text-bg-' + statusMeta.badge;
                    statusBadge.textContent = statusMeta.label;
                    header.appendChild(when);
                    header.appendChild(statusBadge);
                    var body = document.createElement('div');
                    body.className = 'small text-muted';
                    body.textContent = entry.logExcerpt || '';
                    var footer = document.createElement('div');
                    footer.className = 'mt-1 small';
                    var durationLabel = document.createElement('span');
                    durationLabel.textContent = mgwScheduledLang('scheduled-tasks-duration-label', 'Duration') + ': ' + formatDuration(entry.duration);
                    footer.appendChild(durationLabel);
                    item.appendChild(header);
                    item.appendChild(body);
                    item.appendChild(footer);
                    offcanvasList.appendChild(item);
                });
            }
            if (typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
                var offcanvasInstance = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement);
                offcanvasInstance.show();
            }
            return;
        }
        console.info('Scheduler action requested', action, taskId);
    });

    var formatDuration = function (value) {
        if (value === null || value === undefined) {
            return '--';
        }
        value = parseInt(value, 10);
        if (isNaN(value) || value <= 0) {
            return '--';
        }
        if (value < 60) {
            return value + ' ' + mgwScheduledLang('scheduled-tasks-unit-seconds', 's');
        }
        var minutes = Math.floor(value / 60);
        var seconds = value % 60;
        var label = minutes + ' ' + mgwScheduledLang('scheduled-tasks-unit-minutes', 'min');
        if (seconds > 0) {
            label += ' ' + seconds + ' ' + mgwScheduledLang('scheduled-tasks-unit-seconds', 's');
        }
        return label;
    };

    var mgwScheduledLang = function (key, fallback) {
        if (key && Object.prototype.hasOwnProperty.call(translations, key)) {
            return translations[key];
        }
        return typeof fallback !== 'undefined' ? fallback : '';
    };

    var mgwScheduledFormatDate = function (timestamp) {
        if (!timestamp) {
            return '--';
        }
        var date = new Date(timestamp * 1000);
        if (isNaN(date.getTime())) {
            return '--';
        }
        return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0') + ' ' + String(date.getHours()).padStart(2, '0') + ':' + String(date.getMinutes()).padStart(2, '0');
    };
});
