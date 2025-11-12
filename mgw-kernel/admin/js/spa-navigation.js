/**
 * SPA Navigation Module
 * 单页应用导航模块 - 无刷新页面切换
 * 
 * 功能：
 * 1. 拦截侧边栏链接点击，改为异步加载内容
 * 2. 更新浏览器历史记录
 * 3. 保持侧边栏状态不变
 * 4. 添加加载动画
 */
(function() {
	'use strict';
	
	const SPA = {
		// 配置
		config: {
			contentContainer: '#maigewan-main-content',
			sidebarLinks: '.sidebar .nav-link',
			loadingClass: 'spa-loading',
			activeClass: 'active',
			animationDuration: 300
		},
		
		// 当前加载的URL
		currentUrl: window.location.href,
		
		// 初始化
		init: function() {
			this.bindEvents();
			this.updateActiveLink();
			console.log('[SPA Navigation] Initialized');
		},
		
		// 绑定事件
		bindEvents: function() {
			const self = this;
			
			// 拦截侧边栏链接点击
			document.addEventListener('click', function(e) {
				const link = e.target.closest(self.config.sidebarLinks);
				
				if (link && link.href && !link.hasAttribute('data-no-spa')) {
					e.preventDefault();
					e.stopPropagation();
					
					const url = link.href;
					self.navigate(url);
				}
			});
			
			// 处理浏览器前进/后退
			window.addEventListener('popstate', function(e) {
				if (e.state && e.state.url) {
					self.loadContent(e.state.url, false);
				}
			});
		},
		
		// 导航到新页面
		navigate: function(url) {
			if (url === this.currentUrl) {
				return;
			}
			
			this.loadContent(url, true);
		},
		
		// 加载内容
		loadContent: function(url, pushState) {
			const self = this;
			const container = document.querySelector(this.config.contentContainer);
			
			if (!container) {
				// 如果容器不存在，执行正常跳转
				window.location.href = url;
				return;
			}
			
			// 显示加载状态
			container.classList.add(this.config.loadingClass);
			container.style.opacity = '0.5';
			container.style.pointerEvents = 'none';
			
			// 发送AJAX请求
			fetch(url, {
				method: 'GET',
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'X-SPA-Request': 'true'
				},
				credentials: 'same-origin'
			})
			.then(function(response) {
				if (!response.ok) {
					throw new Error('Network response was not ok');
				}
				return response.text();
			})
			.then(function(html) {
				// 解析HTML，提取主内容区域
				const parser = new DOMParser();
				const doc = parser.parseFromString(html, 'text/html');
				const newContent = doc.querySelector(self.config.contentContainer);
				
				if (newContent) {
					// 淡出效果
					setTimeout(function() {
						container.innerHTML = newContent.innerHTML;
						
						// 更新当前URL
						self.currentUrl = url;
						
						// 更新浏览器历史
						if (pushState) {
							history.pushState({ url: url }, '', url);
						}
						
						// 更新激活状态
						self.updateActiveLink();
						
						// 滚动到顶部
						container.scrollTop = 0;
						
						// 移除加载状态，淡入效果
						container.style.opacity = '1';
						container.style.pointerEvents = 'auto';
						container.classList.remove(self.config.loadingClass);
						
						// 重新执行页面内的脚本
						self.executeScripts(newContent);
						
						// 触发自定义事件
						const event = new CustomEvent('spa:loaded', { 
							detail: { url: url } 
						});
						document.dispatchEvent(event);
						
					}, 150);
				} else {
					// 如果无法提取内容，执行正常跳转
					window.location.href = url;
				}
			})
			.catch(function(error) {
				console.error('[SPA Navigation] Load error:', error);
				container.style.opacity = '1';
				container.style.pointerEvents = 'auto';
				container.classList.remove(self.config.loadingClass);
				
				// 出错时执行正常跳转
				window.location.href = url;
			});
		},
		
		// 更新激活状态的链接
		updateActiveLink: function() {
			const links = document.querySelectorAll(this.config.sidebarLinks);
			const currentPath = window.location.pathname;
			
			links.forEach(function(link) {
				const linkPath = new URL(link.href).pathname;
				
				if (linkPath === currentPath) {
					link.classList.add('active');
				} else {
					link.classList.remove('active');
				}
			});
		},
		
		// 执行新加载内容中的脚本
		executeScripts: function(container) {
			const scripts = container.querySelectorAll('script');
			
			scripts.forEach(function(oldScript) {
				const newScript = document.createElement('script');
				
				// 复制属性
				Array.from(oldScript.attributes).forEach(function(attr) {
					newScript.setAttribute(attr.name, attr.value);
				});
				
				// 复制内容
				newScript.textContent = oldScript.textContent;
				
				// 不执行外部脚本，避免重复加载
				if (!oldScript.src) {
					try {
						eval(newScript.textContent);
					} catch (e) {
						console.error('[SPA Navigation] Script execution error:', e);
					}
				}
			});
		}
	};
	
	// DOM加载完成后初始化
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			SPA.init();
		});
	} else {
		SPA.init();
	}
	
	// 导出到全局
	window.MaigewanSPA = SPA;
	
})();
