<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php

// Start form
echo Bootstrap::formOpen(array(
	'id' => 'jsform',
	'class' => 'd-flex flex-column h-100'
));

// Token CSRF
echo Bootstrap::formInputHidden(array(
	'name' => 'tokenCSRF',
	'value' => $security->getTokenCSRF()
));

// UUID
// The UUID is generated in the controller
echo Bootstrap::formInputHidden(array(
	'name' => 'uuid',
	'value' => $uuid
));

// Type = published, draft, sticky, static
echo Bootstrap::formInputHidden(array(
	'name' => 'type',
	'value' => 'published'
));

// Cover image
echo Bootstrap::formInputHidden(array(
	'name' => 'coverImage',
	'value' => ''
));

// Content
echo Bootstrap::formInputHidden(array(
	'name' => 'content',
	'value' => ''
));
?>

<!-- TOOLBAR -->
<div id="jseditorToolbar" class="mb-1">
	<div id="jseditorToolbarRight" class="btn-group btn-group-sm float-right" role="group" aria-label="Toolbar right">
		<button type="button" class="btn btn-light" id="jsmediaManagerOpenModal" data-bs-toggle="modal" data-bs-target="#jsmediaManagerModal"><span class="bi bi-image"></span> <?php $L->p('Images') ?></button>
		<button type="button" class="btn btn-light" id="jsoptionsSidebar" style="z-index:30"><span class="bi bi-gear"></span> <?php $L->p('Options') ?></button>
	</div>

	<div id="jseditorToolbarLeft">
		<button id="jsbuttonSave" type="button" class="btn btn-sm btn-primary" data-autosave-label="<?php $L->p('Autosave') ?>"><?php $L->p('Save') ?></button>
		<button id="jsbuttonPreview" type="button" class="btn btn-sm btn-secondary" data-preview-url="<?php echo DOMAIN_PAGES . 'autosave-' . $uuid . '?preview=' . md5('autosave-' . $uuid) ?>" data-preview-hash="<?php echo md5('autosave-' . $uuid) ?>"><?php $L->p('Preview') ?></button>
		<span id="jsbuttonSwitch" data-switch="publish" class="ml-2 text-secondary switch-button" data-publish-text="<?php $L->p('Publish') ?>" data-draft-text="<?php $L->p('Draft') ?>" data-publish-icon='<i class="bi bi-square switch-icon-publish"></i>' data-draft-icon='<i class="bi bi-square switch-icon-draft"></i>'><i class="bi bi-square switch-icon-publish"></i> <?php $L->p('Publish') ?></span>
	</div>
</div>

