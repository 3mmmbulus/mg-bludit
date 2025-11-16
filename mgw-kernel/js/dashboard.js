/**
 * Dashboard Page Script
 * Handles greeting animation, quick search configuration, and lightweight data visualisations
 */
(function ($) {
	'use strict';

	function decodeHtmlEntities(raw) {
		var textarea = document.createElement('textarea');
		textarea.innerHTML = raw;
		return textarea.value;
	}

	function initWelcomeMessage() {
		var helloMessage = $('#dashboard-hello');
		if (!helloMessage.length) {
			return;
		}

		var userName = helloMessage.data('username') || '';
		var date = new Date();
		var hours = date.getHours();
		var greeting = '';
		var icon = '';

		if (hours >= 6 && hours < 12) {
			icon = '<span class="bi bi-sun"></span>';
			greeting = helloMessage.data('morning') || 'Good morning';
		} else if (hours >= 12 && hours < 18) {
			icon = '<span class="bi bi-sun"></span>';
			greeting = helloMessage.data('afternoon') || 'Good afternoon';
		} else if (hours >= 18 && hours < 22) {
			icon = '<span class="bi bi-moon"></span>';
			greeting = helloMessage.data('evening') || 'Good evening';
		} else {
			icon = '<span class="bi bi-moon"></span>';
			greeting = helloMessage.data('night') || 'Good night';
		}

		helloMessage.html(icon + '<span>' + greeting + (userName ? ', ' + userName : '') + '</span>');
	}

	function initClippySearch() {
		var clippyElement = $('#jsclippy');
		if (!clippyElement.length || typeof clippyElement.select2 !== 'function') {
			return;
		}

		var dropdownContainer = clippyElement.closest('.card-body');

		clippyElement.select2({
			placeholder: clippyElement.data('placeholder') || 'Start typing to see a list of suggestions',
			allowClear: true,
			width: '100%',
			theme: 'bootstrap5',
			minimumInputLength: 2,
			dropdownParent: dropdownContainer.length ? dropdownContainer : $(document.body),
			language: {
				inputTooShort: function () {
					return '';
				}
			},
			ajax: {
				url: HTML_PATH_ADMIN_ROOT + 'ajax/clippy',
				data: function (params) {
					return {
						query: params.term
					};
				},
				processResults: function (data) {
					return data;
				}
			},
			templateResult: function (data) {
				var html = '';

				if (data.type === 'menu') {
					html += '<a href="' + data.url + '"><div class="search-suggestion">';
					html += '<span class="bi bi-' + data.icon + '"></span>' + data.text + '</div></a>';
				} else {
					if (typeof data.id === 'undefined') {
						return '';
					}

					var viewLabel = clippyElement.data('view-label') || 'view';
					var editLabel = clippyElement.data('edit-label') || 'edit';

					html += '<div class="search-suggestion">';
					html += '<div class="search-suggestion-item">' + data.text + ' <span class="badge bg-light text-muted border">' + data.type + '</span></div>';
					html += '<div class="search-suggestion-options">';
					html += '<a target="_blank" href="' + DOMAIN_PAGES + data.id + '">' + viewLabel + '</a>';
					html += '<a class="ms-2" href="' + DOMAIN_ADMIN + 'edit-content/' + data.id + '">' + editLabel + '</a>';
					html += '</div></div>';
				}

				return html;
			},
			escapeMarkup: function (markup) {
				return markup;
			}
		});
	}

	function renderCertificateProgress() {
		$('[data-dashboard-cert-progress]').each(function () {
			var container = $(this);
			var segments = {
				valid: Math.max(0, Number(container.data('valid')) || 0),
				expiring: Math.max(0, Number(container.data('expiring')) || 0),
				invalid: Math.max(0, Number(container.data('invalid')) || 0)
			};

			var total = segments.valid + segments.expiring + segments.invalid;
			if (total <= 0) {
				total = 1;
			}

			Object.keys(segments).forEach(function (key) {
				var ratio = segments[key] / total;
				var width = Math.max(0, Math.min(100, ratio * 100));
				container.find('[data-progress-bar="' + key + '"]').css('width', width + '%');
			});
		});
	}

	function parseChartDataset(element) {
		var raw = element.attr('data-chart-points');
		if (!raw) {
			return [];
		}

		try {
			var decoded = decodeHtmlEntities(raw.trim());
			return JSON.parse(decoded);
		} catch (error) {
			console.warn('Dashboard chart dataset parse error', error);
			return [];
		}
	}

	function buildStackedBarChart(element, dataset) {
		if (!dataset.length) {
			return;
		}

		var wrapper = $('<div class="dashboard-chart-bars"></div>');
		var maxTotal = 0;

		dataset.forEach(function (point) {
			var total = (Number(point.valid) || 0) + (Number(point.expiring) || 0) + (Number(point.invalid) || 0);
			if (total > maxTotal) {
				maxTotal = total;
			}
		});

		if (maxTotal <= 0) {
			maxTotal = 1;
		}

		dataset.forEach(function (point) {
			var label = point.label || '';
			var valid = Math.max(0, Number(point.valid) || 0);
			var expiring = Math.max(0, Number(point.expiring) || 0);
			var invalid = Math.max(0, Number(point.invalid) || 0);
			var total = valid + expiring + invalid;

			var bar = $('<div class="dashboard-chart-bar"></div>');
			var track = $('<div class="dashboard-chart-bar-track"></div>');

			var scale = total > 0 ? (total / maxTotal) : 0;
			var validHeight = total > 0 ? (valid / total) * scale * 100 : 0;
			var expiringHeight = total > 0 ? (expiring / total) * scale * 100 : 0;
			var invalidHeight = total > 0 ? (invalid / total) * scale * 100 : 0;

			track.append($('<span class="segment segment-valid"></span>').css('height', validHeight + '%'));
			track.append($('<span class="segment segment-expiring"></span>').css('height', expiringHeight + '%'));
			track.append($('<span class="segment segment-invalid"></span>').css('height', invalidHeight + '%'));

			bar.append(track);
			bar.append($('<span class="dashboard-chart-label"></span>').text(label));
			wrapper.append(bar);
		});

		element.empty().append(wrapper);
	}

	function buildSingleBarChart(element, dataset) {
		if (!dataset.length) {
			return;
		}

		var wrapper = $('<div class="dashboard-chart-bars"></div>');
		var maxValue = 0;

		dataset.forEach(function (point) {
			var value = Number(point.value) || 0;
			if (value > maxValue) {
				maxValue = value;
			}
		});

		if (maxValue <= 0) {
			maxValue = 1;
		}

		dataset.forEach(function (point) {
			var label = point.label || '';
			var value = Math.max(0, Number(point.value) || 0);
			var scale = (value / maxValue) * 100;

			var bar = $('<div class="dashboard-chart-bar"></div>');
			var track = $('<div class="dashboard-chart-bar-track"></div>');

			track.append($('<span class="segment segment-single"></span>').css('height', scale + '%'));
			bar.append(track);
			bar.append($('<span class="dashboard-chart-label"></span>').text(label));
			wrapper.append(bar);
		});

		element.empty().append(wrapper);
	}

	function renderCharts() {
		$('[data-dashboard-chart]').each(function () {
			var element = $(this);
			var chartType = element.data('chart-type') || 'stacked-bar';
			var dataset = parseChartDataset(element);

			if (!dataset.length) {
				return;
			}

			if (chartType === 'stacked-bar') {
				buildStackedBarChart(element, dataset);
			} else {
				buildSingleBarChart(element, dataset);
			}
		});
	}

	$(function () {
		initWelcomeMessage();
		initClippySearch();
		renderCertificateProgress();
		renderCharts();
	});

})(jQuery);
