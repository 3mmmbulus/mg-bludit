/**
 * Sidebar Collapse Functionality
 * 侧边栏折叠功能
 */
(function() {
	'use strict';
	
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
				if (content.style.display === 'none') {
					// 展开
					content.style.display = 'block';
					icon.classList.remove('bi-chevron-right');
					icon.classList.add('bi-chevron-down');
					
					// 保存状态到localStorage
					localStorage.setItem('sidebar-' + targetId, 'open');
				} else {
					// 折叠
					content.style.display = 'none';
					icon.classList.remove('bi-chevron-down');
					icon.classList.add('bi-chevron-right');
					
					// 保存状态到localStorage
					localStorage.setItem('sidebar-' + targetId, 'closed');
				}
			});
			
			// 恢复之前的状态
			const targetId = toggle.getAttribute('data-target');
			const savedState = localStorage.getItem('sidebar-' + targetId);
			
			if (savedState === 'open') {
				const content = document.getElementById(targetId);
				const icon = toggle.querySelector('.toggle-icon');
				if (content && icon) {
					content.style.display = 'block';
					icon.classList.remove('bi-chevron-right');
					icon.classList.add('bi-chevron-down');
				}
			}
		});
	}
	
	// 防止子菜单链接点击时刷新整个页面的侧边栏
	function preserveSidebarState() {
		// 监听所有侧边栏链接点击
		const sidebarLinks = document.querySelectorAll('.sidebar-section-content a');
		
		sidebarLinks.forEach(function(link) {
			link.addEventListener('click', function(e) {
				// 正常导航,但侧边栏状态已经通过localStorage保存
				// 页面重新加载后会自动恢复状态
			});
		});
	}
	
	// DOM加载完成后初始化
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			initSidebarCollapse();
			preserveSidebarState();
		});
	} else {
		initSidebarCollapse();
		preserveSidebarState();
	}
})();
