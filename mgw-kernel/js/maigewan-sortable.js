/**
 * Maigewan Sortable - 轻量级拖放排序
 * 使用原生HTML5 Drag and Drop API
 * @version 1.0.0
 */

(function(window) {
    'use strict';

    // 等待MGW加载
    function init() {
        if (!window.MGWCollection) {
            setTimeout(init, 50);
            return;
        }

        /**
         * Sortable插件
         */
        window.MGWCollection.prototype.sortable = function(options) {
        const defaults = {
            handle: null,
            placeholder: 'sortable-placeholder',
            update: null
        };

        const config = Object.assign({}, defaults, options);

        this.elements.forEach(container => {
            let draggedItem = null;
            let draggedOver = null;

            const items = Array.from(container.children);
            
            items.forEach(item => {
                // 设置为可拖动
                item.draggable = true;
                
                // 拖动开始
                item.addEventListener('dragstart', function(e) {
                    draggedItem = this;
                    this.style.opacity = '0.5';
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', this.innerHTML);
                });

                // 拖动结束
                item.addEventListener('dragend', function(e) {
                    this.style.opacity = '';
                    
                    // 移除所有占位符
                    container.querySelectorAll('.' + config.placeholder).forEach(el => {
                        el.remove();
                    });

                    // 触发更新回调
                    if (config.update) {
                        config.update.call(container, e, { item: this });
                    }
                });

                // 拖动进入
                item.addEventListener('dragenter', function(e) {
                    e.preventDefault();
                    if (this !== draggedItem) {
                        draggedOver = this;
                        this.style.borderTop = '2px solid #0078d4';
                    }
                });

                // 拖动离开
                item.addEventListener('dragleave', function(e) {
                    this.style.borderTop = '';
                });

                // 拖动经过
                item.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    return false;
                });

                // 放下
                item.addEventListener('drop', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    
                    this.style.borderTop = '';
                    
                    if (draggedItem !== this) {
                        // 获取位置
                        const allItems = Array.from(container.children);
                        const draggedIndex = allItems.indexOf(draggedItem);
                        const targetIndex = allItems.indexOf(this);
                        
                        // 插入到正确位置
                        if (draggedIndex < targetIndex) {
                            this.parentNode.insertBefore(draggedItem, this.nextSibling);
                        } else {
                            this.parentNode.insertBefore(draggedItem, this);
                        }
                    }
                    
                    return false;
                });
            });
        });

        return this;
    };

    /**
     * 获取排序后的数据
     */
    MGWCollection.prototype.sortable.toArray = function(container, attribute) {
        attribute = attribute || 'data-id';
        const items = container.children;
        return Array.from(items).map(item => item.getAttribute(attribute));
    };

    }

    // 开始初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})(window);
