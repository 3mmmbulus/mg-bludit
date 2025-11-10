/**
 * Dashboard Page Script
 * Handles welcome message animation and Clippy search functionality
 */
(function() {
	'use strict';

	/**
	 * Initialize welcome message with time-based greeting
	 * Fades out default message and shows time-appropriate greeting
	 */
	function initWelcomeMessage() {
		var helloMessage = $('#hello-message');
		if (!helloMessage.length) return;

		// Get user name from data attribute or default
		var userName = helloMessage.data('username') || '';

		helloMessage.fadeOut(2400, function() {
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

			$(this).html(icon + '<span>' + greeting + (userName ? ', ' + userName : '') + '</span>');
		}).fadeIn(1000);
	}

	/**
	 * Initialize Clippy search with Select2
	 * Provides quick access to admin pages and content search
	 */
	function initClippySearch() {
		var clippyElement = $('#jsclippy');
		if (!clippyElement.length) return;

		var clippy = clippyElement.select2({
			placeholder: clippyElement.data('placeholder') || 'Start typing to see a list of suggestions',
			allowClear: true,
			width: '100%',
			theme: 'bootstrap5',
			minimumInputLength: 2,
			dropdownParent: '#jsclippyContainer',
			language: {
				inputTooShort: function() {
					return '';
				}
			},
			ajax: {
				url: HTML_PATH_ADMIN_ROOT + 'ajax/clippy',
				data: function(params) {
					return {
						query: params.term
					};
				},
				processResults: function(data) {
					return data;
				}
			},
			templateResult: function(data) {
				var html = '';

				if (data.type === 'menu') {
					// Menu item
					html += '<a href="' + data.url + '"><div class="search-suggestion">';
					html += '<span class="bi bi-' + data.icon + '"></span>' + data.text + '</div></a>';
				} else {
					// Content item
					if (typeof data.id === 'undefined') {
						return '';
					}

					var viewLabel = clippyElement.data('view-label') || 'view';
					var editLabel = clippyElement.data('edit-label') || 'edit';

					html += '<div class="search-suggestion">';
					html += '<div class="search-suggestion-item">' + data.text + ' <span class="badge badge-pill badge-light">' + data.type + '</span></div>';
					html += '<div class="search-suggestion-options">';
					html += '<a target="_blank" href="' + DOMAIN_PAGES + data.id + '">' + viewLabel + '</a>';
					html += '<a class="ml-2" href="' + DOMAIN_ADMIN + 'edit-content/' + data.id + '">' + editLabel + '</a>';
					html += '</div></div>';
				}

				return html;
			},
			escapeMarkup: function(markup) {
				return markup;
			}
		}).on('select2:closing', function(e) {
			e.preventDefault();
		}).on('select2:closed', function(e) {
			clippy.select2('open');
		});

		// Auto-open on page load
		clippy.select2('open');
	}

	// Initialize on DOM ready
	$(document).ready(function() {
		initWelcomeMessage();
		initClippySearch();
	});

})();
