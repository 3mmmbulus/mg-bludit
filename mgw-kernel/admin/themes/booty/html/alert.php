<?php if (Alert::defined()): ?>
<script charset="utf-8">
	window.ALERT_MESSAGE = "<?php echo Alert::get() ?>";
	window.ALERT_DISAPPEAR_IN = <?php echo ALERT_DISAPPEAR_IN ?>;
</script>
<?php endif; ?>

<div id="alert" class="alert <?php echo (Alert::status()==ALERT_STATUS_FAIL)?'alert-danger':'alert-success' ?>" style="display:none;"></div>
