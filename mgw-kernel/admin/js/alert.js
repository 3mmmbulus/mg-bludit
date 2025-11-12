/**
 * Alert System
 * 全局提示消息系统
 */
(function() {
	'use strict';
	
	/**
	 * 显示提示消息
	 * @param {string} text - 要显示的消息文本
	 * @param {number} duration - 显示时长(毫秒),默认从配置读取
	 */
	window.showAlert = function(text, duration) {
		console.log("[INFO] Function showAlert() called.");
		const alertElement = document.getElementById("alert");
		
		if (!alertElement) {
			console.warn("[WARN] Alert element not found");
			return;
		}
		
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
		const hideDelay = duration || (window.ALERT_DISAPPEAR_IN ? window.ALERT_DISAPPEAR_IN * 1000 : 3000);
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
		}, hideDelay);
	};
	
	// Click anywhere to hide alert
	window.addEventListener('click', function() {
		const alertElement = document.getElementById("alert");
		if (alertElement) {
			alertElement.style.display = "none";
		}
	});
	
	// 自动显示从PHP传递的alert消息
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			if (window.ALERT_MESSAGE) {
				setTimeout(function(){ 
					showAlert(window.ALERT_MESSAGE); 
				}, 500);
			}
		});
	} else {
		if (window.ALERT_MESSAGE) {
			setTimeout(function(){ 
				showAlert(window.ALERT_MESSAGE); 
			}, 500);
		}
	}
})();
