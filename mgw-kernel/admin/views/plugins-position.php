<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php echo Bootstrap::formOpen(array('id'=>'jsform', 'class'=>'tab-content')); ?>

<div class="align-middle">
	<div class="float-right mt-1">
		<button type="button" class="btn btn-primary btn-sm jsbuttonSave" name="save"><?php $L->p('Save') ?></button>
		<a class="btn btn-secondary btn-sm" href="<?php echo HTML_PATH_ADMIN_ROOT.'plugins' ?>" role="button"><?php $L->p('Cancel') ?></a>
	</div>
	<?php echo Bootstrap::pageTitle(array('title'=>$L->g('Plugins position'), 'icon'=>'tags')); ?>
</div>

<div class="alert alert-primary"><?php $L->p('Drag and Drop to sort the plugins') ?></div>

<?php
	// Token CSRF
	echo Bootstrap::formInputHidden(array(
		'name'=>'tokenCSRF',
		'value'=>$security->getTokenCSRF()
	));

	echo Bootstrap::formInputHidden(array(
		'name'=>'plugin-list',
		'value'=>''
	));

	echo '<ul class="list-group list-group-sortable">';
	foreach ($plugins['siteSidebar'] as $Plugin) {
		echo '<li class="list-group-item" data-plugin="'.$Plugin->className().'"><span class="bi bi-arrows-vertical"></span> '.$Plugin->name().'</li>';
	}
	echo '</ul>';
?>

<?php echo Bootstrap::formClose(); ?>

<?php // JS 由 index.php 统一加载 ?>