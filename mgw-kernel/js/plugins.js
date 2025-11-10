/**
 * Plugins page functionality
 * Handles real-time plugin search/filter
 */

(function() {
	'use strict';

	/**
	 * Initialize plugin search functionality
	 */
	function initPluginSearch() {
		const searchInput = $("#search");
		
		if (!searchInput.length) {
			return;
		}

		searchInput.on("keyup", function() {
			const searchText = $(this).val().toLowerCase();
			
			$(".searchItem").each(function() {
				const item = $(this);
				let found = false;

				// Search in all searchText elements within this item
				item.find(".searchText").each(function() {
					const text = $(this).text().toLowerCase();
					if (text.indexOf(searchText) !== -1) {
						found = true;
						return false; // Break the loop
					}
				});

				// Show or hide the item based on search result
				if (found || searchText === "") {
					item.show();
				} else {
					item.hide();
				}
			});
		});
	}

	/**
	 * Initialize all plugins page features
	 */
	function init() {
		initPluginSearch();
	}

	// Initialize when DOM is ready
	$(document).ready(init);

})();
