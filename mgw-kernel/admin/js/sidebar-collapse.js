/**
 * Sidebar Collapse Functionality
 * 侧边栏折叠功能 - 优化版本，减少页面刷新时的闪烁
 */
(function() {
	'use strict';
	
	// 提前应用保存的状态，避免闪烁
	function applySavedStates() {
		const toggles = document.querySelectorAll('.sidebar-section-toggle');
		
		toggles.forEach(function(toggle) {
			const targetId = toggle.getAttribute('data-target');
			if (!targetId) return;
			
			const savedState = localStorage.getItem('sidebar-' + targetId);
			const content = document.getElementById(targetId);
			const icon = toggle.querySelector('.toggle-icon');
			
			if (savedState === 'open' && content && icon) {
				content.style.maxHeight = content.scrollHeight + 'px';
				content.style.display = 'block';
				content.style.opacity = '1';
				icon.classList.remove('bi-chevron-right');
				icon.classList.add('bi-chevron-down');
			} else if (content) {
				content.style.maxHeight = '0';
				content.style.display = 'none';
				content.style.opacity = '0';
			}
		});
	}
	
	// 初始化侧边栏折叠功能
	function initSidebarCollapse() {
		// 获取所有可折叠的标题
		const toggles = document.querySelectorAll('.sidebar-section-toggle');
		
		toggles.forEach(function(toggle) {
			toggle.style.cursor = 'pointer';
			toggle.style.userSelect = 'none';
			
			// 点击事件
			toggle.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				
				const targetId = this.getAttribute('data-target');
				const content = document.getElementById(targetId);
				const icon = this.querySelector('.toggle-icon');
				
				if (!content) return;
				
				// 切换展开/折叠状态
				const isHidden = content.style.display === 'none' || content.style.maxHeight === '0' || content.style.maxHeight === '0px';
				
				if (isHidden) {
					// 展开
					content.style.display = 'block';
					// 强制重排
					content.offsetHeight;
					content.style.maxHeight = content.scrollHeight + 'px';
					content.style.opacity = '1';
					
					icon.classList.remove('bi-chevron-right');
					icon.classList.add('bi-chevron-down');
					
					// 保存状态到localStorage
					localStorage.setItem('sidebar-' + targetId, 'open');
				} else {
					// 折叠
					content.style.maxHeight = '0';
					content.style.opacity = '0';
					
					// 动画结束后隐藏
					setTimeout(function() {
						if (content.style.maxHeight === '0px') {
							content.style.display = 'none';
						}
					}, 300);
					
					icon.classList.remove('bi-chevron-down');
					icon.classList.add('bi-chevron-right');
					
					// 保存状态到localStorage
					localStorage.setItem('sidebar-' + targetId, 'closed');
				}
			});
		});
		
		// 再次应用保存的状态，确保正确
		applySavedStates();
	}
	
	// 尽早执行状态恢复
	applySavedStates();
	
	// DOM加载完成后初始化
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initSidebarCollapse);
	} else {
		initSidebarCollapse();
	}
})();
