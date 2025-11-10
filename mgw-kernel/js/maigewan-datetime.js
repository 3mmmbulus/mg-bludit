/**
 * Maigewan DateTime - 轻量级日期时间选择器
 * 使用原生HTML5 datetime-local输入
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
         * DateTimePicker插件
         */
        window.MGWCollection.prototype.datetimepicker = function(options) {
            const defaults = {
                format: 'Y-m-d H:i:s',
                timepicker: true,
                datepicker: true
            };

            const config = Object.assign({}, defaults, options);

            this.elements.forEach(el => {
                // 转换为HTML5的datetime-local类型
                if (config.timepicker && config.datepicker) {
                    el.type = 'datetime-local';
                } else if (config.datepicker) {
                    el.type = 'date';
                } else if (config.timepicker) {
                    el.type = 'time';
                }

                // 转换现有值到HTML5格式
                if (el.value) {
                    el.value = convertToHTML5Format(el.value, config.format);
                }

                // 监听变化,转换回原格式
                el.addEventListener('change', function() {
                    // 这里可以添加格式转换逻辑
                });
            });

            return this;
        };

        /**
         * 转换日期格式
         */
        function convertToHTML5Format(dateStr, format) {
            // 简单的格式转换
            // 例如: "2025-11-11 14:30:00" -> "2025-11-11T14:30"
            if (dateStr.includes(' ')) {
                return dateStr.replace(' ', 'T').substring(0, 16);
            }
            return dateStr;
        }
    }

    // 开始初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})(window);
