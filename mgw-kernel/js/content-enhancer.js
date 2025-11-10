/**
 * Content Management Enhancer - 内容管理增强
 * 为内容列表添加快速操作和局部刷新功能
 */
(function() {
	'use strict';

	/**
	 * 快速切换内容类型(无需刷新页面)
	 */
	function enhanceContentTabs() {
		const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
		if (!tabLinks.length) return;

		tabLinks.forEach(link => {
			link.addEventListener('shown.bs.tab', function(e) {
				// 保存当前标签到localStorage
				const tabId = e.target.getAttribute('href');
				localStorage.setItem('lastContentTab', tabId);
				
				// 添加淡入动画
				const tabPane = document.querySelector(tabId);
				if (tabPane) {
					tabPane.style.animation = 'fadeIn 0.3s ease';
				}
			});
		});

		// 恢复上次查看的标签
		const lastTab = localStorage.getItem('lastContentTab');
		if (lastTab) {
			const tabLink = document.querySelector(`a[href="${lastTab}"]`);
			if (tabLink && window.bootstrap) {
				const tab = new bootstrap.Tab(tabLink);
				tab.show();
			}
		}
	}

	/**
	 * 快速删除确认(优化交互)
	 */
	function enhanceDeleteConfirm() {
		const deleteButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target*="delete"]');
		
		deleteButtons.forEach(button => {
			button.addEventListener('click', function() {
				// 添加点击反馈
				button.style.transform = 'scale(0.95)';
				setTimeout(() => {
					button.style.transform = '';
				}, 100);
			});
		});
	}

	/**
	 * 内容卡片悬停效果
	 */
	function enhanceContentCards() {
		const contentCards = document.querySelectorAll('.card-content, .item');
		
		contentCards.forEach(card => {
			card.style.transition = 'all 0.2s ease';
			
			card.addEventListener('mouseenter', function() {
				this.style.transform = 'translateY(-2px)';
				this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
			});
			
			card.addEventListener('mouseleave', function() {
				this.style.transform = '';
				this.style.boxShadow = '';
			});
		});
	}

	/**
	 * 搜索框即时反馈
	 */
	function enhanceSearchBox() {
		const searchInput = document.querySelector('#search, input[placeholder*="Search"]');
		if (!searchInput) return;

		// 添加清除按钮
		const clearBtn = document.createElement('button');
		clearBtn.type = 'button';
		clearBtn.className = 'btn btn-sm btn-link position-absolute end-0 top-50 translate-middle-y';
		clearBtn.innerHTML = '<i class="bi bi-x-circle"></i>';
		clearBtn.style.display = 'none';
		clearBtn.style.zIndex = '10';
		
		const wrapper = searchInput.parentElement;
		wrapper.style.position = 'relative';
		wrapper.appendChild(clearBtn);

		searchInput.addEventListener('input', function() {
			clearBtn.style.display = this.value ? 'block' : 'none';
		});

		clearBtn.addEventListener('click', function() {
			searchInput.value = '';
			searchInput.dispatchEvent(new Event('input'));
			searchInput.dispatchEvent(new Event('keyup'));
			clearBtn.style.display = 'none';
			searchInput.focus();
		});
	}

	/**
	 * 列表项拖拽视觉反馈增强
	 */
	function enhanceDragAndDrop() {
		const sortableElements = document.querySelectorAll('[data-sortable]');
		
		sortableElements.forEach(element => {
			element.addEventListener('dragstart', function(e) {
				this.style.opacity = '0.5';
				this.style.transform = 'scale(0.98)';
			});
			
			element.addEventListener('dragend', function(e) {
				this.style.opacity = '';
				this.style.transform = '';
			});
			
			element.addEventListener('dragover', function(e) {
				e.preventDefault();
				this.style.borderTop = '2px solid #0078D4';
			});
			
			element.addEventListener('dragleave', function(e) {
				this.style.borderTop = '';
			});
			
			element.addEventListener('drop', function(e) {
				this.style.borderTop = '';
				// 显示成功提示
				if (window.SPAEnhancer) {
					window.SPAEnhancer.showAlert('位置已更新', 'success');
				}
			});
		});
	}

	/**
	 * 批量操作增强
	 */
	function enhanceBulkActions() {
		const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="select"]');
		if (!checkboxes.length) return;

		// 全选功能
		const selectAllBtn = document.createElement('button');
		selectAllBtn.type = 'button';
		selectAllBtn.className = 'btn btn-sm btn-outline-secondary mb-2';
		selectAllBtn.textContent = '全选';
		
		const firstCheckbox = checkboxes[0];
		const container = firstCheckbox.closest('.card-body, .tab-pane');
		if (container) {
			container.insertBefore(selectAllBtn, container.firstChild);
		}

		let allSelected = false;
		selectAllBtn.addEventListener('click', function() {
			allSelected = !allSelected;
			checkboxes.forEach(cb => {
				cb.checked = allSelected;
				const parent = cb.closest('.card-content, .item');
				if (parent) {
					parent.style.backgroundColor = allSelected ? '#f0f8ff' : '';
				}
			});
			this.textContent = allSelected ? '取消全选' : '全选';
		});

		// 单个复选框变化时的视觉反馈
		checkboxes.forEach(checkbox => {
			checkbox.addEventListener('change', function() {
				const parent = this.closest('.card-content, .item');
				if (parent) {
					parent.style.backgroundColor = this.checked ? '#f0f8ff' : '';
					parent.style.transition = 'background-color 0.2s ease';
				}
			});
		});
	}

	/**
	 * 初始化所有增强功能
	 */
	function init() {
		enhanceContentTabs();
		enhanceDeleteConfirm();
		enhanceContentCards();
		enhanceSearchBox();
		enhanceDragAndDrop();
		enhanceBulkActions();
	}

	// 页面加载完成后初始化
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	// 支持动态加载的内容
	window.ContentEnhancer = { init };

})();
