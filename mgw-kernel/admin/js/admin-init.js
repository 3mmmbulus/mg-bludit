/**
 * Maigewan Admin Panel - Initialization Bundle
 * 管理后台初始化脚本集合
 * 
 * 这个文件作为管理后台JS的统一入口点
 * 自动加载所有需要的管理后台功能模块
 */

// 此文件通过index.php引用,确保在DOM加载完成后所有功能正常工作
console.log('[Maigewan Admin] Initialization bundle loaded');

// 注意: 以下脚本需要按顺序加载
// 1. variables.php - 由PHP动态生成全局变量
// 2. sidebar-collapse.js - 侧边栏折叠功能  
// 3. alert.js - 提示消息系统
// 4. 其他功能模块根据需要添加

// 导出全局对象
window.MaigewanAdmin = window.MaigewanAdmin || {
	version: window.MAIGEWAN_VERSION || 'unknown',
	initialized: false,
	
	init: function() {
		if (this.initialized) {
			console.warn('[Maigewan Admin] Already initialized');
			return;
		}
		
		console.log('[Maigewan Admin] Version:', this.version);
		this.initialized = true;
	}
};

// DOM加载完成后初始化
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', function() {
		window.MaigewanAdmin.init();
	});
} else {
	window.MaigewanAdmin.init();
}
