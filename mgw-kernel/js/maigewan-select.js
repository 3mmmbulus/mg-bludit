/**
 * Maigewan Select - 轻量级下拉选择器
 * 简化版Select2,基于原生datalist
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
         * Select2插件简化版
         */
        window.MGWCollection.prototype.select2 = function(options) {
        const defaults = {
            placeholder: '',
            allowClear: false,
            width: '100%',
            theme: 'bootstrap5',
            minimumInputLength: 0,
            ajax: null,
            templateResult: null,
            escapeMarkup: function(markup) { return markup; }
        };

        const config = Object.assign({}, defaults, options);

        this.elements.forEach(el => {
            // 添加Bootstrap类
            el.classList.add('form-select');
            
            // 设置宽度
            if (config.width) {
                el.style.width = config.width;
            }

            // 如果配置了AJAX
            if (config.ajax) {
                setupAjaxSelect(el, config);
            }

            // 添加清除按钮(如果启用)
            if (config.allowClear && !el.dataset.clearAdded) {
                addClearButton(el);
                el.dataset.clearAdded = 'true';
            }

            // 设置placeholder
            if (config.placeholder && !el.querySelector('option[value=""]')) {
                const placeholderOption = document.createElement('option');
                placeholderOption.value = '';
                placeholderOption.textContent = config.placeholder;
                placeholderOption.disabled = true;
                placeholderOption.selected = true;
                el.insertBefore(placeholderOption, el.firstChild);
            }
        });

        // 返回链式对象,支持on方法
        const collection = new MGWCollection(this.elements);
        
        // 添加特殊方法
        collection.select2Open = function() {
            this.elements.forEach(el => {
                // 触发focus使其打开
                el.focus();
                if (el.showPicker) {
                    el.showPicker();
                }
            });
            return this;
        };

        return collection;
    };

    /**
     * 设置AJAX下拉框
     */
    function setupAjaxSelect(selectEl, config) {
        const wrapper = document.createElement('div');
        wrapper.className = 'ajax-select-wrapper';
        wrapper.style.position = 'relative';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control';
        input.placeholder = config.placeholder;
        
        const dropdown = document.createElement('div');
        dropdown.className = 'ajax-select-dropdown';
        dropdown.style.cssText = 'position:absolute;top:100%;left:0;right:0;background:white;border:1px solid #ddd;max-height:300px;overflow-y:auto;display:none;z-index:1000;';
        
        // 隐藏原始select
        selectEl.style.display = 'none';
        selectEl.parentNode.insertBefore(wrapper, selectEl);
        wrapper.appendChild(input);
        wrapper.appendChild(dropdown);
        wrapper.appendChild(selectEl);
        
        let debounceTimer;
        
        input.addEventListener('input', function() {
            const query = this.value;
            
            if (query.length < config.minimumInputLength) {
                dropdown.style.display = 'none';
                return;
            }
            
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                // 调用AJAX
                const params = { term: query };
                fetch(config.ajax.url + '?' + new URLSearchParams(config.ajax.data ? config.ajax.data(params) : params))
                    .then(res => res.json())
                    .then(data => {
                        const results = config.ajax.processResults ? config.ajax.processResults(data) : data;
                        displayResults(dropdown, results, config, input, selectEl);
                    })
                    .catch(err => console.error('AJAX error:', err));
            }, 300);
        });
        
        // 点击外部关闭
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // 打开下拉框
        input.addEventListener('focus', function() {
            if (dropdown.children.length > 0) {
                dropdown.style.display = 'block';
            }
        });
    }

    /**
     * 显示AJAX结果
     */
    function displayResults(dropdown, data, config, input, selectEl) {
        dropdown.innerHTML = '';
        
        if (!data.results || data.results.length === 0) {
            dropdown.style.display = 'none';
            return;
        }
        
        data.results.forEach(item => {
            const div = document.createElement('div');
            div.className = 'ajax-select-item';
            div.style.cssText = 'padding:8px 12px;cursor:pointer;';
            
            // 使用templateResult如果提供
            if (config.templateResult) {
                div.innerHTML = config.escapeMarkup(config.templateResult(item));
            } else {
                div.textContent = item.text || item.id;
            }
            
            div.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f0f0f0';
            });
            
            div.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
            
            div.addEventListener('click', function() {
                input.value = item.text || item.id;
                
                // 更新select
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.text || item.id;
                option.selected = true;
                selectEl.appendChild(option);
                
                dropdown.style.display = 'none';
                
                // 触发change事件
                selectEl.dispatchEvent(new Event('change', { bubbles: true }));
            });
            
            dropdown.appendChild(div);
        });
        
        dropdown.style.display = 'block';
    }

    /**
     * 添加清除按钮
     */
    function addClearButton(selectEl) {
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.display = 'inline-block';
        wrapper.style.width = '100%';
        
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.innerHTML = '×';
        clearBtn.className = 'btn-close btn-sm';
        clearBtn.style.cssText = 'position:absolute;right:30px;top:50%;transform:translateY(-50%);z-index:10;';
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            selectEl.value = '';
            selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        });
        
        selectEl.parentNode.insertBefore(wrapper, selectEl);
        wrapper.appendChild(selectEl);
        wrapper.appendChild(clearBtn);
    }

    }

    // 开始初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})(window);
