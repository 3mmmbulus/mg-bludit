/**
 * Maigewan DOM - 轻量级原生JS工具库
 * 替代jQuery,提供简洁的DOM操作API
 * @version 1.0.0
 */

(function(window) {
    'use strict';

    /**
     * 主选择器函数 - 替代 $()
     * @param {string|Element|Document|Window|Function} selector
     * @param {Element} context
     * @returns {MGW|MGWCollection}
     */
    function MGW(selector, context) {
        // 如果是函数,等待DOM加载完成
        if (typeof selector === 'function') {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', selector);
            } else {
                selector();
            }
            return;
        }

        // 如果已经是元素、Document或Window
        if (selector instanceof Element || selector instanceof Document || selector instanceof Window) {
            return new MGWCollection([selector]);
        }

        // 如果是MGWCollection，直接返回
        if (selector instanceof MGWCollection) {
            return selector;
        }

        // 如果是字符串选择器
        if (typeof selector === 'string') {
            context = context || document;
            
            // 处理HTML字符串
            if (selector.trim().startsWith('<')) {
                const template = document.createElement('template');
                template.innerHTML = selector.trim();
                return new MGWCollection(Array.from(template.content.children));
            }
            
            // 普通选择器
            const elements = context.querySelectorAll(selector);
            return new MGWCollection(Array.from(elements));
        }

        // 默认返回空集合
        return new MGWCollection([]);
    }

    /**
     * MGW集合类 - 提供链式调用
     */
    class MGWCollection {
        constructor(elements) {
            this.elements = elements;
            this.length = elements.length;
            
            // 使元素可通过索引访问
            elements.forEach((el, i) => {
                this[i] = el;
            });
        }

        // 遍历元素
        each(callback) {
            this.elements.forEach((el, index) => {
                callback.call(el, index, el);
            });
            return this;
        }

        // 添加事件监听 - 替代 .on()
        on(event, selector, handler) {
            // 如果没有selector,handler就是第二个参数
            if (typeof selector === 'function') {
                handler = selector;
                selector = null;
            }

            this.elements.forEach(el => {
                if (selector) {
                    // 事件委托
                    el.addEventListener(event, function(e) {
                        const target = e.target.closest(selector);
                        if (target && el.contains(target)) {
                            handler.call(target, e);
                        }
                    });
                } else {
                    el.addEventListener(event, handler);
                }
            });
            return this;
        }

        // 移除事件监听
        off(event, handler) {
            this.elements.forEach(el => {
                el.removeEventListener(event, handler);
            });
            return this;
        }

        // 触发事件
        trigger(eventName, data) {
            this.elements.forEach(el => {
                let event;
                if (typeof eventName === 'object') {
                    // 如果传入的是 Event 对象
                    event = eventName;
                } else {
                    // 创建新事件
                    if (typeof Event === 'function') {
                        event = new Event(eventName, { bubbles: true, cancelable: true });
                    } else {
                        event = document.createEvent('Event');
                        event.initEvent(eventName, true, true);
                    }
                }
                if (data !== undefined) {
                    event.detail = data;
                }
                el.dispatchEvent(event);
            });
            return this;
        }

        // Bootstrap 5 兼容性:键盘事件快捷方法
        keypress(handler) {
            return this.on('keypress', handler);
        }
        
        keydown(handler) {
            return this.on('keydown', handler);
        }
        
        keyup(handler) {
            return this.on('keyup', handler);
        }
        
        // Bootstrap 5 兼容性:其他常用事件快捷方法
        click(handler) {
            return handler ? this.on('click', handler) : this.trigger('click');
        }
        
        change(handler) {
            return this.on('change', handler);
        }
        
        submit(handler) {
            return this.on('submit', handler);
        }
        
        focus(handler) {
            return handler ? this.on('focus', handler) : this.elements[0]?.focus();
        }
        
        blur(handler) {
            return handler ? this.on('blur', handler) : this.elements[0]?.blur();
        }

        // 获取/设置值 - 替代 .val()
        val(value) {
            if (value === undefined) {
                return this.elements[0] ? this.elements[0].value : '';
            }
            this.elements.forEach(el => {
                el.value = value;
            });
            return this;
        }

        // 获取/设置HTML - 替代 .html()
        html(content) {
            if (content === undefined) {
                return this.elements[0] ? this.elements[0].innerHTML : '';
            }
            this.elements.forEach(el => {
                el.innerHTML = content;
            });
            return this;
        }

        // 获取/设置文本 - 替代 .text()
        text(content) {
            if (content === undefined) {
                return this.elements[0] ? this.elements[0].textContent : '';
            }
            this.elements.forEach(el => {
                el.textContent = content;
            });
            return this;
        }

        // 获取/设置属性 - 替代 .attr()
        attr(name, value) {
            if (value === undefined) {
                return this.elements[0] ? this.elements[0].getAttribute(name) : null;
            }
            this.elements.forEach(el => {
                el.setAttribute(name, value);
            });
            return this;
        }

        // 移除属性
        removeAttr(name) {
            this.elements.forEach(el => {
                el.removeAttribute(name);
            });
            return this;
        }

        // 添加类 - 替代 .addClass()
        addClass(className) {
            this.elements.forEach(el => {
                el.classList.add(...className.split(' '));
            });
            return this;
        }

        // 移除类 - 替代 .removeClass()
        removeClass(className) {
            this.elements.forEach(el => {
                el.classList.remove(...className.split(' '));
            });
            return this;
        }

        // 切换类 - 替代 .toggleClass()
        toggleClass(className) {
            this.elements.forEach(el => {
                el.classList.toggle(className);
            });
            return this;
        }

        // 检查类 - 替代 .hasClass()
        hasClass(className) {
            return this.elements[0] ? this.elements[0].classList.contains(className) : false;
        }

        // 显示元素 - 替代 .show()
        show() {
            this.elements.forEach(el => {
                el.style.display = '';
            });
            return this;
        }

        // 隐藏元素 - 替代 .hide()
        hide() {
            this.elements.forEach(el => {
                el.style.display = 'none';
            });
            return this;
        }

        // 切换显示 - 替代 .toggle()
        toggle() {
            this.elements.forEach(el => {
                el.style.display = el.style.display === 'none' ? '' : 'none';
            });
            return this;
        }

        // 淡入 - 替代 .fadeIn()
        fadeIn(duration = 400, callback) {
            this.elements.forEach(el => {
                el.style.opacity = 0;
                el.style.display = '';
                
                let start = null;
                function animate(timestamp) {
                    if (!start) start = timestamp;
                    const progress = timestamp - start;
                    el.style.opacity = Math.min(progress / duration, 1);
                    
                    if (progress < duration) {
                        requestAnimationFrame(animate);
                    } else if (callback) {
                        callback.call(el);
                    }
                }
                requestAnimationFrame(animate);
            });
            return this;
        }

        // 淡出 - 替代 .fadeOut()
        fadeOut(duration = 400, callback) {
            this.elements.forEach(el => {
                let start = null;
                const initialOpacity = parseFloat(getComputedStyle(el).opacity);
                
                function animate(timestamp) {
                    if (!start) start = timestamp;
                    const progress = timestamp - start;
                    el.style.opacity = Math.max(initialOpacity - (progress / duration), 0);
                    
                    if (progress < duration) {
                        requestAnimationFrame(animate);
                    } else {
                        el.style.display = 'none';
                        if (callback) callback.call(el);
                    }
                }
                requestAnimationFrame(animate);
            });
            return this;
        }

        // 获取/设置CSS - 替代 .css()
        css(prop, value) {
            if (typeof prop === 'object') {
                // 设置多个属性
                this.elements.forEach(el => {
                    Object.assign(el.style, prop);
                });
                return this;
            }
            
            if (value === undefined) {
                // 获取属性
                return this.elements[0] ? getComputedStyle(this.elements[0])[prop] : null;
            }
            
            // 设置单个属性
            this.elements.forEach(el => {
                el.style[prop] = value;
            });
            return this;
        }

        // 获取/设置data属性 - 替代 .data()
        data(key, value) {
            if (value === undefined) {
                return this.elements[0] ? this.elements[0].dataset[key] : null;
            }
            this.elements.forEach(el => {
                el.dataset[key] = value;
            });
            return this;
        }

        // 查找子元素 - 替代 .find()
        find(selector) {
            const found = [];
            this.elements.forEach(el => {
                found.push(...el.querySelectorAll(selector));
            });
            return new MGWCollection(found);
        }

        // 查找最近的父元素 - 替代 .closest()
        closest(selector) {
            const found = [];
            this.elements.forEach(el => {
                const closest = el.closest(selector);
                if (closest) found.push(closest);
            });
            return new MGWCollection(found);
        }

        // 获取父元素 - 替代 .parent()
        parent() {
            const parents = this.elements.map(el => el.parentElement).filter(Boolean);
            return new MGWCollection(parents);
        }

        // 获取子元素 - 替代 .children()
        children() {
            const children = [];
            this.elements.forEach(el => {
                children.push(...el.children);
            });
            return new MGWCollection(children);
        }

        // 添加子元素 - 替代 .append()
        append(content) {
            this.elements.forEach(el => {
                if (typeof content === 'string') {
                    el.insertAdjacentHTML('beforeend', content);
                } else if (content instanceof Element) {
                    el.appendChild(content);
                } else if (content instanceof MGWCollection) {
                    content.elements.forEach(child => {
                        el.appendChild(child.cloneNode(true));
                    });
                }
            });
            return this;
        }

        // 在前面添加 - 替代 .prepend()
        prepend(content) {
            this.elements.forEach(el => {
                if (typeof content === 'string') {
                    el.insertAdjacentHTML('afterbegin', content);
                } else if (content instanceof Element) {
                    el.insertBefore(content, el.firstChild);
                }
            });
            return this;
        }

        // 移除元素 - 替代 .remove()
        remove() {
            this.elements.forEach(el => {
                el.remove();
            });
            return this;
        }

        // 提交表单 - 替代 .submit()
        submit() {
            this.elements.forEach(el => {
                if (el.tagName === 'FORM') {
                    el.submit();
                }
            });
            return this;
        }

        // 获取第一个元素
        get(index) {
            return index !== undefined ? this.elements[index] : this.elements;
        }

        // 获取第一个元素的MGW对象
        eq(index) {
            return new MGWCollection([this.elements[index]]);
        }

        // Document ready - 支持 $(document).ready()
        ready(callback) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', callback);
            } else {
                callback();
            }
            return this;
        }
    }

    /**
     * AJAX请求 - 替代 $.ajax()
     */
    MGW.ajax = function(options) {
        const defaults = {
            method: 'GET',
            url: '',
            data: null,
            headers: {},
            contentType: 'application/x-www-form-urlencoded',
            success: null,
            error: null,
            complete: null
        };

        const config = Object.assign({}, defaults, options);
        
        // 处理数据
        let body = config.data;
        if (config.method === 'POST' && config.data && typeof config.data === 'object') {
            if (config.contentType.includes('json')) {
                body = JSON.stringify(config.data);
            } else {
                body = new FormData();
                for (let key in config.data) {
                    body.append(key, config.data[key]);
                }
            }
        }

        const fetchOptions = {
            method: config.method,
            headers: config.headers,
            body: body
        };

        if (config.method === 'POST' && typeof body === 'string') {
            fetchOptions.headers['Content-Type'] = config.contentType;
        }

        fetch(config.url, fetchOptions)
            .then(response => {
                if (!response.ok) throw new Error('HTTP error ' + response.status);
                return response.json();
            })
            .then(data => {
                if (config.success) config.success(data);
            })
            .catch(error => {
                if (config.error) config.error(error);
            })
            .finally(() => {
                if (config.complete) config.complete();
            });
    };

    /**
     * GET请求 - 替代 $.get()
     */
    MGW.get = function(url, success) {
        return MGW.ajax({
            method: 'GET',
            url: url,
            success: success
        });
    };

    /**
     * POST请求 - 替代 $.post()
     */
    MGW.post = function(url, data, success) {
        return MGW.ajax({
            method: 'POST',
            url: url,
            data: data,
            success: success
        });
    };

    /**
     * 扩展对象 - 替代 $.extend()
     */
    MGW.extend = function(target, ...sources) {
        return Object.assign(target, ...sources);
    };

    /**
     * Bootstrap 5 兼容性: Event 构造函数
     */
    MGW.Event = function(type, props) {
        // 创建原生事件
        let event;
        if (typeof Event === 'function') {
            event = new Event(type, { bubbles: true, cancelable: true });
        } else {
            event = document.createEvent('Event');
            event.initEvent(type, true, true);
        }
        
        // 添加自定义属性
        if (props) {
            Object.assign(event, props);
        }
        
        // jQuery 兼容方法
        event.preventDefault = event.preventDefault || function() {};
        event.stopPropagation = event.stopPropagation || function() {};
        event.stopImmediatePropagation = event.stopImmediatePropagation || function() {};
        event.isDefaultPrevented = function() { return event.defaultPrevented; };
        event.isPropagationStopped = function() { return false; };
        event.isImmediatePropagationStopped = function() { return false; };
        
        return event;
    };
    
    // 确保 Event 也作为原型方法可用
    MGWCollection.prototype.Event = MGW.Event;

    /**
     * 遍历数组或对象 - 替代 $.each()
     */
    MGW.each = function(obj, callback) {
        if (Array.isArray(obj)) {
            obj.forEach((item, index) => {
                callback.call(item, index, item);
            });
        } else {
            Object.keys(obj).forEach(key => {
                callback.call(obj[key], key, obj[key]);
            });
        }
    };

    // 暴露到全局
    window.MGW = window.$ = MGW;
    window.MGWCollection = MGWCollection;

    // Bootstrap 5 兼容性: fn 别名指向原型
    MGW.fn = MGWCollection.prototype;
    
    // Bootstrap 5 会在 $.fn 上注册组件,为其预留占位符
    // 这样 Bootstrap 加载时就能找到 $.fn.alert, $.fn.modal 等
    if (!MGW.fn.alert) {
        MGW.fn.alert = function() { return this; };
    }
    if (!MGW.fn.button) {
        MGW.fn.button = function() { return this; };
    }
    if (!MGW.fn.carousel) {
        MGW.fn.carousel = function() { return this; };
    }
    if (!MGW.fn.collapse) {
        MGW.fn.collapse = function() { return this; };
    }
    if (!MGW.fn.dropdown) {
        MGW.fn.dropdown = function() { return this; };
    }
    if (!MGW.fn.modal) {
        MGW.fn.modal = function() { return this; };
    }
    if (!MGW.fn.offcanvas) {
        MGW.fn.offcanvas = function() { return this; };
    }
    if (!MGW.fn.popover) {
        MGW.fn.popover = function() { return this; };
    }
    if (!MGW.fn.scrollspy) {
        MGW.fn.scrollspy = function() { return this; };
    }
    if (!MGW.fn.tab) {
        MGW.fn.tab = function() { return this; };
    }
    if (!MGW.fn.toast) {
        MGW.fn.toast = function() { return this; };
    }
    if (!MGW.fn.tooltip) {
        MGW.fn.tooltip = function() { return this; };
    }

    // 兼容性别名
    window.jQuery = MGW;

})(window);
