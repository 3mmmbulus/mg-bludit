/**
 * Edit Content Page Script
 * Handles editor toolbar, sidebar, cover image, parent selector, autosave, and content publishing
 */
(function() {
	'use strict';

	var currentContent = '';

	/**
	 * Initialize editor toolbar and sidebar toggle
	 */
	function initToolbar() {
		$('#jsoptionsSidebar').on('click', function() {
			$('#jseditorSidebar').toggle();
			$('#jsshadow').toggle();
		});

		$('#jsshadow').on('click', function() {
			$('#jseditorSidebar').toggle();
			$('#jsshadow').toggle();
		});
	}

	/**
	 * Initialize cover image selection and removal
	 */
	function initCoverImage() {
		$('#jscoverImagePreview').on('click', function() {
			openMediaManager();
		});

		$('#jsbuttonSelectCoverImage').on('click', function() {
			openMediaManager();
		});

		$('#jsbuttonRemoveCoverImage').on('click', function() {
			$('#jscoverImage').val('');
			$('#jscoverImagePreview').attr('src', HTML_PATH_CORE_IMG + 'default.svg');
		});
	}

	/**
	 * Initialize parent page selector with Select2
	 */
	function initParentSelector() {
		var parentElement = $('#jsparent');
		if (!parentElement.length) return;

		var currentKey = parentElement.data('current-key') || '';

		parentElement.select2({
			placeholder: '',
			allowClear: true,
			theme: 'bootstrap5',
			minimumInputLength: 2,
			ajax: {
				url: HTML_PATH_ADMIN_ROOT + 'ajax/get-published',
				data: function(params) {
					return {
						checkIsParent: true,
						currentKey: currentKey,
						query: params.term
					};
				},
				processResults: function(data) {
					return data;
				}
			},
			escapeMarkup: function(markup) {
				return markup;
			},
			templateResult: function(data) {
				var html = data.text;
				if (data.type === 'static') {
					html += '<span class="badge badge-pill badge-light">' + data.type + '</span>';
				}
				return html;
			}
		});
	}

	/**
	 * Initialize advanced tab functionality
	 */
	function initAdvancedTab() {
		// External cover image changes
		$('#jsexternalCoverImage').change(function() {
			$('#jscoverImage').val($(this).val());
		});

		// Generate slug when user types title
		$('#jstitle').keyup(function() {
			var text = $(this).val();
			var parent = $('#jsparent').val();
			var currentKey = $('#jsparent').data('current-key') || '';
			var ajax = new maigewanAjax();
			var callBack = $('#jsslug');
			ajax.generateSlug(text, parent, currentKey, callBack);
		});

		// Datepicker initialization
		var dateElement = $('#jsdate');
		if (dateElement.length && typeof $.fn.datetimepicker !== 'undefined') {
			dateElement.datetimepicker({
				format: DB_DATE_FORMAT
			});
		}
	}

	/**
	 * Define fallback editor functions if no editor plugin is active
	 */
	function defineEditorFallbacks() {
		if (typeof window.editorGetContent !== 'function') {
			window.editorGetContent = function() {
				return $('#jseditor').val();
			};
		}
		if (typeof window.editorSetContent !== 'function') {
			window.editorSetContent = function(text) {
				$('#jseditor').val(text);
			};
		}
		if (typeof window.editorInsertMedia !== 'function') {
			window.editorInsertMedia = function(filename) {
				$('#jseditor').val($('#jseditor').val() + '<img src="' + filename + '" alt="">');
			};
		}
		if (typeof window.editorInsertLinkedMedia !== 'function') {
			window.editorInsertLinkedMedia = function(filename, link) {
				$('#jseditor').val($('#jseditor').val() + '<a href="' + link + '"><img src="' + filename + '" alt=""></a>');
			};
		}
	}

	/**
	 * Initialize publish/draft switch button
	 */
	function initSwitchButton() {
		var switchButton = $('#jsswitchButton');
		var publishIcon = switchButton.data('publish-icon') || '<i class="bi bi-square switch-icon-publish"></i>';
		var draftIcon = switchButton.data('draft-icon') || '<i class="bi bi-square switch-icon-draft"></i>';
		var publishText = switchButton.data('publish-text') || 'Publish';
		var draftText = switchButton.data('draft-text') || 'Draft';

		switchButton.on('click', function() {
			if ($(this).data('switch') === 'publish') {
				$(this).html(draftIcon + ' ' + draftText);
				$(this).data('switch', 'draft');
			} else {
				$(this).html(publishIcon + ' ' + publishText);
				$(this).data('switch', 'publish');
			}
		});
	}

	/**
	 * Initialize preview button
	 */
	function initPreviewButton() {
		$('#jsbuttonPreview').on('click', function() {
			var previewUrl = $(this).data('preview-url');
			if (previewUrl) {
				var preview = window.open(previewUrl, 'maigewan-preview');
				preview.focus();
			}
		});
	}

	/**
	 * Initialize save button
	 */
	function initSaveButton() {
		$('#jsbuttonSave').on('click', function() {
			var actionParameters = '';

			// Determine type based on switch state
			if ($('#jsswitchButton').data('switch') === 'publish') {
				var value = $('#jstypeSelector').val();
				$('#jstype').val(value);
				actionParameters = '#' + value;
			} else {
				$('#jstype').val('draft');
				actionParameters = '#draft';
			}

			// Get content from editor
			$('#jscontent').val(editorGetContent());

			// Submit form
			$('#jsform').attr('action', actionParameters);
			$('#jsform').submit();
		});
	}

	/**
	 * Initialize delete page button
	 */
	function initDeleteButton() {
		$('#jsbuttonDeleteAccept').on('click', function() {
			$('#jstype').val('delete');
			$('#jscontent').val('');
			$('#jsform').submit();
		});
	}

	/**
	 * Initialize autosave functionality
	 */
	function initAutosave() {
		var autosaveInterval = window.AUTOSAVE_INTERVAL || 5; // Minutes
		var autosaveLabel = $('#jsbuttonSave').data('autosave-label') || 'Autosave';
		var minContentLength = 100;

		// Store initial content (safely)
		try {
			currentContent = editorGetContent();
		} catch (e) {
			currentContent = '';
			console.log('Editor not ready yet, will start autosave when content is available');
		}

		setInterval(function() {
			// Safety check: ensure editorGetContent exists and works
			if (typeof editorGetContent !== 'function') {
				return;
			}

			var uuid = $('#jsuuid').val();
			var title = $('#jstitle').val() + '[' + autosaveLabel + ']';
			var content;
			
			try {
				content = editorGetContent();
			} catch (e) {
				// Editor not ready yet, skip this autosave cycle
				return;
			}

			// Only autosave if content is long enough
			if (!content || content.length < minContentLength) {
				return false;
			}

			// Only autosave if content has changed
			if (currentContent !== content) {
				currentContent = content;
				maigewanAjax.saveAsDraft(uuid, title, content).then(function(data) {
					if (data.status === 0) {
						showAlert(autosaveLabel);
					}
				});
			}
		}, 1000 * 60 * autosaveInterval);
	}

	/**
	 * Initialize all components on document ready
	 */
	$(document).ready(function() {
		defineEditorFallbacks();
		initToolbar();
		initCoverImage();
		initParentSelector();
		initAdvancedTab();
		initSwitchButton();
		initPreviewButton();
		initSaveButton();
		initDeleteButton();
		initAutosave();
	});

})();
