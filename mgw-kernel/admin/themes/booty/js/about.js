(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var copyBtn = document.querySelector('[data-about-copy-email]');
		if (!copyBtn) {
			return;
		}

		var labelSpan = copyBtn.querySelector('[data-about-copy-label]');
		var originalLabel = copyBtn.getAttribute('data-original-label') || '';
		var copiedLabel = copyBtn.getAttribute('data-copied-label') || originalLabel;
		var failedLabel = copyBtn.getAttribute('data-failed-label') || originalLabel;
		var revertTimer = null;

		function resetState() {
			if (labelSpan) {
				labelSpan.textContent = originalLabel;
			}
			copyBtn.classList.remove('btn-success');
			copyBtn.classList.add('btn-primary');
		}

		function setState(text, success) {
			if (labelSpan) {
				labelSpan.textContent = text;
			}
			copyBtn.classList.toggle('btn-success', success);
			copyBtn.classList.toggle('btn-primary', !success);
			if (revertTimer) {
				clearTimeout(revertTimer);
			}
			revertTimer = window.setTimeout(resetState, 2000);
		}

		function fallbackCopy(text) {
			var textarea = document.createElement('textarea');
			textarea.value = text;
			document.body.appendChild(textarea);
			textarea.select();
			var success = false;
			try {
				success = document.execCommand('copy');
			} catch (err) {
				success = false;
			} finally {
				document.body.removeChild(textarea);
			}
			return success;
		}

		copyBtn.addEventListener('click', function () {
			var email = copyBtn.getAttribute('data-about-copy-email') || '';
			if (email === '') {
				setState(failedLabel, false);
				return;
			}

			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(email).then(function () {
					setState(copiedLabel, true);
				}).catch(function () {
					if (fallbackCopy(email)) {
						setState(copiedLabel, true);
					} else {
						setState(failedLabel, false);
					}
				});
			} else if (fallbackCopy(email)) {
				setState(copiedLabel, true);
			} else {
				setState(failedLabel, false);
			}
		});
	});
})();
