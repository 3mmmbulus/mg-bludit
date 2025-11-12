/**
 * Settings page functionality
 * Handles Select2 initialization and tab persistence
 */

(function() {
	'use strict';

	/**
	 * Initialize Select2 for homepage selector
	 */
	function initHomepageSelect() {
		const homepageSelect = $("#jshomepage");
		
		if (!homepageSelect.length) {
			return;
		}

		homepageSelect.select2({
			placeholder: homepageSelect.data('placeholder') || "Start typing to see a list of suggestions.",
			allowClear: true,
			theme: "bootstrap5",
			minimumInputLength: 2,
			ajax: {
				url: HTML_PATH_ADMIN_ROOT + "ajax/get-published",
				data: function(params) {
					return {
						query: params.term
					};
				},
				processResults: function(data) {
					return data;
				}
			},
			escapeMarkup: function(markup) {
				return markup;
			}
		});
	}

	/**
	 * Initialize Select2 for 404 page selector
	 */
	function initPageNotFoundSelect() {
		const pageNotFoundSelect = $("#jspageNotFound");
		
		if (!pageNotFoundSelect.length) {
			return;
		}

		pageNotFoundSelect.select2({
			placeholder: pageNotFoundSelect.data('placeholder') || "Start typing to see a list of suggestions.",
			allowClear: true,
			theme: "bootstrap5",
			minimumInputLength: 2,
			ajax: {
				url: HTML_PATH_ADMIN_ROOT + "ajax/get-published",
				data: function(params) {
					return {
						query: params.term
					};
				},
				processResults: function(data) {
					return data;
				}
			},
			escapeMarkup: function(markup) {
				return markup;
			}
		});
	}

	/**
	 * Initialize Select2 for order-by selector
	 */
	function initOrderBySelect() {
		const orderBySelect = $("#jsorderBy");
		
		if (!orderBySelect.length) {
			return;
		}

		orderBySelect.select2({
			minimumResultsForSearch: Infinity,
			theme: "bootstrap5"
		});
	}

	/**
	 * Initialize Select2 for URL filters selector
	 */
	function initUrlFiltersSelect() {
		const urlFiltersSelect = $("#jsurlFilters");
		
		if (!urlFiltersSelect.length) {
			return;
		}

		urlFiltersSelect.select2({
			minimumResultsForSearch: Infinity,
			theme: "bootstrap5"
		});
	}

	/**
	 * Initialize Select2 for items per page selector
	 */
	function initItemsPerPageSelect() {
		const itemsPerPageSelect = $("#jsitemsPerPage");
		
		if (!itemsPerPageSelect.length) {
			return;
		}

		itemsPerPageSelect.select2({
			minimumResultsForSearch: Infinity,
			theme: "bootstrap5"
		});
	}

	/**
	 * Initialize tab persistence using localStorage
	 */
	function initTabPersistence() {
		const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
		
		if (!tabLinks.length) {
			return;
		}

		// Save active tab on click
		tabLinks.forEach(function(tabEl) {
			tabEl.addEventListener('click', function(e) {
				const href = e.target.getAttribute('href');
				if (href) {
					window.localStorage.setItem('settingsActiveTab', href);
				}
			});
		});
		
		// Restore active tab on page load
		const activeTab = window.localStorage.getItem('settingsActiveTab');
		if (activeTab) {
			const tabElement = document.querySelector('#nav-tab a[href="' + activeTab + '"]');
			if (tabElement && typeof bootstrap !== 'undefined') {
				const tab = new bootstrap.Tab(tabElement);
				tab.show();
			}
		}
	}

	/**
	 * Initialize URL filter input behavior
	 */
	function initUrlFilterInput() {
		const urlFiltersSelect = $("#jsurlFilters");
		const urlFiltersInput = $("#jsurlFilter");
		
		if (!urlFiltersSelect.length || !urlFiltersInput.length) {
			return;
		}

		// Show/hide custom filter input based on selection
		urlFiltersSelect.on('change', function() {
			if ($(this).val() === 'custom') {
				urlFiltersInput.parent().show();
			} else {
				urlFiltersInput.parent().hide();
			}
		});

		// Trigger on load
		urlFiltersSelect.trigger('change');
	}

	/**
	 * Initialize all settings page features
	 */
	function init() {
		// 检查必要的依赖是否加载
		if (typeof $ === 'undefined') {
			console.error('jQuery not loaded, retrying settings initialization...');
			setTimeout(init, 100);
			return;
		}
		
		if (typeof $.fn.select2 === 'undefined') {
			console.error('Select2 not loaded, retrying settings initialization...');
			setTimeout(init, 100);
			return;
		}
		
		// 所有依赖已加载，执行初始化
		initHomepageSelect();
		initPageNotFoundSelect();
		initOrderBySelect();
		initUrlFiltersSelect();
		initItemsPerPageSelect();
		initUrlFilterInput();
		initTabPersistence();
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})();
