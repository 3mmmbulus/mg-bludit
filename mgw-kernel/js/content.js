/**
 * Content page functionality
 * Handles delete page modal and tab navigation
 */

(function() {
	'use strict';

	/**
	 * Initialize delete page functionality
	 */
	function initDeletePage() {
		var selectedKey = false;

		// Store the page key when delete button is clicked
		$(".deletePageButton").on("click", function() {
			selectedKey = $(this).data('key');
		});

		// Handle delete confirmation
		$(".deletePageModalAcceptButton").on("click", function() {
			if (!selectedKey) {
				return;
			}

			// Create and submit delete form
			var form = $('<form>', {
				'action': HTML_PATH_ADMIN_ROOT + 'edit-content/' + selectedKey,
				'method': 'post',
				'target': '_top'
			});

			// Add CSRF token
			form.append($('<input>', {
				'type': 'hidden',
				'name': 'tokenCSRF',
				'value': tokenCSRF
			}));

			// Add page key
			form.append($('<input>', {
				'type': 'hidden',
				'name': 'key',
				'value': selectedKey
			}));

			// Add delete type
			form.append($('<input>', {
				'type': 'hidden',
				'name': 'type',
				'value': 'delete'
			}));

			// Submit form
			form.hide().appendTo("body").submit();
		});
	}

	/**
	 * Initialize tab navigation from URL hash
	 */
	function initTabNavigation() {
		const anchor = window.location.hash;
		if (anchor) {
			const tabElement = document.querySelector('a[href="' + anchor + '"]');
			if (tabElement && typeof bootstrap !== 'undefined') {
				const tab = new bootstrap.Tab(tabElement);
				tab.show();
			}
		}
	}

	/**
	 * Initialize all content page features
	 */
	function init() {
		initDeletePage();
		initTabNavigation();
	}

	// Initialize when DOM is ready
	$(document).ready(init);

})();
