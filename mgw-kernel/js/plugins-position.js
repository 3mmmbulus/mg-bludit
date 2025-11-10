/**
 * Plugins position page functionality
 * Handles drag-and-drop sorting of plugins
 */

(function() {
	'use strict';

	/**
	 * Initialize sortable plugin list
	 */
	function initSortable() {
		const sortableList = $('.list-group-sortable');
		
		if (!sortableList.length) {
			return;
		}

		sortableList.sortable({
			placeholderClass: 'list-group-item'
		});
	}

	/**
	 * Initialize save button handler
	 */
	function initSaveButton() {
		$(".jsbuttonSave").on("click", function() {
			const pluginOrder = [];
			
			// Collect plugin order from DOM
			$("li.list-group-item").each(function() {
				const pluginClass = $(this).attr("data-plugin");
				if (pluginClass) {
					pluginOrder.push(pluginClass);
				}
			});

			// Set hidden input value with comma-separated list
			$("#jsplugin-list").val(pluginOrder.join(","));
			
			// Submit form
			$("#jsform").submit();
		});
	}

	/**
	 * Initialize all plugins position features
	 */
	function init() {
		initSortable();
		initSaveButton();
	}

	// Initialize when DOM is ready
	$(document).ready(init);

})();
