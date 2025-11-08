<script charset="utf-8">
	function showAlert(text) {
		console.log("[INFO] Function showAlert() called.");
		const alertElement = document.getElementById("alert");
		alertElement.innerHTML = text;
		alertElement.style.display = "block";
		alertElement.style.opacity = "0";
		
		// Fade in
		let opacity = 0;
		const fadeIn = setInterval(function() {
			if (opacity >= 1) {
				clearInterval(fadeIn);
			}
			alertElement.style.opacity = opacity;
			opacity += 0.1;
		}, 30);
		
		// Auto hide after delay
		setTimeout(function() {
			let opacity = 1;
			const fadeOut = setInterval(function() {
				if (opacity <= 0) {
					clearInterval(fadeOut);
					alertElement.style.display = "none";
				}
				alertElement.style.opacity = opacity;
				opacity -= 0.1;
			}, 30);
		}, <?php echo ALERT_DISAPPEAR_IN*1000 ?>);
	}

	<?php if (Alert::defined()): ?>
	setTimeout(function(){ showAlert("<?php echo Alert::get() ?>") }, 500);
	<?php endif; ?>

	// Click anywhere to hide alert
	window.addEventListener('click', function() {
		const alertElement = document.getElementById("alert");
		alertElement.style.display = "none";
	});
</script>

<div id="alert" class="alert <?php echo (Alert::status()==ALERT_STATUS_FAIL)?'alert-danger':'alert-success' ?>" style="display:none;"></div>