<!-- SIDEBAR OPTIONS -->
<div id="jseditorSidebar">
	<nav>
		<div class="nav nav-tabs" id="nav-tab" role="tablist">
			<a class="nav-link active show" id="nav-general-tab" data-bs-toggle="tab" href="#nav-general" role="tab" aria-controls="general"><?php $L->p('General') ?></a>
			<a class="nav-link" id="nav-advanced-tab" data-bs-toggle="tab" href="#nav-advanced" role="tab" aria-controls="advanced"><?php $L->p('Advanced') ?></a>
			<?php if (!empty($site->customFields())) : ?>
				<a class="nav-link" id="nav-custom-tab" data-bs-toggle="tab" href="#nav-custom" role="tab" aria-controls="custom"><?php $L->p('Custom') ?></a>
			<?php endif ?>
			<a class="nav-link" id="nav-seo-tab" data-bs-toggle="tab" href="#nav-seo" role="tab" aria-controls="seo"><?php $L->p('SEO') ?></a>
		</div>
	</nav>

	<div class="tab-content pr-3 pl-3 pb-3">
		<div id="nav-general" class="tab-pane fade show active" role="tabpanel" aria-labelledby="general-tab">
			<?php
			// Category
			echo Bootstrap::formSelectBlock(array(
				'name' => 'category',
				'label' => $L->g('Category'),
				'selected' => '',
				'class' => '',
				'emptyOption' => '- ' . $L->g('Uncategorized') . ' -',
				'options' => $categories->getKeyNameArray()
			));

			// Description
			echo Bootstrap::formTextareaBlock(array(
				'name' => 'description',
				'label' => $L->g('Description'),
				'selected' => '',
				'class' => '',
				'value' => '',
				'rows' => 5,
				'placeholder' => $L->get('this-field-can-help-describe-the-content')
			));
			?>

			<!-- Cover Image -->
			<label class="mt-4 mb-2 pb-2 border-bottom text-uppercase w-100"><?php $L->p('Cover Image') ?></label>
			<div>
				<img id="jscoverImagePreview" class="mx-auto d-block w-100" alt="Cover image preview" src="<?php echo HTML_PATH_CORE_IMG ?>default.svg" />
			</div>
			<div class="mt-2 text-center">
				<button type="button" id="jsbuttonSelectCoverImage" class="btn btn-primary btn-sm"><?php echo $L->g('Select cover image') ?></button>
				<button type="button" id="jsbuttonRemoveCoverImage" class="btn btn-secondary btn-sm"><?php echo $L->g('Remove cover image') ?></button>
			</div>
		</div>
		<div id="nav-advanced" class="tab-pane fade" role="tabpanel" aria-labelledby="advanced-tab">
			<?php
			// Date
			echo Bootstrap::formInputTextBlock(array(
				'name' => 'date',
				'label' => $L->g('Date'),
				'placeholder' => '',
				'value' => Date::current(DB_DATE_FORMAT),
				'tip' => $L->g('date-format-format')
			));

			// Type
			echo Bootstrap::formSelectBlock(array(
				'name' => 'typeSelector',
				'label' => $L->g('Type'),
				'selected' => '',
				'options' => array(
					'published' => '- ' . $L->g('Default') . ' -',
					'sticky' => $L->g('Sticky'),
					'static' => $L->g('Static')
				),
				'tip' => ''
			));

			// Position
			echo Bootstrap::formInputTextBlock(array(
				'name' => 'position',
				'label' => $L->g('Position'),
				'tip' => $L->g('Field used when ordering content by position'),
				'value' => $pages->nextPositionNumber()
			));

			// Tags
			echo Bootstrap::formInputTextBlock(array(
				'name' => 'tags',
				'label' => $L->g('Tags'),
				'placeholder' => '',
				'tip' => $L->g('Write the tags separated by comma')
			));

			// Parent
			echo Bootstrap::formSelectBlock(array(
				'name' => 'parent',
				'label' => $L->g('Parent'),
				'options' => array(),
				'selected' => false,
				'class' => '',
				'tip' => $L->g('Start typing a page title to see a list of suggestions.'),
			));

			?>

			<?php
			// Template
			echo Bootstrap::formInputTextBlock(array(
				'name' => 'template',
				'label' => $L->g('Template'),
				'placeholder' => '',
				'value' => '',
				'tip' => $L->g('Write a template name to filter the page in the theme and change the style of the page.')
			));

			echo Bootstrap::formInputTextBlock(array(
				'name' => 'externalCoverImage',
				'label' => $L->g('External cover image'),
				'placeholder' => "https://",
				'value' => '',
				'tip' => $L->g('Set a cover image from external URL, such as a CDN or some server dedicated for images.')
			));

			// Username
			echo Bootstrap::formInputTextBlock(array(
				'name' => '',
				'label' => $L->g('Author'),
				'placeholder' => '',
				'value' => $login->username(),
				'tip' => '',
				'disabled' => true
			));
			?>

		</div>
		<?php if (!empty($site->customFields())) : ?>
			<div id="nav-custom" class="tab-pane fade" role="tabpanel" aria-labelledby="custom-tab">
				<?php
				$customFields = $site->customFields();
				foreach ($customFields as $field => $options) {
					if (!isset($options['position'])) {
						if ($options['type'] == "string") {
							echo Bootstrap::formInputTextBlock(array(
								'name' => 'custom[' . $field . ']',
								'label' => (isset($options['label']) ? $options['label'] : ''),
								'value' => (isset($options['default']) ? $options['default'] : ''),
								'tip' => (isset($options['tip']) ? $options['tip'] : ''),
								'placeholder' => (isset($options['placeholder']) ? $options['placeholder'] : '')
							));
						} elseif ($options['type'] == "bool") {
							echo Bootstrap::formCheckbox(array(
								'name' => 'custom[' . $field . ']',
								'label' => (isset($options['label']) ? $options['label'] : ''),
								'placeholder' => (isset($options['placeholder']) ? $options['placeholder'] : ''),
								'checked' => (isset($options['checked']) ? true : false),
								'labelForCheckbox' => (isset($options['tip']) ? $options['tip'] : '')
							));
						}
					}
				}
				?>
			</div>
		<?php endif ?>
		<div id="nav-seo" class="tab-pane fade" role="tabpanel" aria-labelledby="seo-tab">
			<?php
			// Friendly URL
			echo Bootstrap::formInputTextBlock(array(
				'name' => 'slug',
				'tip' => $L->g('URL associated with the content'),
				'label' => $L->g('Friendly URL'),
				'placeholder' => $L->g('Leave empty for autocomplete by Maigewan.')
			));

			// Robots
			echo Bootstrap::formCheckbox(array(
				'name' => 'noindex',
				'label' => 'Robots',
				'labelForCheckbox' => $L->g('apply-code-noindex-code-to-this-page'),
				'placeholder' => '',
				'checked' => false,
				'tip' => $L->g('This tells search engines not to show this page in their search results.')
			));

			// Robots
			echo Bootstrap::formCheckbox(array(
				'name' => 'nofollow',
				'label' => '',
				'labelForCheckbox' => $L->g('apply-code-nofollow-code-to-this-page'),
				'placeholder' => '',
				'checked' => false,
				'tip' => $L->g('This tells search engines not to follow links on this page.')
			));

			// Robots
			echo Bootstrap::formCheckbox(array(
				'name' => 'noarchive',
				'label' => '',
				'labelForCheckbox' => $L->g('apply-code-noarchive-code-to-this-page'),
				'placeholder' => '',
				'checked' => false,
				'tip' => $L->g('This tells search engines not to save a cached copy of this page.')
			));
			?>
		</div>
	</div>
