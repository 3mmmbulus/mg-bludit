/**
 * SPA Enhancer - 单页面应用体验增强
 * 为传统多页面应用添加平滑的过渡效果和AJAX导航
 */
(function() {
	'use strict';

	// 配置项
	const config = {
		enablePageTransitions: true,      // 启用页面过渡动画
		enableAjaxForms: true,             // 启用AJAX表单提交
		enableProgressBar: true,           // 启用顶部进度条
		transitionDuration: 300,           // 过渡动画时长(ms)
		progressBarColor: '#0078D4',       // 进度条颜色
		excludeSelectors: [                // 排除选择器
			'a[target="_blank"]',
			'a[href^="http"]',
			'a[href^="mailto:"]',
			'a[href^="tel:"]',
			'a[download]'
		]
	};

	// 状态管理
	let isNavigating = false;
	let progressBar = null;

	/**
	 * 初始化进度条
	 */
	function initProgressBar() {
		if (!config.enableProgressBar) return;

		progressBar = document.createElement('div');
		progressBar.id = 'spa-progress-bar';
		progressBar.style.cssText = `
			position: fixed;
			top: 0;
			left: 0;
			width: 0%;
			height: 3px;
			background: ${config.progressBarColor};
			z-index: 9999;
			transition: width 0.3s ease, opacity 0.3s ease;
			opacity: 0;
		`;
		document.body.appendChild(progressBar);
	}

	/**
	 * 显示进度条
	 */
	function showProgress() {
		if (!progressBar) return;
		
		progressBar.style.opacity = '1';
		progressBar.style.width = '0%';
		
		// 模拟进度
		let progress = 0;
		const interval = setInterval(() => {
			progress += Math.random() * 30;
			if (progress > 90) progress = 90;
			progressBar.style.width = progress + '%';
		}, 200);
		
		progressBar.dataset.interval = interval;
	}

	/**
	 * 完成进度条
	 */
	function completeProgress() {
		if (!progressBar) return;
		
		const interval = progressBar.dataset.interval;
		if (interval) clearInterval(interval);
		
		progressBar.style.width = '100%';
		setTimeout(() => {
			progressBar.style.opacity = '0';
			setTimeout(() => {
				progressBar.style.width = '0%';
			}, 300);
		}, 200);
	}

	/**
	 * 页面淡出动画
	 */
	function fadeOut(callback) {
		if (!config.enablePageTransitions) {
			callback();
			return;
		}

		const main = document.querySelector('#maigewan-main-content') || document.body;
		main.style.transition = `opacity ${config.transitionDuration}ms ease`;
		main.style.opacity = '0';
		
		setTimeout(callback, config.transitionDuration);
	}

	/**
	 * 页面淡入动画
	 */
	function fadeIn() {
		if (!config.enablePageTransitions) return;

		const main = document.querySelector('#maigewan-main-content') || document.body;
		main.style.opacity = '0';
		
		setTimeout(() => {
			main.style.transition = `opacity ${config.transitionDuration}ms ease`;
			main.style.opacity = '1';
		}, 50);
	}

	/**
	 * AJAX表单提交
	 */
	function handleFormSubmit(event, form) {
		if (!config.enableAjaxForms) return false;
		
		// 排除文件上传表单
		if (form.querySelector('input[type="file"]')) return false;
		
		// 排除编辑器表单(需要特殊处理)
		if (form.id === 'jsform' && window.editorGetContent) {
			return false; // 让原有逻辑处理
		}

		event.preventDefault();
		showProgress();

		const formData = new FormData(form);
		const action = form.action || window.location.href;
		const method = form.method || 'POST';

		fetch(action, {
			method: method,
			body: formData,
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		})
		.then(response => {
			completeProgress();
			
			if (response.redirected) {
				// 有重定向,导航到新页面
				fadeOut(() => {
					window.location.href = response.url;
				});
				return null;
			}
			
			return response.text();
		})
		.then(html => {
			if (html) {
				// 显示成功消息
				showAlert('保存成功');
				fadeIn();
			}
		})
		.catch(error => {
			completeProgress();
			console.error('表单提交失败:', error);
			showAlert('保存失败,请重试');
		});

		return true;
	}

	/**
	 * AJAX导航(可选,需要后端支持)
	 */
	function handleLinkClick(event, link) {
		// 暂时禁用AJAX导航,保持传统页面跳转
		// 未来如果需要可以启用
		return false;
	}

	/**
	 * 显示提示消息
	 */
	function showAlert(message, type = 'success') {
		// 使用现有的showAlert函数或创建简单提示
		if (typeof window.showAlert === 'function') {
			window.showAlert(message);
			return;
		}

		// 简单的Toast提示
		const toast = document.createElement('div');
		toast.className = `spa-toast spa-toast-${type}`;
		toast.textContent = message;
		toast.style.cssText = `
			position: fixed;
			top: 20px;
			right: 20px;
			padding: 12px 24px;
			background: ${type === 'success' ? '#28a745' : '#dc3545'};
			color: white;
			border-radius: 4px;
			box-shadow: 0 2px 8px rgba(0,0,0,0.2);
			z-index: 10000;
			animation: slideInRight 0.3s ease;
		`;
		
		document.body.appendChild(toast);
		
		setTimeout(() => {
			toast.style.animation = 'slideOutRight 0.3s ease';
			setTimeout(() => toast.remove(), 300);
		}, 3000);
	}

	/**
	 * 添加CSS动画
	 */
	function addAnimationStyles() {
		const style = document.createElement('style');
		style.textContent = `
			@keyframes slideInRight {
				from {
					transform: translateX(100%);
					opacity: 0;
				}
				to {
					transform: translateX(0);
					opacity: 1;
				}
			}
			
			@keyframes slideOutRight {
				from {
					transform: translateX(0);
					opacity: 1;
				}
				to {
					transform: translateX(100%);
					opacity: 0;
				}
			}

			/* 平滑的内容过渡 */
			#maigewan-main-content {
				transition: opacity 0.3s ease;
			}

			/* 按钮点击反馈 */
			.btn:active {
				transform: scale(0.98);
				transition: transform 0.1s ease;
			}

			/* 表单输入焦点增强 */
			.form-control:focus,
			.form-select:focus {
				transform: scale(1.01);
				transition: transform 0.2s ease;
			}
		`;
		document.head.appendChild(style);
	}

	/**
	 * 为现有页面添加加载动画
	 */
	function enhancePageLoading() {
		// 监听页面卸载事件
		window.addEventListener('beforeunload', function() {
			showProgress();
		});

		// 页面加载完成后淡入
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', function() {
				completeProgress();
				fadeIn();
			});
		} else {
			fadeIn();
		}
	}

	/**
	 * 优化现有功能的用户体验
	 */
	function enhanceExistingFeatures() {
		// 为所有按钮添加加载状态
		document.addEventListener('click', function(e) {
			const button = e.target.closest('button[type="submit"], .btn-primary');
			if (button && !button.disabled) {
				const form = button.closest('form');
				if (form) {
					showProgress();
				}
			}
		}, true);

		// 为Select2下拉添加平滑动画
		if (window.jQuery && window.jQuery.fn.select2) {
			$(document).on('select2:opening', function() {
				$('.select2-dropdown').css({
					'animation': 'fadeIn 0.2s ease'
				});
			});
		}
	}

	/**
	 * 初始化
	 */
	function init() {
		// 添加样式
		addAnimationStyles();
		
		// 初始化进度条
		initProgressBar();
		
		// 增强页面加载
		enhancePageLoading();
		
		// 增强现有功能
		enhanceExistingFeatures();

		// 监听表单提交
		if (config.enableAjaxForms) {
			document.addEventListener('submit', function(e) {
				const form = e.target;
				if (form && form.tagName === 'FORM') {
					handleFormSubmit(e, form);
				}
			});
		}

		// 暴露到全局以便外部调用
		window.SPAEnhancer = {
			showProgress,
			completeProgress,
			showAlert,
			fadeOut,
			fadeIn
		};
	}

	// 当DOM加载完成后初始化
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})();
