/**
 * Bootstrap 5 JavaScript 兼容性补丁
 * 自动将 Bootstrap 4 的 data-toggle 属性转换为 Bootstrap 5 的 data-bs-toggle
 * 提供 Bootstrap 5 所需的 jQuery 兼容性 API
 */

(function() {
	'use strict';
	
	// 确保 MGW/$ 已加载
	if (typeof window.$ === 'undefined') {
		console.error('Bootstrap 5 兼容性补丁: MGW 库未加载');
		return;
	}

	// 页面加载完成后执行
	document.addEventListener('DOMContentLoaded', function() {
		
		// 转换 data-toggle 为 data-bs-toggle
		const elementsWithToggle = document.querySelectorAll('[data-toggle]');
		elementsWithToggle.forEach(function(element) {
			const toggleValue = element.getAttribute('data-toggle');
			element.setAttribute('data-bs-toggle', toggleValue);
			element.removeAttribute('data-toggle');
		});
		
		// 转换 data-target 为 data-bs-target
		const elementsWithTarget = document.querySelectorAll('[data-target]');
		elementsWithTarget.forEach(function(element) {
			const targetValue = element.getAttribute('data-target');
			element.setAttribute('data-bs-target', targetValue);
			element.removeAttribute('data-target');
		});
		
		// 转换 data-dismiss 为 data-bs-dismiss
		const elementsWithDismiss = document.querySelectorAll('[data-dismiss]');
		elementsWithDismiss.forEach(function(element) {
			const dismissValue = element.getAttribute('data-dismiss');
			element.setAttribute('data-bs-dismiss', dismissValue);
			element.removeAttribute('data-dismiss');
		});
		
		// 转换 data-placement 为 data-bs-placement
		const elementsWithPlacement = document.querySelectorAll('[data-placement]');
		elementsWithPlacement.forEach(function(element) {
			const placementValue = element.getAttribute('data-placement');
			element.setAttribute('data-bs-placement', placementValue);
			element.removeAttribute('data-placement');
		});
		
		// 转换 data-content 为 data-bs-content
		const elementsWithContent = document.querySelectorAll('[data-content]');
		elementsWithContent.forEach(function(element) {
			const contentValue = element.getAttribute('data-content');
			element.setAttribute('data-bs-content', contentValue);
			element.removeAttribute('data-content');
		});
		
		// 转换 data-trigger 为 data-bs-trigger
		const elementsWithTrigger = document.querySelectorAll('[data-trigger]');
		elementsWithTrigger.forEach(function(element) {
			const triggerValue = element.getAttribute('data-trigger');
			element.setAttribute('data-bs-trigger', triggerValue);
			element.removeAttribute('data-trigger');
		});
		
		console.log('Bootstrap 5 兼容性补丁已应用');
	});
	
})();