</div>

<!-- Custom fields: TOP -->
<?php
$customFields = $site->customFields();
foreach ($customFields as $field => $options) {
	if (isset($options['position']) && ($options['position'] == 'top')) {
		if ($options['type'] == "string") {
			echo Bootstrap::formInputTextBlock(array(
				'name' => 'custom[' . $field . ']',
				'label' => (isset($options['label']) ? $options['label'] : ''),
				'value' => (isset($options['default']) ? $options['default'] : ''),
				'tip' => (isset($options['tip']) ? $options['tip'] : ''),
				'placeholder' => (isset($options['placeholder']) ? $options['placeholder'] : ''),
				'class' => 'mb-2',
				'labelClass' => 'mb-2 pb-2 border-bottom text-uppercase w-100'

			));
		} elseif ($options['type'] == "bool") {
			echo Bootstrap::formCheckbox(array(
				'name' => 'custom[' . $field . ']',
				'label' => (isset($options['label']) ? $options['label'] : ''),
				'placeholder' => (isset($options['placeholder']) ? $options['placeholder'] : ''),
				'checked' => (isset($options['checked']) ? true : false),
				'labelForCheckbox' => (isset($options['tip']) ? $options['tip'] : ''),
				'class' => 'mb-2',
				'labelClass' => 'mb-2 pb-2 border-bottom text-uppercase w-100'
			));
		}
	}
}
?>


<!-- Title -->
<div id="jseditorTitle" class="mb-3">
	<input id="jstitle" name="title" type="text" dir="auto" class="form-control form-control-lg rounded-0" value="" placeholder="<?php $L->p('Enter title') ?>">
</div>

<!-- Editor -->
<textarea id="jseditor" class="editable h-100 mb-1"></textarea>

<!-- Custom fields: BOTTOM -->
<?php
$customFields = $site->customFields();
foreach ($customFields as $field => $options) {
	if (isset($options['position']) && ($options['position'] == 'bottom')) {
		if ($options['type'] == "string") {
			echo Bootstrap::formInputTextBlock(array(
				'name' => 'custom[' . $field . ']',
				'label' => (isset($options['label']) ? $options['label'] : ''),
				'value' => (isset($options['default']) ? $options['default'] : ''),
				'tip' => (isset($options['tip']) ? $options['tip'] : ''),
				'placeholder' => (isset($options['placeholder']) ? $options['placeholder'] : ''),
				'class' => 'mt-2',
				'labelClass' => 'mb-2 pb-2 border-bottom text-uppercase w-100'

			));
		} elseif ($options['type'] == "bool") {
			echo Bootstrap::formCheckbox(array(
				'name' => 'custom[' . $field . ']',
				'label' => (isset($options['label']) ? $options['label'] : ''),
				'placeholder' => (isset($options['placeholder']) ? $options['placeholder'] : ''),
				'checked' => (isset($options['checked']) ? true : false),
				'labelForCheckbox' => (isset($options['tip']) ? $options['tip'] : ''),
				'class' => 'mt-2',
				'labelClass' => 'mb-2 pb-2 border-bottom text-uppercase w-100'
			));
		}
	}
}
?>

</form>

<!-- Modal for Media Manager -->
<?php 
	include(PATH_ADMIN_THEMES . 'booty/html/media.php');
	// JS 由 index.php 统一加载
?>
